<?php

/**
 * Alias de confort pour la fonction p_dump(). Effectue directement un echo, ou retourne le texte si le deuxième paramètre est passé à true
 *
 * @param mixed   $val La variable à tester
 * @param boolean $return Retourne le texte si true, ou effectue un echo sinon
 *
 * @author Pierstoval 26/12/2012
 */
function pr($val, $return = false) {
	if ($return === true) {
	    ob_start();
	    dump($val);
		return ob_get_clean();
	}

    echo p_dump($val);
}

/**
 * Alias de la fonction var_dump, cette fonction permet un affichage plus sympathique des dump de variables
 *
 * @param mixed $val La variable à tester
 * @return string contenant un dump plus agréable de la variable entrée en paramètre
 * @author Pierstoval 26/12/2012
 */
function p_dump(...$args) {
    dump(...$args);
}

function show_globals($glob = array(), $return = false) {

	foreach($glob as $k => $v) {
		if (0 === strpos($k, '_')
		// && $k !== '_PAGE'
 		) { unset($glob[$k]); }
		if (is_object($v)) { $v = 'Object'.get_class($v); $glob[$k] = $v; }
	}

	if ($return === true) { return $glob; }

    pr($glob);

    return true;
}
