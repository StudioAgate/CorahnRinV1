<?php

## Récupération de $_POST à partir des réelles données POST, pour obtenir les bons noms de variable entrées en paramètre,
// $_POST = get_post_datas();

## Définition de la constante BASE_URL. Source : http://www.koezion-cms.com/
use App\FileAndDir;
use App\Session;
use App\Users;

if (isset($_SERVER['BASE'])) {
    $baseUrl = $_SERVER['BASE'];
} else {
    $baseUrl = '';
    $scriptPath = preg_split("#[\\\\/]#", __DIR__, -1, PREG_SPLIT_NO_EMPTY);
    $urlPath = preg_split("#[\\\\/]#", $_SERVER['REQUEST_URI'], -1, PREG_SPLIT_NO_EMPTY);
    foreach($urlPath as $k => $v) {
        $key = array_search($v, $scriptPath, true);
        if($key !== false) {
            $baseUrl .= '/'.$v;
        } else {
            break;
        }
    }
}
define('BASE_URL', 'http'.(is_ssl()?'s':'').'://'.P_BASE_HOST.$baseUrl);//url absolue du site
unset($baseUrl, $scriptPath, $urlPath, $k, $v, $key);

/**
 * On crée la variable $_PAGE['request'] pour obtenir les paramètres découpés par les '/'
 */
$request = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
if (isset($_SERVER['BASE'])) {
    $request = str_replace($_SERVER['BASE'], '', $request);
}
$request = preg_replace('#\?.*$#Uu', '', $request);
$ext = pathinfo($request);
$ext = isset($ext['extension']) ? strtolower($ext['extension']) : '';//On génère l'extension de l'url
$request = preg_replace('#\.([a-zA-Z0-9]{1,6})$#iUu', '', $request);
$request = preg_replace('~^/esteren~i', '', $request);

if ($request) {
	$request = preg_split('~/~', $request, null, PREG_SPLIT_NO_EMPTY);
	$lang = array_shift($request);
	if ('index.php' === $lang) {
	    $lang = array_shift($request);
    }
    if (!$lang) {
        $lang = 'fr';
    }
	$getmod = array_shift($request);
	if ($lang === $getmod) {
	    // Fix this stupid thing that happens when uri has twice the language in the url.
        header('Location: '.str_replace("/$lang/$lang", "/$lang", $_SERVER['REQUEST_URI']));
        exit;
    }
} else {
	$request = array();
    $lang = null;
	$getmod = null;
}

if ($lang !== 'fr' && $lang !== 'en') {
    $url = base_url().'/fr/'.$getmod;
    if ($getmod && count($request)) {
        $url .= '/'.implode('/', $request);
    }
    redirect($url);
    exit;
}

$t = array();
if ($ext === $getmod) { $ext = ''; }
foreach($request as $v) {
	if (strpos($v, ':') !== false) {
		$v = explode(':', $v, 2);
        if ($v[1]) {
		    $t[$v[0]] = $v[1];
        }
	} elseif ($v) {
		$t[] = $v;
	}
}
$request = $t;

/**
 * On crée la variable $_GET pour obtenir les informations en GET
 */
$get_parameters = $_SERVER['REQUEST_URI'];
if (false !== strpos($get_parameters, "?")) {
	$get_parameters = preg_replace('#^[^?]+\?#Uu', '', $get_parameters);
	$get_parameters = explode('&', $get_parameters);
	$t = array();
	foreach($get_parameters as $k => $v) {
		$v = explode('=', $v);
		$t[$v[0]] = isset($v[1]) ? $v[1] : null;
		$_GET[$v[0]] = isset($v[1]) ? $v[1] : null;
	}
	$get_parameters = $t;
} else {
	$get_parameters = array();
}

$_PAGE['request'] = $request;
unset($request);
$_GET = array_map('urldecode', $get_parameters);
foreach ($_GET as $key => $param) {
    if (!$key) {
        unset($_GET[$key]);
    }
}
unset($_GET['request'], $t, $get_parameters);


// Gestion de la traduction insérée en page
if ($lang === 'fr' || $lang === 'en') {
    ## La langue est désormais définie ici pour la compatibilité avec le nouveau système de langue
    define('P_LANG', $lang);
} else {
    redirect(base_url().'/fr/404.html');
    exit;
}


/**
 * Définition de la variable $_PAGE
 * Celle-ci est chargée de gérer la liste des pages.
 * On force sa portée en globale notamment en la chargeant dans la plupart des librairies susceptibles de la gérer,
 * comme le gestionnaire d'urls (mkurl) ou le chargement de modules (load_module)
 */
$_PAGE['get'] = is_string($getmod) && $getmod ? $getmod : 'index';
$_PAGE['id'] = null;
$_PAGE['extension'] = $ext;
$_PAGE['style'] = 'corahn_rin';//id CSS de la balise body
$_PAGE['anchor'] = '';
$_PAGE['list'] = [];
$_PAGE['more_js'] = [];
$_PAGE['more_css'] = [];

$cacheFile = CACHE_DIR.DS.'requestlist.php';
if (file_exists($cacheFile) && filemtime($cacheFile) >= (time() - 864000) && $cnt = file_get_contents($cacheFile)) {
    $_PAGE['list'] = require $cacheFile;
} else {
    $result = $db->req('SELECT  %page_id, %page_show_in_menu, %page_show_in_debug, %page_getmod, %page_anchor, %page_acl, %page_require_login FROM %%pages ORDER BY %page_anchor ASC');

    if ($result) {
        foreach ($result as $data) {
            $_PAGE['list'][$data['page_id']] = $data;
        }
        unset($result);
    }
    unset($result, $data);
    $pageToSave = var_export($_PAGE['list'], true);
    file_put_contents($cacheFile, "<?php\nreturn $pageToSave;");
}

if (!$_PAGE['id'] || !isset($_PAGE['list'][$_PAGE['id']])) {
    $page = null;
    foreach ($_PAGE['list'] as $id => $data) {
        if (((string) $data['page_getmod']) === '404') {
            $page = $data;
            break;
        }
    }
    if (!$page) {
        header('HTTP/1.0 404 Not Found');
        header('Status: 404 Not Found');
        echo 'Not found.';
        exit;
    }
    $_PAGE['id'] = (int) $page['page_id'];
    $_PAGE['anchor'] = $page['page_anchor'];
    $_PAGE['acl'] = (int) $page['page_acl'];
    $_PAGE['extension'] = 'html';
}

foreach ($_PAGE['list'] as $data) {
    if ($_PAGE['get'] === $data['page_getmod'] || $_PAGE['id'] === $data['page_id']) {
        if (Users::$acl > $data['page_acl'] || (P_LOGGED === false && $data['page_require_login'] === 1)) {
            Session::setFlash("Vous n'avez pas les droits pour accéder à cette page.", 'error');
            header('Location:'.mkurl(array('val'=>1)));
            exit;
        }
        $_PAGE['id'] = (int) $data['page_id'];
        $_PAGE['anchor'] = $data['page_anchor'];
        $_PAGE['acl'] = (int) $data['page_acl'];
    }
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], (string)$data['page_getmod']) !== false) {
        $_PAGE['referer'] = array(
            'id' => (int) $data['page_id'],
            'getmod' => $data['page_getmod'],
            'anchor' => $data['page_anchor'],
            'full_url' => $_SERVER['HTTP_REFERER'],
        );
    }
}

if (!$_PAGE['extension'] && $_PAGE['get'] !== 'index') {
    $url = base_url().'/fr/'.$_PAGE['get'];
    if (count($_PAGE['request'])) {
        $url .= '/'.implode('/', $_PAGE['request']);
    }
    $url .= '.html';
    redirect($url);
    exit;
}

## Si le module chargé est "index" alors on redirige vers une page d'accueil possédant une url "saine"
if ($getmod === 'index') { redirect(base_url(true)); }
unset($getmod);

##On définit le referer en fonction de ce que l'on a dans HTTP_REFERER.
##Si celui-ci est sur ce site, on récupère ses paramètres dans $_PAGE. Sinon, uniquement son url.
if (isset($_SERVER['HTTP_REFERER']) && !isset($_PAGE['referer'])) {
	if ($_SERVER['HTTP_REFERER'] === base_url().'/') {
		$_PAGE['referer'] = array(
			'id' => 1,
			'getmod' => $_PAGE['list'][1]['page_getmod'],
			'anchor' => $_PAGE['list'][1]['page_anchor'],
			'full_url' => $_SERVER['HTTP_REFERER'],
		);
	} else {
		$_PAGE['referer'] = array(
			'id' => 0,
			'getmod' => '',
			'anchor' => 0,
			'full_url' => $_SERVER['HTTP_REFERER'],
		);
		//Si le referer n'existe pas sur ce site, alors on enregistre l'url dans les log pour des raisons statistiques
        $logfile = ROOT.DS.'logs'.DS.'referer'.DS.date('Y.m.d').'.log';
        if (!is_dir(dirname($logfile))) {
            FileAndDir::createPath(dirname($logfile));
            FileAndDir::put($logfile, '');
        }
		$f = fopen($logfile, 'ab');##On stocke le temps d'exécution dans le fichier log
		$final = '*|*|*Date=>'.json_encode(date(DATE_RFC822))
		.'||Ip=>'.json_encode($_SERVER['REMOTE_ADDR'])
		.'||Referer=>'.json_encode($_SERVER['HTTP_REFERER'] ?: 'Accès direct au site')
		.'||Page.get=>'.json_encode($_PAGE['get'])
		.'||Page.request=>'.json_encode(@$_PAGE['request'])
		.'||Page.get_params=>'.json_encode($_GET)
		.'||User.id=>'.json_encode(Users::$id);
		$final = str_replace(["\n", "\r", "\t"], '', $final);
		$final = preg_replace('#\s\s+#Uu', ' ', $final);
		fwrite($f, $final);
		fclose($f);
		unset($f, $final);
	}
}
