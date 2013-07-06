<?php

/**
 * Retourne un tableau formé à partir de la chaîne d'entrée. Cette chaîne DOIT provenir d'un dump à partir de la fonction print_r()
 * @param string $str La chaîne provenant de print_r()
 * @return array Le tableau d'origine
 */
function print_r_to_array($str) {

	//Initialize arrays
	$keys = array();
	$values = array();
	$output = array();

	//Is it an array?
	if(@preg_match('#Array.+#u', (string)$str)) {

		//Let's parse it (hopefully it won't clash)
		$array_contents = substr($str, 7, -2);
		$array_contents = str_replace("\r", '', $array_contents);
		$array_contents = str_replace("\n", '', $array_contents);
		$array_contents = str_replace("\t", '', $array_contents);
		$array_contents = preg_replace('#\s\s+#isUu', ' ', $array_contents);
		$array_contents = trim($array_contents);
		$array_contents = str_replace(array('[', ']', '=>'), array('#!#', '#?#', ''), $array_contents);
		$array_fields = explode("#!#", $array_contents);

		//For each array-field, we need to explode on the delimiters I've set and make it look funny.
		for($i = 0; $i < count($array_fields); $i++ ) {

			//First run is glitched, so let's pass on that one.
			if( $i != 0 ) {

				$bits = explode('#?#', $array_fields[$i]);
				$bits[1] = trim($bits[1]);
				if( $bits[0] != '' ) $output[$bits[0]] = $bits[1];

			}
		}

		//Return the output.
		return $output;

	} else {
		return $str;
	}

}