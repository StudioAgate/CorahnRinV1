<?php
/**
 * Ce fichier est la passerelle appelée par toutes les urls.
 * Les .htaccess de la racine et de webroot/ se chargent d'envoyer la requête dans $_GET['request']
 * Le fichier racine index.php se charge de charger le site.
 */

## Variables qui contiendront le temps d'exécution du script
$global_time = microtime(true);
$time = microtime(true);

require '../index.php';