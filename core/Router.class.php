<?php
/**
 * Le routeur est chargé de gérer tout ce qui concerne les routes mais aussi la création d'urls
 * @author Pierstoval 01/08/2013
 * @version 1.0
 */
class Router extends Object {

	/**
	 * Le fichier de config des routes du coeur du système
	 * Par défaut ROOT.DS.'cache'.DS.'routes.json'
	 * @var String
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static $routes_core_file = '';

	/**
	 * Sera passé à true lorsque les routes seront chargées
	 * @var boolean
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static $routes_loaded = false;

	/**
	 *
	 * @var boolean
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static $routes = array();

	/**
	 *
	 * @var boolean
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static $last_route = array();

	/**
	 * Cette variable contient les valeurs par défaut utilisées pour la méthode self::link()
	 * @var array<br />
		'route'				=> '',<br />
		'route_name'		=> '',<br />
		'route_type'		=> 'uri',<br />
		'type'				=> 'href',<br />
		'anchor'			=> '',<br />
		'ext'				=> 'html',<br />
		'get'				=> array(),<br />
		'attr'				=> array(),<br />
		'aftertag'			=> '',<br />
		'beforetag'			=> '',<br />
		'custom'			=> false,<br />
		'translate'			=> false,<br />
		'translate_params'	=> array(),
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static $defaults = array(
		'route'				=> '',
		'route_name'		=> '',
		'route_type'		=> 'uri',
		'force_route'		=> false,
		'type'				=> 'href',
		'anchor'			=> '',
		'ext'				=> 'html',
		'get'				=> array(),
		'attr'				=> array(),
		'aftertag'			=> '',
		'beforetag'			=> '',
		'custom'			=> false,
		'translate'			=> false,
		'translate_params'	=> array(),
	);

	/**
	 * Charge les routes à partir du fichier source, ainsi que les routes des modules
	 * @return array Liste des routes
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	private static function load_routes() {
		self::$routes_core_file = ROOT.DS.'cache'.DS.'routes.json';
		$cnt = file_get_contents(ROOT.DS.'configs'.DS.'routes.json');
		$routes_core = array();
		if ($cnt) {
			$routes_core = json_decode($cnt, true);
			unset($cnt);
			if (!$routes_core) {
				tr('Erreur dans le chargement des routes de base');
				return exit;
			}
		} else {
			touch(ROOT.DS.'configs'.DS.'routes.json');
		}
		$routes_modules = self::load_routes_modules();
		$routes = array_merge($routes_core, $routes_modules);
		foreach ($routes as $k => $v) {
			if (!isset($v['uri'])) { $routes[$k]['uri'] = $v['route']; }
		}
		self::$routes_loaded = true;
		self::$routes = $routes;
		return $routes;
	}

	/**
	 * Renvoie les routes des modules
	 * @return array
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	private static function load_routes_modules() {
		$dir_modules = ROOT.DS.'modules'.DS;
		$modules = scandir($dir_modules);
		$routes = array();
		foreach ($modules as $v) {
			if ($v !== '.' && $v !== '..') {
				if (file_exists($dir_modules.$v.DS.'routes.json')) {
					$cnt = file_get_contents($dir_modules.$v.DS.'routes.json');
					$cnt = json_decode($cnt, true);
					if ($cnt && is_array($cnt)) { $routes = array_merge($cnt, $routes); }
				} elseif (file_exists($dir_modules.$v.DS.'routes.php')) {
					$cnt = file_get_contents($dir_modules.$v.DS.'routes.php');
					$cnt = eval($cnt);
					if (isset($module_routes) && is_array($module_routes)) { $routes = array_merge($module_routes, $routes); }
					unset($module_routes);
				}
			}
		}
		return $routes;
	}

	/**
	 * Vérifie l'existence d'une route par tous les paramètres possibles
	 *
	 * @param string $route_name Le nom, l'uri ou autre qui correspond à la route
	 * @return boolean
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static function check($route_name) {
		$routes = self::$routes;
		$route_name = trim($route_name, '/');
		if (isset($routes[$route_name])) {
			return true;
		}
		foreach ($routes as $k => $v) {
			foreach ($v as $param) {
				$param = trim($param, '/');
				if ($param === $route_name) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Récupère une route dans la liste si elle existe, renvoie false sinon
	 *
	 * @param string $route_name La valeur à chercher en fonction du champ suivant
	 * @param string $field Le champ à récupérer
	 * @param string $search La recherche. 'name' pour le nom de la route, sinon, le nom d'un champ des tableaux de routes
	 * @return boolean|mixed La route si elle est trouvée, false sinon
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static function get($route_name, $field = 'uri', $search = 'name') {
		$routes = self::$routes;
		if ($search === 'name') {
			if (isset($routes[$route_name])) {
				if (isset($routes[$route_name][$field])) {
					return $routes[$route_name][$field];
				} else {
					return false;
				}
			} else {
	// 			Session::setFlash('La route {route_name} n\'existe pas.', 'warning', array('route_name'=>$route_name));
				return false;
			}
		} else {
			foreach ($routes as $k => $v) {
				if (isset($v[$search]) &&
				(preg_match('~^/?'.$v[$search].'$~isUu', $route_name) || preg_match('~^/?'.$route_name.'$~isUu', $v[$search]))) {
					$v['name'] = $k;
					return $v;
				}
			}
			return false;
		}
	}

	/**
	 * Retourne la liste des routes enregistrées
	 * @return array La liste des routes
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static function get_routes() {

		if (self::$routes_loaded === false) {
			self::load_routes();
		}

		return self::$routes;
	}

	/**
	 * En fonction de la requête, cherche si une route existe dans la liste, et renvoie le remplacement
	 *
	 * @param string $request La requête
	 * @param string $route_name Le nom de la route si on force la recherche en fonction du nom
	 * @param string $flags Les flags de la regex
	 * @return mixed La route si elle est trouvée
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static function route($request, $route_name = '', $flags = 'isUu') {

		if (self::$routes_loaded === false) { self::load_routes(); }//Au cas où les routes ne sont pas chargées, on force le chargement

		foreach (self::$routes as $name => $rt) {//On boucle sur chaque route enregistrée
			$regex = '~^'.$rt['route'].'$~'.$flags;
			$redirect = $rt['redirect'];

			self::$last_route = array('name'=>$name, 'route'=>(object)$rt);

			if ($route_name === $name && preg_match($regex, $redirect)) {
// 				self::$last_route = array('name'=>$name, 'route'=>$rt);
				return preg_replace($regex, $redirect, $request);
			} elseif ($route_name === $name && !preg_match($regex, $redirect)) {
// 				self::$last_route = array('name'=>$name, 'route'=>$rt);
				return $redirect;
			} elseif (preg_match($regex, $request)) {
				return preg_replace($regex, $redirect, $request);
			}
		}
		return $request;
	}

	/**
	 * Effectue une redirection vers une url, en appliquant correctement le code HTTP spécifié, et en ajoutant si demandé un message Flash
	 *
	 * @param mixed $url Si $url est une chaîne, alors on cherche la route spécifiée et on crée le lien.
	 * <br />Si c'est un tableau, alors on va utiliser Router::link pour créer le lien
	 * @param number $code Le code HTTP de la redirection
	 * @param string $message Si un message est spécifié, il sera ajouté aux messages Flash
	 * @param string $flash_error Le type d'erreur à émettre
	 * @param array $tr_params Des paramètres de traduction à envoyer à Translate pour le message Flash
	 * @see Router::link()
	 * @see Session::setFlash()
	 * @see Translate::translate()
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static function redirect($url, $code = 200, $message = null, $flash_error = 'notif', $tr_params = array()) {

		//Code de redirection possibles
		$http_codes = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Time-out',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Large',
			415 => 'Unsupported Media Type',
			416 => 'Requested range not satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Time-out'
		);

		if ($message !== null) {
			Session::setFlash($message, $flash_error, $tr_params);
		}

		if($code && array_key_exists($code, $http_codes)) { header("HTTP/1.0 ".$code." ".$http_codes[$code]); } //Si un code est passé on l'indique dans le header

		if (is_string($url) && self::check($url)) {
			$url = Router::route($url, $url);
		}

		if ($url === 'home') {
			$url = array('route_name'=>'core_home', 'force_route'=>true, 'type'=>'redirect');
		}
		if (is_array($url)) {
			$url = self::link($url);
		}

		if (strpos($url, 'http://') === false && strpos($url, BASE_URL) === false) {
			$url = preg_replace('~^/~', '', $url);
			$url = BASE_URL.'/'.$url;
		}

		if(isset($params)) {$url .= '?'.$params; }

		header('Location: '.$url);

		exit; //Pour éviter que le script ne continue
	}

	/**
	 * Crée un lien ou une balise &lt;a&gt; en fonction des paramètres saisis dans $params
	 * @param array $params
	 * @return boolean | string L'url formatée si réussi, false sinon
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static function link($params = array()) {

		$final = '';//Cette variable contient la chaîne de caractère du résultat

		$params = array_merge(self::$defaults, $params);//On récupère les paramètres de l'utilisateur

		$params = array(//On sécurise toutes les variables
			'route'				=> (string) $params['route'],
			'route_name'		=> (string) $params['route_name'],
			'route_type'		=> (string) $params['route_type'],
			'force_route'		=> (bool) $params['force_route'],
			'type'				=> (string) strtolower($params['type']),
			'anchor'			=> (string) $params['anchor'],
			'attr'				=> (array) $params['attr'],
			'ext'				=> (string) ($params['ext']),
			'get'				=> (array) $params['get'],
			'aftertag'			=> (string) $params['aftertag'],
			'beforetag'			=> (string) $params['beforetag'],
			'custom'			=> (bool) $params['custom'],
			'translate' 		=> (bool) $params['translate'],
			'translate_params'	=> (array) $params['translate_params'],
		);


// 		if (!$params['route'] && $params['route_name']) { $params['force_route'] = true; }//On force le routage si on a un nom de route et pas de route en paramètre

		if ($params['force_route'] === false && $params['custom'] === false && $params['route'] && !$params['route_name']) {
			$route = self::route($params['route']);//On demande au routeur de parser la route si elle existe

		} elseif (($params['force_route'] === false && $params['custom'] === false && !$params['route'] && $params['route_name']) || $params['force_route'] === true) {
			$route = self::get($params['route_name'], $params['route_type']);

		} else {
			$route = $params['route'] ?: ($params['route_name'] ?: false);
		}
		if ($route === false) {
// 			tr('Erreur de lien : route introuvable.');
			return false;
		}

		$route = preg_replace('~^/~U', '', $route);

		$get_params = '';
		//Gestion des paramètres GET de l'url (s'il y en a) pour les types 'href' et 'tag'
		if (!empty($params['get'])
			&& ($params['type'] === 'href'
				|| $params['type'] === 'tag')
		) {
			foreach($params['get'] as $k => $v) {
				if ($get_params) { $get_params .= ($params['type'] === 'tag' ? '&amp;' : '&'); }
				if (!is_numeric($k)) {
					$get_params .= $k.'='.urlencode($v);
				} else {
					$get_params .= urlencode($v).'=';
				}
			}
		}
		if ($get_params) { $get_params = '?'.$get_params; }


		//Création du résultat
		if ($params['type'] === 'get') {//Uniquement la valeur
			$final = $route;

		} elseif ($params['type'] === 'href') {//Uniquement le lien, ou l'attribut "href" des balises html

			if (!preg_match('~(ht|f)tp://~U', $route)) {
				$final = BASE_URL.'/'.$route;
			} else {
				$final = $route;
			}

			if ($final !== BASE_URL && $final !== BASE_URL.'/' && !preg_match('~\.('.$params['ext'].'|html)$~U', $final) && $params['custom'] === false && $params['ext']) {
				$final .= '.'.$params['ext'];
			}
			$final .= ($get_params ? $get_params : '');

		} elseif ($params['type'] === 'tag') {//Création d'une balise <a> complète

			if (!preg_match('~(ht|f)tp://~U', $route)) {
				$href = BASE_URL.'/'.$route;
			} else {
				$href = $route;
			}

			if ($href !== BASE_URL && $href !== BASE_URL.'/' && !preg_match('~\.('.$params['ext'].'|html)$~U', $href) && $params['custom'] === false) {
				$href .= '.'.$params['ext'];
			}
			$href .= ($get_params ? $get_params : '');

			$attr = '';
// 			if (!isset($params['attr']['title'])
// 			|| (isset($params['attr'][0])
// 					&& strpos($params['attr'][0], 'title') === false && !isset($params['attr']['title'])
// 			) || empty($params['attr'])
// 			) {
// 				$params['attr']['title'] = $page['page_anchor'];//On définit un attribut title s'il n'a pas été ajouté dans les paramètres 'attr' du lien
// 			}
			foreach ($params['attr'] as $param => $value) {
				if (is_numeric($param)) {
					$attr .= ' '.$value;
				} else {
					$attr .= ' '.$param.'="'.$value.'"';
				}
			}
			if (!$params['anchor']) {
				$params['anchor'] = $href;// Si $params['anchor'] est vide et qu'on crée une url personnaliée, alors on affiche l'url elle-même par défaut
			}
			if ($params['translate'] === true) {
				$params['anchor'] = tr($params['anchor'], true);
			}

			$final = $params['beforetag'].'<a href="'.$href.'"'.$attr.'>'.$params['anchor'].'</a>'.$params['aftertag'];
		}
		return $final;
	}
}