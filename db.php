<?php

## Constantes concernant la BDD
	## Données locales
	define('P_DB_HOST', '127.0.0.1');
	define('P_DB_USER', 'root');
	define('P_DB_PWD', '');
	define('P_DB_DBNAME', 'esteren');
	define('P_DB_PREFIX', 'est_');


## Détermine si les erreurs seront affichées
define('P_DB_INITERRORS', false);

## Détermine quel type d'erreur sera envoyé
define('P_DB_ERROR_TYPE', 'notice');

## Création de la connexion à la BDD
$db = new bdd(P_DB_HOST, P_DB_USER, P_DB_PWD, P_DB_DBNAME, P_DB_PREFIX);

## Initialisation des paramètres d'erreur
// $db->initErr(P_DB_INITERRORS, P_DB_ERROR_TYPE);