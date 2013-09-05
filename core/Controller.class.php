<?php
/**
 * Classe de contrôleur générique
 *
 * @author Pierstoval 01/01/2013
 * @version 1.0
 */
class Controller extends Object {

	/**
	 * Une liste de contrôleurs instanciés
	 * @var array
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	private static $instanciated = array();

	/**
	 * Le nom du dossier du module
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public $module;

	/**
	 * Variables supplémentaires à envoyer à la vue
	 * @var array
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	protected $vars = array();

	/**
	 * Passé à true lorsque la vue est rendue
	 * @var boolean
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public $rendered = false;

	/**
	 * L'action à exécuter
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public $action;

	/**
	 * Instance de l'objet FrontController
	 * @var object
	 * @see FrontController
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public $main_controller;

	/**
	 * Instance de l'objet Request
	 * @var object
	 * @see Request
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	protected $request;

	function __construct() {
		$this->name = get_class($this);
		$this->name_human = Inflector::humanize(strtolower($this->name));
		$reflection = new ReflectionClass($this);
		$name = $reflection->getFilename();
		$pathinfo = pathinfo($name);
		$dir = $pathinfo['dirname'];
		$module = str_replace(ROOT.DS.'modules'.DS, '', $dir);
		$module = str_replace(DS.'controllers', '', $module);
		if (!$module) {
			tr('Erreur du chargement dans le module "{MODULE}"', null, array('MODULE'=>$module));
			exit;
		}
		$this->module = $module;
		$this->layout = 'default';
		self::$instanciated[get_class($this)] = $this;
	}

	function __call($method, $args) {
// 		if (method_exists($this, $method)) {
// 			return call_user_func_array(array($this, $method), $args);
// 		}
		if (isset($this->$method)) {
			return $this->$method;
		}
		if (FrontController::$automatic_pages_controller === true) {
			Router::redirect('404', 404, 'Le contrôleur "{controller}" n\'existe pas', 'error', array('controller'=>$method));
// 			tr('Le contrôleur "{controller}" n\'existe pas', false, array('controller'=>$method));
// 			exit;
		} else {
			Router::redirect('404', 404, 'Le contrôleur "{controller}" n\'a pas de méthode "{method}"', 'error', array('controller'=>$this->name, 'method'=>$method));
// 			tr('Le contrôleur "{controller}" n\'a pas de méthode "{method}"', false, array('controller'=>$this->name, 'method'=>$method));
// 			exit;
		}
	}

	/**
	 * Récupère la liste des contrôleurs qui ont été instanciés
	 * @param string $name Si $name est défini, alors retourne la référence de l'instance demandée
	 * @return boolean | object | array
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static function get_instances($name = null) {
		if ($name === null) {
			return self::$instanciated;
		} elseif (isset(self::$instanciated[$name])) {
			return self::$instanciated[$name];
		} else {
			return false;
		}
	}

	/**
	 * Retourne une instance de l'objet Request
	 * @param object $set Lorsque le contrôleur est instancié, cette fonction affecte Request au contrôleur en plus de le retourner.
	 * @return object
	 * @see Request
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function request($set = null) {
		if (!$this->request && $set) { $this->request = $set; }
		return $this->request;
	}

	/**
	 * Retourne les données _GET dans Request
	 * @param string $name Si $name est fourni, retourne l'index demandé dans _GET s'il existe, null sinon.
	 * @return multitype La valeur de GET
	 * @see Request::get()
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function get($name = null) {
		if ($name === null) {
			return $this->request->get();
		} else {
			return $this->request->get($name) ?: null;
		}
	}

	/**
	 * Retourne les données _POST dans Request
	 * @param string $name Si $name est fourni, retourne l'index demandé dans _POST s'il existe, null sinon.
	 * @return multitype La valeur de POST
	 * @see Request::post()
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function post($name = null) {
		if ($name === null) {
			return $this->request->post;
		} else {
			return $this->request->post($name) ?: null;
		}
	}

	/**
	 * Retourne les valeurs qui doivent être envoyées à la vue
	 * @return array
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function vars() {
		return $this->vars;
	}

	/**
	 * Ajoute une variable à envoyer à la vue
	 * @param string $varname Le nom ou le chemin dans de la variable
	 * @param multitype $value
	 * @return integer 0 si rien n'a été fait (erreur).<br />1 si la valeur a été insérée.<br />2 si la valeur a été changée
	 * @uses Hash::insert
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function set($varname, $value) {
		$change = 0;
		if (!preg_match('~\.|\{|\}~isUu', $varname)) {
			if (isset($this->vars[$varname]) && $this->vars[$varname] != $value) {
				$change = 2;
			} elseif (!isset($this->vars[$varname])) {
				$change = 1;
			}
			$this->vars[$varname] = $value;
		} else {
			if (isset($this->vars[$varname]) && $this->vars[$varname] != $value) {
				$change = 2;
			} elseif (!isset($this->vars[$varname])) {
				$change = 1;
			}
			$args = Hash::insert($this->vars, $varname, $value);
			$this->vars = $args;
		}
		return $change;
	}

	/**
	 * Retourne le modèle demandé
	 * @param string $model_name Le nom du modèle
	 * @param boolean $return = true Si passé à false, le modèle sera créé dans le contrôleur et non retourné
	 * @return Model object or boolean
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function load_model($model_name, $return = true) {
		FrontController::$search_controller = true;
		if (!preg_match('~Model$~', $model_name)) {
			$model_name .= 'Model';
		}
		if (class_exists($model_name)) {
			if ($return === true) {
				return new $model_name();
			} else {
				$this->$model_name = new $model_name();
				return true;
			}
		} else {
			Session::setFlash('Le modèle {model} n\'existe pas.', 'warning', array('model'=>$model_name));
			return false;
		}
		FrontController::$search_controller = false;
	}

	/**
	 * Décharge le modèle du contrôleur.
	 * Uniquement si le modèle a été créé dans le contrôleur.
	 * @param string $model_name Le nom du modèle
	 */
	public function unload_model($model_name) {
		unset($this->{$model_name.'Model'});
	}

	/**
	 * Effectue le rendu de la vue demandée et le retourne.
	 * @param string $action Le nom de l'action à rendre
	 * @param boolean $return
	 * @return string Le contenu chargé
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function render($action, $return = false) {
		$action = preg_replace('~_action$~U', '', $action);
		$view_name = ucfirst($action).'View';
		$this->$view_name = new View($this->module, $action, $this);
		$this->$view_name->layout = $this->layout;
		$cnt = $this->$view_name->render($return);
		$this->rendered(true);
		return $cnt;
	}

	/**
	 * Vérifie que la méthode existe dans le contrôleur, et si c'est le cas, compte le nombre d'arguments qu'elle demande
	 * @param string $method
	 * @return number | boolean Le nombre d'arguments de cette méthode, false si la méthode n'existe pas
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function check_method_args($method) {
		if (method_exists($this, $method)) {
			$class = new ReflectionMethod($this, $method);
			$args = count($class->getParameters());
			return $args;
		} else {
			return false;
		}
	}

	/**
	 * Retourne la valeur de $this->rendered
	 * @param boolean $render Si $render est spécifié, alors on affecte le contenu à $this->rendered
	 * @return boolean
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function rendered($render = null) {
		if ($render === null) {
			return $this->rendered;
		} else {
			$this->rendered = (bool) $render;
			return (bool) $render;
		}
	}
}