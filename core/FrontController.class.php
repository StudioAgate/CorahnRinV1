<?php
/**
 * Contrôleur frontal permettant de déléguer les tâches qui conviennent.
 * 1. Instancie un objet Request
 * 2. Instancie un contrôleur s'il en existe un
 * 3. Exécute une action demandée par Request, si elle existe.
 *
 * @author Pierstoval 01/08/2013
 * @version 1.0
 */
class FrontController extends Object {

	/**
	 * Si le dispatcher a été exécuté et qu'une vue a été rendue, sera passé à true.
	 * Cela permet de ne pas charger deux fois le layout au cas où l'on effectue le rendu d'un contrôleur et d'une action depuis une vue.
	 * @var boolean
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static $dispatched = false;

	/**
	 * Si passé à true, l'autoload ne renverra pas d'erreur.
	 * Cette variable est utilisée pour que l'on puisse vérifier l'existence d'une classe de contrôleur avant son instanciation
	 * @var boolean
	 */
	public static $search_controller = false;

	/**
	 * Sera passé à true dans le cas où le contrôleur "Pages" aura été chargé par défaut, en fonction de certaines urls
	 * @var boolean
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static $automatic_pages_controller = false;

	/**
	 * Sera passé à true lorsque le contrôleur aura effectué le rendu de sa vue
	 * @var unknown
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static $rendered = false;

	/**
	 * Contient le contenu du layout à afficher
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	protected $content_for_layout = '';

	function __construct() {
		if (self::$dispatched === false) {
			$this->request = new Request();
			$this->dispatch();
		}
	}

	/**
	 * Récupère le contenu du layout demandé
	 * @return string
	 */
	function content() {
		return $this->content_for_layout;
	}

	/**
	 * Charge un contrôleur en fonction de son nom. Les paramètres du contrôleur seront récupérés dans l'objet Request
	 * Si le contrôleur n'existe pas, charge le contrôleur "Pages" et vérifie s'il possède une action équivalente au contrôleur demandé.
	 *
	 * @param string $controller_name
	 * @see Request
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function load_controller($controller_name) {

		$controller_name = preg_replace('~(Controller)*$~U', '', $controller_name);
		$controller_name .= 'Controller';

		if (preg_match('~^[0-9]~', $controller_name)) {
			$controller_name = preg_replace('~^([0-9])~', '_$1', $controller_name);
		}

		self::$search_controller = true;//On va prévenir __autoload() que l'on cherche un contrôleur pour qu'il ne renvoie pas d'erreur

		if(class_exists($controller_name)) {//Si le contrôleur existe on le charge directement
			self::$search_controller = false;//On prévient __autoload() que le contrôleur a été trouvé pour qu'il renvoie des erreurs s'il ne trouve pas la classe demandée

			$this->$controller_name = new $controller_name();
			if (self::$dispatched === false) {
				$action = $this->request()->action();
				if (!$action) { $action = 'index'; }
				$action = preg_replace('~^([0-9])~', '_$1', $action);//Si l'on a des requêtes commençant par un chiffre (comme "404") alors la méthode dans la classe DOIT commencer par un "_"
				$this->$controller_name->action = $action;//On lui affecte l'action depuis request
			}

		} else {//Sinon, on charge le module Pages pour vérifier qu'une méthode du même nom existe déjà
			self::$search_controller = false;//Désormais __autoload() peut renvoyer une erreur s'il ne trouve pas la classe demandée
			self::$automatic_pages_controller = true;//On indique au main_controller qu'il a chargé par défaut le contrôleur "Pages"
			$action = $controller_name;
			$action = preg_replace('~(Controller)*$~U', '', $action);
			$action = preg_replace('~^([0-9])~', '_$1', $action);//Si l'on a des requêtes commençant par un chiffre (comme "404") alors la méthode dans la classe DOIT commencer par un "_"
			$controller_name = 'PagesController';
			$this->request->controller_name = $controller_name;
			$this->$controller_name = new $controller_name();//On crée le contrôleur , chargé avec __autoload()
			if (!$action) { $action = 'index'; }
			$this->request->action = $action;
			$this->$controller_name->action= $action;//On lui affecte l'action depuis request
// 			if ($action !== 'index' && self::$dispatched === false) {
// 				array_unshift($this->request->params, $action);
// 			}

		}

		$this->$controller_name->request($this->request);//On lui affecte request au cas où
		$this->$controller_name->main_controller = $this;//On lui affecte main_controller

	}

	/**
	 * Selon le contrôleur demandé, exécute l'action spécifiée. Si l'action n'existe pas, exécute l'action "index" par défaut<br />
	 * À noter que si le nombre d'arguments dans Request ne correspond pas au nombre d'arguments demandés dans l'action, on renvoie une erreur 404
	 *
	 * @param string $controller_name
	 * @param string $action
	 * @param array $params
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function controller_execute_action($controller_name, $action = 'index', $params = null) {

		$controller_name = preg_replace('~(Controller)*$~U', '', $controller_name);
		$controller_name .= 'Controller';

		$action = preg_replace('~_action$~U', '', $action);
		$action .= '_action';

		if (!isset($this->$controller_name)) {
			$this->load_controller($controller_name);
		}
		$args = $this->$controller_name->check_method_args($action);

		if ($args < count($params)){
			Router::redirect('404', 404);
			exit;
		}

		if ($params === null) {
			$params = $this->request->params;
		}

		ob_start();
		try {
			call_user_func_array(array($this->$controller_name, $action), $params);//Exécution de l'action du contrôleur
		} catch (PException $e) {
			$nb = 0;
			$msg = $e->getMessage();
			if (preg_match('~Missing argument ([0-9]+) for~isUu', $msg, $matches)) {
				$nb = (int) $matches[1];
			}
			$method = ucfirst(str_replace('_action', '', $action));
			$controller = str_replace('Controller', '', $controller_name);

			if ($nb) {
				$words = array('method'=>'<strong>'.$method.'</strong>', 'controller'=>'<strong>'.$controller.'</strong>', 'nb'=>$nb);
				$msg = tr('La méthode {method} du contrôleur {controller} doit comporter {nb} argument(s) minimum !', true, $words);
			} else {
				$words = array('method'=>'<strong>'.$method.'</strong>', 'controller'=>'<strong>'.$controller.'</strong>');
				$msg = tr('Une erreur est survenue lors de l\'exécution de la méthode {method} du contrôleur {controller}...', true, $words);
			}
			$e->show(false, $msg);
			exit;
		}
		$content_for_layout = ob_get_clean();
		$this->content_for_layout = $content_for_layout;
		$rendered = self::$rendered;
		if ($rendered === false) {
			$action = preg_replace('~^_~', '', $action);
			self::$dispatched = true;
			try {
				$cnt = $this->$controller_name->render($action);
			} catch (PException $e) {
				echo $e->show();
			}
// 			pr($cnt);
			self::$rendered = true;
		}

	}

	/**
	 * Charge le contrôleur dans Request et exécute son action.
	 *
	 * @throws PException Uniquement si une erreur survient
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	private function dispatch() {
		$controller_name = $this->request->controller_name();

		$controller_name = preg_replace('~(Controller)*$~U', '', $controller_name);
		$controller_name .= 'Controller';
		$this->load_controller($controller_name);

		$controller_name = $this->request->controller_name();

		if (isset($this->$controller_name)) {
			echo $this->controller_execute_action($controller_name, $this->request->action(), $this->request->params());
		} else {
			throw new PException('Erreur dans le chargement du contrôleur '.$controller_name);
			exit;
		}
	}

}