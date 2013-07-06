<?php
/**
 * Fichier de configuration
 * Définition de toutes les constantes et paramètres utilisées sur le site
 * Chaque constante commence par le préfixe "P_" pour être reconnaissable, exceptées celles concernant les chemins de fichiers et urls
 * Créées par Alexandre Ancelet - Pierstoval
 * 2012-2013
 */
setlocale(LC_TIME, array('fr_FR', 'fr_FR.UTF-8'));

## Réécriture d'url active ou non, permet de créer des liens cohérents);
//define('P_REWRITE_URLS', true);

## Regex vérifiant les adresses mail
define('P_MAIL_REGEX', '#^[a-z0-9!\#$%&\'*+/=?^_`{|}~-]+((\.[a-z0-9!\#$%&\'*+/=?^_`{|}~-]+)?)+@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-z0-9][a-z0-9\-]*[a-z0-9])?$#isU');

## Champs disponibles pour la fonction mkurl. Dépend de la base de données
define('P_MKURL_FIELDS', 'page_id,page_getmod,page_anchor');

## Contenu de la balise meta generator
define('P_META_GENERATOR', 'Corahn Rin {version} - Automatic character creation by Pierstoval');

## Définition de la langue en fonction de la session
define('P_LANG', Session::check('lang') ? Session::read('lang') : 'fr');

## Constantes liées à FPDF pour les fichiers externes
define('P_FPDF_FONTPATH', ROOT.DS.'files'.DS.'fpdf'.DS.'fonts');
define('P_FPDF_SYSTEM_TTF_FONTS', ROOT.DS.'files'.DS.'fpdf'.DS.'fonts');

##Constantes liées à PHP Mailer
define('P_MAIL_SMTP_HOST', 'auth.smtp.1and1.fr');
define('P_MAIL_SMTP_USER', 'no-reply@pierstoval.com');
define('P_MAIL_SMTP_PASSWORD', '');
define('P_MAIL_SMTP_SECURE', 'TLS');
define('P_MAIL_SMTP_PORT', 587);
define('P_MAIL_DEFAULT_FROM_MAIL', P_MAIL_SMTP_USER);
define('P_MAIL_DEFAULT_FROM_NAME', 'Corahn Rin');

## Couleur des différents types de variables pour les fonctions p_dump et p_dumpTxt
define('P_DUMP_INTCOLOR', 'blue');
define('P_DUMP_FLOATCOLOR', 'darkblue');
define('P_DUMP_NUMSTRINGCOLOR', '#c0c');
define('P_DUMP_STRINGCOLOR', 'darkgreen');
define('P_DUMP_RESSCOLOR', '#aa0');
define('P_DUMP_NULLCOLOR', '#aaa');
define('P_DUMP_BOOLTRUECOLOR', '#0c0');
define('P_DUMP_BOOLFALSECOLOR', 'red');
define('P_DUMP_OBJECTCOLOR', 'pink');
define('P_DUMP_PADDINGLEFT', '25px');
define('P_DUMP_WIDTH', '');

## Générer les fichiers css et js des pages à chaque chargement via la fonction buffWrite(). Permet de réinitialiser une partie cache en local ou lorsque le superadmin est connecté
define('P_GEN_FILES_ONLOAD', ($_SERVER['HTTP_HOST'] === '127.0.0.1' ? true : false));


## Extensions de fichiers qu'il est possible de créer à chaque chargement via la fonction buffWrite(). Par défaut CSS et JS
// define('P_GEN_FILES_TYPES', 'css,js');

## Template de base des modules
define('P_TPL_BASEMOD', <<<'TPLBASEMOD'
<?php

?>

<div class="container">


</div><!-- /container -->

<?php
buffWrite('css', <<<CSSFILE

CSSFILE
);
buffWrite('js', <<<JSFILE

JSFILE
);
TPLBASEMOD
);

## Création de la variable $_SESSION['etape'] qui correspond à l'avancement du personnage
if (!Session::read('etape')) { Session::write('etape', 1); }

## Initialisation de la classe de traduction
Translate::init();

## Si debug, pas de cache
// if (P_DEBUG === true) {
// 	header('Pragma: no-cache');
// 	header('Cache-Control: no-cache, must-revalidate');
// 	header('Expires: Thu, 03 Jan 2013 06:23:00 GMT');
// }

## Variable de la version issue du fichier XML
$versions_xml = FileAndDir::get(ROOT.DS.'versions.xml');
if (!$versions_xml) {
	echo 'Une erreur est survenue dans la récupération du fichier de versions';
	exit;
}
$versions = new SimpleXMLElement($versions_xml);
unset($versions_xml);
$day	= preg_replace('#^([0-9]{4})([0-9]{2})([0-9]{2})$#isU', '$3', (string)$versions->version[0]['date']);
$month	= preg_replace('#^([0-9]{4})([0-9]{2})([0-9]{2})$#isU', '$2', (string)$versions->version[0]['date']);
$year	= preg_replace('#^([0-9]{4})([0-9]{2})([0-9]{2})$#isU', '$1', (string)$versions->version[0]['date']);

$date = $day.'/'.$month.'/'.$year;
$_PAGE['version'] = array(
	'code' => (string)$versions->version[0]['code'],
	'date' => $date
);
unset($day,$month,$year,$versions,$date);
