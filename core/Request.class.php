<?php
/**
 * Request est un objet qui va seulement, à l'instanciation, découper la requête.
 *
 * Si le Router trouve une route qui matche la requête, il récupère le contrôleur, l'action et les paramètres éventuels.
 * Sinon, il se charge lui-même de découper la requête :
 * 1. L'url est découpée selon ses signes "/".
 * 2. Le premier paramètre récupéré est le contrôleur.
 * 3. Le deuxième paramètre est l'action du contrôleur.
 * 4. Tous les autres seront des arguments envoyés à la vue.
 *
 * @author Pierstoval 01/08/2013
 * @version 1.0
 */
class Request extends Object {

	/**
	 * Le contrôleur demandé dans la requête
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public $controller_name = 'Pages';

	/**
	 * L'action demandée dans la requête
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public $action = 'index';

	/**
	 * Sera passé à true dans le cas où la requête matche l'une des routes enregistrées dans le Router
	 * @var boolean
	 * @see Router
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	protected $routed = false;

	/**
	 * Si $routed === true, alors contiendra un objet avec les données de la route
	 * @var object StdClass
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	protected $route_used = array();

	/**
	 * La requête, issu de $_SERVER['REQUEST_URI']
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	protected $asked_uri;

	/**
	 * Les arguments à passer à l'action du contrôleur
	 * @var array
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	protected $params = array();

	/**
	 * L'url complète actuelle
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	protected $full_url = '';

	/**
	 * L'extension de l'url
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	protected $extension = 'html';

	/**
	 * Un tableau avec les données _GET
	 * @var array
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	protected $get = array();

	/**
	 * Un tableau avec les données _POST
	 * @var array
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	protected $post = array();

	/**
	 * Contiendra l'url du referer si elle est différente de l'url actuelle
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	protected $referer = '';

	function __construct() {

		$request = isset($_GET['request']) ? $_GET['request'] : '';//On récupère ce qu'il y a dans request, envoyé par les deux fichiers .htaccess

		unset($_GET['request']);
		$this->asked_uri = $request;
		$ext = pathinfo($request);//Grâce à pathinfo() on va récupérer l'extension

		$this->routed = false;
		$route = Router::route($request);//On vérifie si l'on a une route
		if ($route !== $request) {
			$this->route_used = (object)Router::$last_route;
			$this->routed = true;
			$request = $route;
		}

		if (isset($ext['extension'])) {//Si elle existe, on l'enlève de l'url et on la met en lowercase
			$request = str_replace('.'.$ext['extension'], '', $request);
			$ext = strtolower($ext['extension']);
		} else {
			$ext = '';
		}

		if ($request) {//Si on a une requête on la découpe
			$request = explode('/', $request);
			$request = array_filter($request);
			$controller = array_shift($request);	//La première information est le contrôleur
			$action = array_shift($request);		//La deuxième est l'action du contrôleur
			if (!$action) { $action = 'index'; }	//Si on n'a aucune action, on charge la méthode "index" par défaut

		} else {
			//Sinon on charge l'accueil par défaut
			$request = array();
			$controller = 'pages';
			$action = 'index';
		}
		$t = array();
		if ($ext === $controller) { $ext = ''; }
		foreach($request as $v) {
			if (preg_match('#:#isUu', $v)) {//Transfert des variables cle:valeur en tableau
				$v = explode(':', $v, 2);
				$t[$v[0]] = $v[1];
			} else { $t[] = $v; }
		}
		if (preg_match('~^[0-9]~', $controller)) {
			$controller = preg_replace('~^([0-9])~', '_$1', $controller);
		}
		$controller = preg_replace('~(Controller)*$~U', '', $controller);
		$controller .= 'Controller';
		$this->params = $request;
		$this->extension = $ext;
		$this->controller_name = ucfirst($controller);
		$this->action = $action;

		/**
		 * On crée la variable $_GET pour obtenir les informations en GET
		 */
		$get_parameters = $_SERVER['REQUEST_URI'];
		if (preg_match('#\?#isUu', $get_parameters)) {
			$get_parameters = preg_replace('#^[^\?]*\?#isUu', '', $get_parameters);
			$get_parameters = explode('&', $get_parameters);
			$t = array();
			foreach($get_parameters as $k => $v) {
				$v = explode('=', $v);
				$t[$v[0]] = isset($v[1]) ? $v[1] : '';
				$_GET[$v[0]] = isset($v[1]) ? $v[1] : '';
			}
			$get_parameters = $t;
		} else {
			$get_parameters = array();
		}
		$_GET = array_map('urldecode', $get_parameters);
		$this->get = $_GET;
		unset($t, $get_parameters);

		$this->post = get_post_datas();

		if ($this->asked_uri === '') {
			$actual_url = BASE_URL;
		} else {
			$actual_url = 'http://'.P_BASE_HOST.$_SERVER['REQUEST_URI'];
// 			$actual_url = Router::link(array(
// 				'route'=>$this->asked_uri,
// 				'route_type' => 'route',
// 				'ext'=>$ext,
// 				'get'=>$_GET,
// 			));
		}

		//*Module de redirection automatique pour que les urls soient celles des routes
		if (preg_match('~^'.BASE_URL.'/?(index)?(\.html?)?$~isUu', $actual_url)) {
			$full_url = BASE_URL;
		} else {
			if ($this->route_used) {
				$full_url = Router::link(array('route'=>$route, 'get'=>$_GET));
			} elseif ($route) {
				$route = trim($route, '/');
				$c = Router::get($route, 'uri', 'redirect');
				$full_url = Router::link(array('route_name' => $c['name'], 'force_route'=>true, 'get'=>$_GET));
			} else {
				$full_url = $actual_url;
			}
		}
		$this->full_url = $full_url;

		if (
			($actual_url !== $full_url) ||
			($this->route_used && $full_url !== $actual_url) ||
			($actual_url && $actual_url !== BASE_URL && $actual_url !== BASE_URL.'/' && !$ext && $action && $action !== 'index' && $this->routed === false)
		) {
// 			Router::redirect($full_url);//Le module de redirection automatique.
		}
		//Fin module de redirection automatique*/

		## Si le module chargé est la page d'accueil alors on redirige vers une page d'accueil possédant une url "saine"
// 		if ($controller === 'pages' && $action === 'index' && empty($request) && $actual_url !== BASE_URL && $actual_url !== BASE_URL.'/') { Router::redirect(BASE_URL); }
		unset($request);

		##On définit le referer en fonction de ce que l'on a dans HTTP_REFERER.
		##Si celui-ci est sur ce site, on récupère ses paramètres dans $_PAGE. Sinon, uniquement son url.
		$this->referer = '';
		if (isset($_SERVER['HTTP_REFERER'])) {
			$referer = $_SERVER['HTTP_REFERER'];
			if ($referer === $actual_url || $referer === $full_url) { $referer = ''; }
			$this->referer = $referer;
			//Si le referer n'existe pas sur ce site, alors on enregistre l'url dans les log pour des raisons statistiques
			$arr = array(
				'date' => date(DATE_RFC822),
				'referer' => $this->referer,
				'controller_name' => $this->controller_name,
				'action' => $this->action,
				'params' => $this->params,
			);
			$final = ','.json_encode($arr, P_JSON_ENCODE);
			file_put_contents(ROOT.DS.'logs'.DS.'referer'.DS.date('Y.m.d').'.log', $final, FILE_APPEND);
			unset($f, $final);
		}

	}

	/**
	 * Retourne les données _POST.
	 * @param string $name Si $name est fourni, retourne l'index demandé dans _POST s'il existe, null sinon.
	 * @return multitype La valeur de POST
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function post($name = null) {
		if ($name === null) {
			return $this->post;
		} else {
			return isset($this->post[$name]) ? $this->post[$name] : null;
		}
	}

	/**
	 * Retourne les données _GET.
	 * @param string $name Si $name est fourni, retourne l'index demandé dans _GET s'il existe, null sinon.
	 * @return multitype La valeur de GET
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function get($name = null) {
		if ($name === null) {
			return $this->get;
		} else {
			return isset($this->get[$name]) ? $this->get[$name] : null;
		}
	}
}