<?php

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

/**
 * Alias de confort pour la fonction p_dump(). Effectue directement un echo, ou retourne le texte si le deuxième paramètre est passé à true
 *
 * @param mixed $val La variable à tester
 * @param boolean $return Retourne le texte si true, ou effectue un echo sinon
 * @author Pierstoval 26/12/2012
 */
function pr($val, $return = false) {
	if ($return === true) {
		return p_dump($val);
	} else {
		echo p_dump($val);
	}
}

/**
 * Utilisé par la fonction p_dump() pour formater le texte et le coloriser selon son type
 *
 * @param mixed $val La variable à tester
 * @return string contenant un formatage selon le typage
 * @author Pierstoval 26/12/2012
 */
function p_dumpTxt($val = null) {
	$final = '';
	if (is_int($val)) {
		$final .= '<small><em>entier</em></small> <span style="color:'.P_DUMP_INTCOLOR.';">' . $val . '</span>';
	} elseif (is_float($val)) {
		$final .= '<small><em>décimal</em></small> <span style="color:'.P_DUMP_FLOATCOLOR.';">' . $val . '</span>';
	} elseif (is_numeric($val)) {
		$final .= '<small><em>chaîne numérique</em> (' . strlen($val) . ')</small> <span style="color:'.P_DUMP_NUMSTRINGCOLOR.';">\'' . $val . '\'</span>';
	} elseif (is_string($val)) {
		$final .= '<small><em>chaîne</em> (' . strlen($val) . ')</small> <span style="color:'.P_DUMP_STRINGCOLOR.';">\'' . htmlspecialchars($val) . '\'</span>';
	} elseif (is_resource($val)) {
		$final .= '<small><em>ressource</em></small> <span style="color:'.P_DUMP_RESSCOLOR.';">' . get_resource_type($val) . '</span>';
	} elseif (is_null($val)) {
		$final .= '<span style="color: '.P_DUMP_NULLCOLOR.';">null</span>';
	} elseif (is_bool($val)) {
		$final .= '<span style="color: '.($val === true ? P_DUMP_BOOLTRUECOLOR : P_DUMP_BOOLFALSECOLOR).';">'.($val === true ? 'true' : 'false').'</span>';
	} elseif (is_object($val)) {
		ob_start();
		var_dump($val);
		$final .= ob_get_clean();
	} elseif (is_array($val)) {
		$final .= '<em>tableau</em> {' . p_dump($val) . '}';
	} else {
		$final .= $val;
	}
	return $final;
}

/**
 * Alias de la fonction var_dump, cette fonction permet un affichage plus sympathique des dump de variables
 *
 * @param mixed $val La variable à tester
 * @return string contenant un dump plus agréable de la variable entrée en paramètre
 * @author Pierstoval 26/12/2012
 */
function p_dump($param) {
	global $_PAGE;
	$lay = isset($_PAGE['layout']) ? $_PAGE['layout'] : 'default';

	if ($lay === 'default') {
		$final = '<div class="p_dump" style="margin: 0 auto;'.(P_DUMP_WIDTH ? 'max-width: '.P_DUMP_WIDTH.';' : '').' min-height: 20px;">';
		$final .= '<p style="cursor: pointer; float: right; padding-left: 200px; margin: 0;"
		onclick="$(this).next(\'div\').slideToggle(400);"><span class="icon-chevron-down"></span></p>';
		$final .= '<div>';
	} else {
		$final = '<div><div>';
	}
	if (!is_array($param)) {
		## Considère tout ce qui n'est pas array, affichage simple
		$final .= '<div style="margin-left: 0;">';
		$final .= p_dumpTxt($param);
		$final .= '</div>';
	} else {
		## Considère les array, et fais une boucle récursive
		$final .= '<div style="padding-left: '.P_DUMP_PADDINGLEFT.';">';
		foreach ($param as $key => $val) {
			$final .= '<div>';
			if (is_int($key)) {
				$final .= '<span style="color:'.P_DUMP_INTCOLOR.';">' . $key . '</span>';
			} else {
				$final .= '<span style="color:'.P_DUMP_STRINGCOLOR.';">\'' . $key . '\'</span>';
			}
			$final .= ' => ';
			$final .= p_dumpTxt($val);
			$final .= '</div>';
		}
		$final .= '</div>';
	}
	$final .= '</div></div>';
	return $final;
}

function show_globals($glob = array(), $return = false) {

	foreach($glob as $k => $v) {
		if (preg_match('#^_#isUu', $k)
		// && $k !== '_PAGE'
 		) { unset($glob[$k]); }
		if (is_object($v)) { $v = 'Object'.get_class($v); $glob[$k] = $v; }
	}

	if ($return === true) { return $glob; } else { pr($glob); return true; }
}