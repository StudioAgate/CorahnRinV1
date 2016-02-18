<?php
/**
 * Ce fichier est la passerelle appelée par toutes les urls.
 * Les .htaccess de la racine et de webroot/ se chargent d'envoyer la requête
 * Le fichier racine index.php se charge de charger le site.
 */

## Variable qui contiendra le temps d'exécution du script
$time = microtime(true);

date_default_timezone_set('Europe/Paris');

try {
    require '../app.php';
} catch (Exception $e) {
?>
<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Esteren - Error</title>
</head>

<body>
    <pre>Exception !<br /></pre>
<?php
    do {
        echo "<pre>&gt; ".$e->getMessage().' ('.$e->getCode().')</pre>';
        if (P_DEBUG) {
            echo '<pre>'.$e->getTraceAsString().'</pre>';
            pr($e->getTrace());
        }
        $e = $e->getPrevious();
    } while ($e);
?>
    </pre>
</body>
</html>
<?php
}
