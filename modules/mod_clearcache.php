<?php

use App\FileAndDir;

$_PAGE['layout'] = 'ajax';

//Suppression des CSS
$css = P_CSS.DS.'pages'.DS;
FileAndDir::delete_directory_file($css);
FileAndDir::remove(P_CSS.DS.'main.min.css');

//Suppression des JS
$js = P_JS.DS.'pages'.DS;
FileAndDir::delete_directory_file($js);
FileAndDir::remove(P_JS.DS.'main.min.js');

//Suppression des feuilles de personnage
FileAndDir::remove_directory(CHAR_EXPORT.DS);

//Suppression du cache SQL
FileAndDir::remove(ROOT.DS.'logs'.DS.'cache_sql'.DS.'cache.php');

//On force le navigateur à recharger la page
header('Pragma: no-cache');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Thu, 03 Jan 2013 06:23:00 GMT');

//Si le referer n'est pas la page en cours, on renvoie vers le referer. Sinon, vers la page d'accueil
if (isset($_PAGE['referer']['id']) && isset($_PAGE['list'][$_PAGE['referer']['id']]) && $_PAGE['referer']['id'] != $_PAGE['id']) {
	$url = $_PAGE['referer']['full_url'];
} else {
	$url = array('val'=>1);
}

redirect($url, 'Le cache du site a été vidé', 'success');
