<?php
/**
 * Effectue une comparaison de deux tableaux de façon récursive
 * @param array $array1 Le premier tableau à scanner
 * @param array $array2 Le deuxième tableau à scanner
 * @param boolean $strict Si true, effectuera une comparaison strict. Sinon, effectuera une comparaison large
 */
function p_array_diff_recursive($array1, $array2, $strict = true) {
	$ret = [];

	foreach ($array1 as $k => $v) {
		if (array_key_exists($k, $array2)) {
			if (is_array($v)) {
				$recursive_result = p_array_diff_recursive($v, $array2[$k], $strict);
				if (count($recursive_result)) {
					$ret[$k] = $recursive_result;
				}
			} else {
				if ($strict === true && $v !== $array2[$k]) {
					$ret[$k] = $v;
				} elseif ($strict !== true && $v != $array2[$k]) {
				   	$ret[$k] = $v;
				}
			}
		} else {
			$ret[$k] = $v;
		}
	}
	return $ret;
}