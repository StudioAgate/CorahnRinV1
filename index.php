<?php
/**
 * Définitions des paramètres de fonctionnalités du site
 * Contient les chargements de fonctions, les constantes principales des chemins de fichier
 * Effectue le chargement des modules du site en fonction de l'url
 */

## Charge tous les paramètres de base du site
require 'config.php';

## Chargement des classes en ajoutant le dossier "class" à l'include_path
// define('CLASS_DIR', ROOT.DS.'class');//Dossier des classes
// set_include_path(get_include_path().PATH_SEPARATOR.CLASS_DIR);//On l'ajoute à l'include_path
// spl_autoload_extensions('.class.php');//On ajoute l'extension ".class.php" pour les fichiers
// spl_autoload_register();//On charge l'autoload classique

## Chargement des fonctions
$function_inc = array(
	'__autoload',
	'_aliases_for_classes_methods',
	'buffWrite',
	'create_zip',
	'error_logging',
	'getXPFromAvtg',
	'getXPFromDiscs',
	'getXPFromDoms',
	'get_post_datas',
	'git_update',
	'goto_404',
// 	'is_blacklisted',
// 	'imagick_text',
	'is_correct_email',
	'load_module',
	'minify',
	'mkurl',
	'p_array_diff_recursive',
	'p_dump',
	'phpMailer_filters',
// 	'print_r_to_array',
// 	'redirect',
	'remove_accents',
	'send_mail',
	'url_exists',
);
$func_time = microtime(true);
foreach ($function_inc as $val) {
	$filename = ROOT.DS.'functions'.DS.$val.'.func.php';
	if (file_exists($filename)) {
		require $filename;
	} else {
		tr('Erreur dans le chargement de la fonction '.$val);
		exit;
	}
}
unset($function_inc,$val,$filename);

## On démarre la session
Session::init();

## Initialisation de la classe de traduction
Translate::init();

## Configuration de la base de données
require ROOT.DS.'db.php';

## Initialisation de l'utilisateur
Users::init((int) Session::read('user'));
define('P_LOGGED',	(Users::id() > 0 ? true : false));
define('P_DEBUG',	(Users::id() == 1 ? true : false));

## On va créer la requête dans la variable $_PAGE
// require ROOT.DS.'request.php';

## On charge le module Git au cas où une mise à jour est prévue.
// require ROOT.DS.'git.php';

ob_start();
new FrontController();
$layout = ob_get_clean();

Translate::translate_writewords();//On enregistre les mots à traduire

$global_time = (microtime(true) - $global_time)*1000;## On arrête le calcul de temps d'exécution du script pour pouvoir l'enregistrer
// $f = fopen(P_EXECTIME_LOGFILE, 'a');##On stocke le temps d'exécution dans le fichier log
$final = "*|*|*Date=>".json_encode(date(DATE_RFC822))
.'||Ip=>'.json_encode($_SERVER['REMOTE_ADDR'])
// 	.'||Referer=>'.json_encode(@$_SERVER['HTTP_REFERER'])
// .'||Page.get=>'.json_encode($_PAGE['get'])
// .'||Page.request=>'.json_encode((array)@$_PAGE['request'])
// .'||GET=>'.json_encode((array)$_GET)
.'||User.id=>'.json_encode(Users::id())
.'||Exectime=>'.json_encode($global_time);
$final = preg_replace('#\n|\r|\t#isU', '', $final);
$final = preg_replace('#\s\s+#isUu', ' ', $final);

$layout = str_replace('{PAGE_TIME}', number_format($global_time, 4, ',', ' '), $layout);## On affiche le message de temps d'exécution
FileAndDir::put(P_EXECTIME_LOGFILE, $final, FILE_APPEND);
unset($final);
echo $layout;
exit;

/*
## Récupération du module dans $module
// ob_start();
// 	if (file_exists(ROOT.DS.'pages'.DS.'mod_' . $_PAGE['get'] . '.php')) {//S'il existe on le charge
// 		load_module($_PAGE['get'], 'page');
// 	} else {
// 		goto_404();
// 	}
// $_PAGE['content_for_layout'] = ob_get_clean();
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

if (PHP_SAPI === 'cli') {
	$_PAGE['layout'] = 'cli';
}


$global_time = (microtime(true) - $global_time)*1000;## On arrête le calcul de temps d'exécution du script pour pouvoir l'enregistrer
// $f = fopen(P_EXECTIME_LOGFILE, 'a');##On stocke le temps d'exécution dans le fichier log
$final = "*|*|*Date=>".json_encode(date(DATE_RFC822))
	.'||Ip=>'.json_encode($_SERVER['REMOTE_ADDR'])
// 	.'||Referer=>'.json_encode(@$_SERVER['HTTP_REFERER'])
	.'||Page.get=>'.json_encode($_PAGE['get'])
	.'||Page.request=>'.json_encode((array)@$_PAGE['request'])
	.'||GET=>'.json_encode((array)$_GET)
	.'||User.id=>'.json_encode(Users::$id)
	.'||Exectime=>'.json_encode($global_time);
$final = preg_replace('#\n|\r|\t#isU', '', $final);
$final = preg_replace('#\s\s+#isUu', ' ', $final);

$_LAYOUT = str_replace('{PAGE_TIME}', number_format($global_time, 4, ',', ' '), $_LAYOUT);## On affiche le message de temps d'exécution
file_put_contents(P_EXECTIME_LOGFILE, $final, FILE_APPEND);
unset($final);

if (array_key_exists('only_exectime', $_GET)) {
	echo $global_time;
	exit;
}
if (is_string($_LAYOUT) && !empty($_LAYOUT)) { echo $_LAYOUT; }##On affiche finalement la page
//*/