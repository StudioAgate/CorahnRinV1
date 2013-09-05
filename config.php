<?php
/**
 * Fichier de configuration
 * Définition de toutes les constantes et paramètres utilisées sur le site
 * Chaque constante commence par le préfixe "P_" pour être reconnaissable, exceptées celles concernant les chemins de fichiers et urls
 * Créées par Alexandre Ancelet - Pierstoval
 * 2012-2013
 */

## Définition de la locale
date_default_timezone_set('Europe/Paris');
setlocale(LC_ALL, array('fr_FR', 'fr_FR.UTF-8', 'French_France', 'french'));

define('ROOT', dirname(__FILE__)); //Chemin vers le dossier racine
define('DS', DIRECTORY_SEPARATOR); //Définition du séparateur dans le cas ou l'on est sur windows ou linux

define('P_CLASS',		ROOT.DS.'class');
define('P_CORE',		ROOT.DS.'core');
define('P_LIBS',		ROOT.DS.'core'.DS.'libs');
define('P_MODULES',		ROOT.DS.'modules');
define('P_TRANSLATION',	ROOT.DS.'translation');
define('WEBROOT',		ROOT.DS.'webroot');
define('P_FONTS',		WEBROOT.DS.'css'.DS.'fonts');
define('P_CSS',			WEBROOT.DS.'css');
define('P_JS',			WEBROOT.DS.'js');
define('CHAR_EXPORT',	WEBROOT.DS.'files'.DS.'characters_export');

## Constantes liées à tFPDF pour les fichiers externes
define('P_FPDF_FONTPATH',			ROOT.DS.'configs'.DS.'fpdf'.DS.'fonts');
define('P_FPDF_SYSTEM_TTF_FONTS',	ROOT.DS.'configs'.DS.'fpdf'.DS.'fonts');
define('P_FPDF_VERSION','1.24');


## On bascule systématiquement localhost vers 127.0.0.1 pour éviter les problèmes de compatibilité sur les dernières versions de Windows
$_SERVER['HTTP_HOST'] = str_replace('localhost', '127.0.0.1', $_SERVER['HTTP_HOST']);

## On récupère le Host original pour conserver le port s'il est mentionné. Il sera ajouté à BASE_URL
## Utilisé surtout pour des serveurs locaux en 127.0.0.1:8888 ou 127.0.0.1:8080
define('P_BASE_HOST', $_SERVER['HTTP_HOST']);

## Redéfinition de HTTP_HOST pour éviter les problèmes de compatibilité sur les serveurs locaux, ou les changements de ports avec EasyPHP ou WAMP par exemple
if (strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
	$_SERVER['HTTP_HOST'] = '127.0.0.1';//On définit le serveur local
}

## Définition de la constante BASE_URL pour les liens internes. Source : http://www.koezion-cms.com/
$baseUrl = '';
$scriptPath = preg_split("#[\\\\/]#", dirname(__FILE__), -1, PREG_SPLIT_NO_EMPTY);
$urlPath = preg_split("#[\\\\/]#", $_SERVER['REQUEST_URI'], -1, PREG_SPLIT_NO_EMPTY);
foreach($urlPath as $k => $v) {
	$key = array_search($v, $scriptPath);
	if($key !== false) {
		$baseUrl .= "/".$v;
	} else {
		break;
	}
}
define('BASE_URL', 'http://'.P_BASE_HOST.$baseUrl);//url absolue du site
unset($baseUrl, $scriptPath, $urlPath, $k, $v, $key);

## Réécriture d'url active ou non, permet de créer des liens cohérents);
//define('P_REWRITE_URLS', true);

## Expression rationnelle vérifiant les adresses mail
define('P_MAIL_REGEX', '#^[a-z0-9!\#$%&\'*+/=?^_`{|}~-]+((\.[a-z0-9!\#$%&\'*+/=?^_`{|}~-]+)?)+@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-z0-9][a-z0-9\-]*[a-z0-9])?$#isUu');

## Contenu de la balise meta generator
define('P_META_GENERATOR', 'Corahn Rin {version} - Automatic character creation by Pierstoval');

## Définition de la langue en fonction de la session
define('P_LANG', isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr');

## Constante de stockage du fichier de temps d'exécutions
define('P_EXECTIME_LOGFILE', ROOT.DS.'logs'.DS.'exectime'.DS.date('Y.m.d').'.log');

## Définit des paramètres de base pour la fonction json_encode() afin de ne pas les resaisir
## PHP 5.4 ou plus récent exigé
define('P_JSON_ENCODE', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);

## Si debug, pas de cache
// if (P_DEBUG === true) {
// 	header('Pragma: no-cache');
// 	header('Cache-Control: no-cache, must-revalidate');
// 	header('Expires: Thu, 03 Jan 2013 06:23:00 GMT');
// }

## Variable de la version issue du fichier XML
$versions_xml = file_get_contents(ROOT.DS.'versions.xml');
$versions = new SimpleXMLElement($versions_xml);
if (!$versions) { tr('Une erreur est survenue dans la récupération du fichier de versions');exit; }
unset($versions_xml);
preg_match('#^([0-9]{4})([0-9]{2})([0-9]{2})$#isU', (string)$versions->version[0]['date'] , $matches);
array_shift($matches);//On supprime $matches[0] pour n'avoir que les sous-ensembles qui matchent l'expression rationnelle
$date = implode('/', array_reverse($matches));//On récupère d/m/y
define('P_VERSION_CODE', (string)$versions->version[0]['code']);
define('P_VERSION_DATE', $date);

unset($versions,$date, $matches);
