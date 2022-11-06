<?php
/**
 * Teste si une url est valide et ne renvoie pas un code HTTP incorrect
 * @param string $URL
 * @return boolean
 */
function url_exists($URL) {
	$URL = (string) $URL;//On force la chaîne de caractère
	$exists = true;
	$file_headers = get_headers($URL);//On récupère les headers
	$InvalidHeaders = ['404', '403', '500'];//On fait une liste des headers incorrects
	foreach($InvalidHeaders as $HeaderVal) {
		if(strstr($file_headers[0], $HeaderVal)) {//Si l'un des headers incorrects est présent dans la chaîne on renvoie false
			$exists = false;
			break;
		}
	}
	return $exists;
}

