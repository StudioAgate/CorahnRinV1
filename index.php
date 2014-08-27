<?php
/**
 * Définitions des paramètres de fonctionnalités du site
 * Contient les chargements de fonctions, les constantes principales des chemins de fichier
 * Effectue le chargement des modules du site en fonction de l'url
 */

define('ROOT', dirname(__FILE__)); //Chemin vers le dossier racine
define('DS', DIRECTORY_SEPARATOR); //Définition du séparateur dans le cas ou l'on est sur windows ou linux

define('WEBROOT',		ROOT.DS.'webroot');
define('P_FONTS',		WEBROOT.DS.'css'.DS.'fonts');
define('P_CSS',			WEBROOT.DS.'css');
define('P_JS',			WEBROOT.DS.'js');
define('CHAR_EXPORT',	WEBROOT.DS.'files'.DS.'characters_export');

## Chargement des classes
$class_inc = array(
	'bddPDO',
	'encoding',
	'esterenchar',
	'minifier',
	'translate',
	'users',
	'fpdf',
	//'fpdfMakefont',
	//'fpdfTTFparser',
	'tfpdf',
	'tfpdfTTFonts',
	'mailerEasyPeasyICS',
	'mailerHtml2text',
	'mailerNtlm_sasl_client',
	'mailerPhpmailer',
	'mailerPop3',
	'mailerSmtp',
	'cakePhpFileAndDir',
	'cakePhpInflector',
	'cakePhpHash',
	'cakePhpSession',
	'cakePhpSet',
	'cakePhpString',
);
foreach ($class_inc as $val) {
	$filename = ROOT.DS.'class'.DS.'class_'.$val.'.php';
	if (file_exists($filename)) {
		require $filename;
	} else {
		echo 'Erreur dans le chargement de la classe '.$val;
		exit;
	}
}
unset($class_inc);

## Chargement des fonctions
$function_inc = array(
	'arrayDiffRecursive',
	'buffwrite',
	'createZip',
	'getPostDatas',
	'getXPFromAvtg',
	'getXPFromDiscs',
	'getXPFromDoms',
	'errorLogging',
	'goto404',
    'httpCode',
	'isBlacklisted',
	'minify',
	'isCorrectEmail',
	'mkurl',
	'mailerHtmlfilter',
	'pDump',
	'printrToArray',
	'loadModule',
	'removeAccents',
	'sendMail',
	'urlExists',
	'redirect',
);
foreach ($function_inc as $val) {
	$filename = ROOT.DS.'functions'.DS.'func_'.$val .'.php';
	if (file_exists($filename)) {
		require $filename;
	} else {
		echo 'Erreur dans le chargement de la fonction '.$val;
		exit;
	}
}
unset($function_inc,$val,$filename);

## On démarre la session
Session::init();

## On récupère le Host original en cas d'url du type 127.0.0.1:8080, pour conserver le port
define('P_BASE_HOST', $_SERVER['HTTP_HOST']);

## Redéfinition de HTTP_HOST pour éviter les problèmes de compatibilité sur les serveurs locaux, ou les changements de ports avec EasyPHP ou WAMP par exemple
if (preg_match('#127\.0\.0\.1|localhost#isUu', $_SERVER['HTTP_HOST'])) {
	$_SERVER['HTTP_HOST'] = '127.0.0.1';//On définit le serveur local
}
## Configuration de la base de données
if (!FileAndDir::fexists(ROOT.DS.'db.php')) {
    echo "Database not configured.";
    exit;
}
require ROOT.DS.'db.php';

## On charge tous les paramètres de base du site (variable $_PAGE, session, etc)
require ROOT.DS.'config.php';

## Initialisation de l'utilisateur
Users::init((int) Session::read('user'));
define('P_LOGGED',	(Users::$id > 0 ? true : false));
define('P_DEBUG',	(Users::$id == 1 ? true : false));

## On va créer la requête dans la variable $_PAGE
require ROOT.DS.'request.php';
Translate::$_PAGE = $_PAGE;

## On charge le module Git au cas où une mise à jour est prévue.
//require ROOT.DS.'git.php';

##On définit le layout par défaut
$_PAGE['layout'] = 'default';

## Récupération du module dans $module
ob_start();
	if (file_exists(ROOT.DS.'modules'.DS.'mod_' . $_PAGE['get'] . '.php')) {//S'il existe on le charge
		load_module($_PAGE['get'], 'page');
	} else {
		goto_404();
	}
$_PAGE['content_for_layout'] = ob_get_clean();
## Fin de récupération du module

## Création de la variable contenant la navigation
if ($_PAGE['layout'] === 'default') {
	ob_start();
	load_module('', 'menu');
	//include ROOT.DS.'includes'.DS.'inc_nav.php';
	$_PAGE['nav_for_layout'] = ob_get_clean();
}

$_LAYOUT = '';
ob_start();
	if (file_exists(ROOT.DS.'layouts'.DS.'layout_'.$_PAGE['layout'].'.php')) {
		load_module($_PAGE['layout'], 'layout');
		//require ROOT.DS.'layouts'.DS.'layout_'.$_PAGE['layout'].'.php';//S'il existe on le charge
	} else {
		Session::setFlash('Le layout "'.$_PAGE['layout'].'" n\'existe pas.', 'error');
		load_module('default', 'layout');
		//require ROOT.DS.'layouts'.DS.'layout_default.php';//On charge par défaut sinon
	}
$_LAYOUT = ob_get_clean();
unset($content_for_layout);

Translate::translate_writewords();//On enregistre les mots à traduire

if (strpos($_LAYOUT, '{PAGE_TIME}') !== false) {
    $time = (microtime(true) - $time)*1000;
    $_LAYOUT = str_replace('{PAGE_TIME}', number_format($time, 4, ',', ' '), $_LAYOUT);## On affiche le message de temps d'exécution
    $logfile = ROOT.DS.'logs'.DS.'exectime'.DS.date('Y.m.d').'.log';
    if (!is_dir(dirname($logfile))) {
        FileAndDir::createPath(dirname($logfile));
        touch($logfile);
    }
    $f = fopen($logfile, 'a');##On stocke le temps d'exécution dans le fichier log
    $final = "*|*|*Date=>".json_encode(date(DATE_RFC822))
        .'||Ip=>'.json_encode($_SERVER['REMOTE_ADDR'])
    // 	.'||Referer=>'.json_encode(@$_SERVER['HTTP_REFERER'])
        .'||Page.get=>'.json_encode($_PAGE['get'])
        .'||Page.request=>'.json_encode((array)@$_PAGE['request'])
        .'||GET=>'.json_encode((array)$_GET)
        .'||User.id=>'.json_encode(Users::$id)
        .'||Exectime=>'.json_encode($time);
    $final = preg_replace('#\n|\r|\t#isU', '', $final);
    $final = preg_replace('#\s\s+#isUu', ' ', $final);
    fwrite($f, $final);
    fclose($f);
    unset($f, $final);
}

if (is_string($_LAYOUT) && !empty($_LAYOUT)) { echo $_LAYOUT; }##On affiche finalement la page
