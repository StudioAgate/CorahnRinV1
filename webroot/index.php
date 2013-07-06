<?php
/**
 * Ce fichier est la passerelle appelée par toutes les urls.
 * Les .htaccess de la racine et de webroot/ se chargent d'envoyer la requête dans $_GET['request']
 * Le fichier racine index.php se charge de charger le site.
 */

## Variable qui contiendra le temps d'exécution du script
$time = microtime(true);

date_default_timezone_set('Europe/Paris');

require '../index.php';