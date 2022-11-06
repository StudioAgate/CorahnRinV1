<?php

/**
 * Cette fonction retourne un tableau contenant les réelles valeurs POST non modifiées
 * Grâce à cette fonction, les noms complets des variables passées en POST sont conservés
 * Ainsi les signes "." ne deviennent pas des "_", le signe "." étant non-autorisé pour une variable en temps normal
 *
 * @return array Le tableau avec les données POST
 * @author Pierstoval 23/05/2013
 */
function get_post_data() {
	$pairs = explode('&', file_get_contents('php://input'));//On découpe la requête selon les "&"
	$var_names = array();
	if (!empty($pairs)) {
		foreach ($pairs as $pair) {//Si le tableau n'est pas vide, on fait une boucle
			if (!empty($pair)) {
				$nv = explode('=', $pair);//On explose selon le signe "=" qui correspond à clé=valeur dans la requête
				$name = urldecode((string)@$nv[0]);//On décode chaque valeur
				$value = urldecode((string)@$nv[1]);//On décode chaque valeur
				$var_names[$name] = $value;//On l'ajoute au tableau final
			}
		}
		foreach ($var_names as $k => $v) {
			if (is_numeric($v)) {
				$v = (int) $v;
				$var_names[$k] = $v;
			}
			if (preg_match('#^([a-zA-Z0-9_]+[a-zA-Z0-9_-]+)\[(.+)\]$#isUu', $k, $matches)) {
				unset($var_names[$k]);
				$var_names[$matches[1]][$matches[2]] = $v;
			}
			if (preg_match('#^([a-zA-Z]+[a-zA-Z0-9_-]+)\[\]$#isUu', $k, $matches)) {
				unset($var_names[$k]);
				$var_names[$matches[1]][] = $v;
			}
		}
	}
	return $var_names;//On retourne le tableau
}


/**
 * Crée des variables dynamiques provenant de _POST en fonction des paramètres d'entrée
 * @param array $datas clé = nom de la variable, valeur = type (int, string...)
 */
/*
function create_from_post($datas) {
	if (!is_array($datas)) { return false; }

	foreach ($datas as $var_name => $type) {
		if (is_string($var_name) && preg_match('#^[a-zA-Z_]+[a-zA-Z0-9_\.]+$#isUu', $var_name)) {
			global $$var_name;
			if (isset($_POST[$var_name])) {
				$$var_name = $_POST[$var_name];
			} else {
				$$var_name = 0;
			}
			settype($$var_name, $type);
		}
	}
}
*/