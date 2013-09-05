<?php
/**
 * La classe qui va être instanciée par le contrôleur pour faire le rendu d'une vue
 *
 * @author Pierstoval 01/08/2013
 * @version 1.0
 */
class View extends Object {

	private $controller;

	/**
	 * Les variables à créer dynamiquement seulement pour la vue
	 * @var array
	 */
	public $vars = array();

	/**
	 * Les données et variables supplémentaires à insérer dans le layout, notamment les CSS et JS
	 * @var array
	 */
	private $more_datas = array();

	function __construct($module, $name, Controller $controller) {
		$this->file = ROOT.DS.'modules'.DS.$module.DS.'views'.DS.$name.'.php';//On génère le fichier de la vue
		$this->module = $module;
		$this->view_name = preg_replace('~^_~', '', $controller->action);
		$this->controller = $controller;//On rajoute le contrôleur à la vue pour éventuellement en récupérer le nom ou certaines données
	}

	public function controller() {
		return $this->controller;
	}

	/**
	 * Retourne les variables du layout
	 * @return array
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function get_more_datas() {
		return $this->more_datas;
	}

	/**
	 * Ajoute un fichier CSS à insérer dans le layout
	 * @param string $filename Le nom du fichier
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function more_css($filename) {
		$this->more_datas['css_for_layout'][] = $filename;
	}

	/**
	 * Effectue le rendu d'un contrôleur et d'une action et l'affiche directement
	 *
	 * @param string $controller Le contrôleur à utiliser
	 * @param string $view L'action à exécuter
	 * @param array $params Les paramètres à envoyer à cette action
	 * @param boolean $return Renvoie le contenu au lieu de l'afficher si passé à true
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function render_controller($controller, $view = 'index', $params = array(), $return = false) {

		$controller = preg_replace('~(Controller)*$~U', '', $controller);
		$controller .= 'Controller';

		$main_controller = $this->controller()->main_controller();

		if ($this->controller()->name() !== $controller) {
			$main_controller->load_controller($controller);
		}

		ob_start();
		if ($return === true) {
			$main_controller->controller_execute_action($controller, $view, $params);
			return ob_get_clean();
		} else {
			$main_controller->controller_execute_action($controller, $view, $params);
			echo ob_get_clean();
		}
	}

	/**
	 * Effectue le rendu de la vue à partir du nom de fichier récupéré dans le constructeur
	 * @param string $return
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function render($return = false) {
		$this->return = $return;
		$controller = $this->controller();
		$controller_content = $this->controller()->main_controller()->content_for_layout();
		unset($return);
		if (file_exists($this->file)) {//Chargement du fichier de la vue s'il existe
			if ($controller->vars()) {
				foreach ($controller->vars() as $k => $v) {//On récupère les paramètres à envoyer à la vue
					if (!is_numeric($k)) {
						$$k = $v;//On crée les variables dynamiques à envoyer à la vue
					}
				}
			}
			unset($args, $k, $v);
			ob_start();
			require $this->file;//On charge le fichier php de la vue
			$content_for_layout = ob_get_clean();
		} elseif ($controller->rendered()) {
			//Si le contrôleur détermine qu'il est rendu (généralement manuellement) alors le contenu du
			//layout est vide puisqu'aucun fichier n'existe. Souvent, le contrôleur lui-même affiche le contenu
			$content_for_layout = $controller->main_controller->content();
		} else {//Si le fichier de la vue n'existe pas

			$content_for_layout = '';
			$url = BASE_URL;
			if (FrontController::$automatic_pages_controller === false) {
				$url = Router::route($controller->request()->action());
			}
			Router::redirect('404', 404, 'La vue "{view}" n\'existe pas dans le contrôleur "{module}"', 'error', array('view'=>$this->view_name,'module'=>$controller->name));
		}
		$content_for_layout .= $controller_content;

		$more_datas = $this->get_more_datas();
		foreach ($more_datas as $k => $v) {
			$$k = $v;
		}

		if (!isset($_SERVER['__rendered']) || (isset($_SERVER['__rendered']) && $_SERVER['__rendered'] !== true)) {
			$_SERVER['__rendered'] = true;
			if (!isset($title_for_layout)) { $title_for_layout = ''; }
			require ROOT.DS.'layouts'.DS.'layout_'.$this->layout.'.php';
		} else {
			echo $content_for_layout;
		}
	}

}