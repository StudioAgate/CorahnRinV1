<?php
/**
 * Récupère l'XP en fonction des domaines passés en paramètre
 *
 * @param array $avdesv contenant une combinaison clé=>valeur correspondant à domain_id=>multiplicateur(de 0 à 5)
 * @param int $initexp la valeur initiale d'expérience
 * @return int le nombre de points d'XP final
 * @author Pierstoval 28/12/2012
 */
function getXPFromDoms($xpdom, $initexp = 100) {
	global $db;
	$initexp = (int) $initexp;
	if (!is_array($xpdom)) {
		$return = false;
	} else {
		foreach($xpdom as $key => $val) {
			if (isset($val['curval'])) {
				$initexp -= $val['curval'] * 10;
			}
		}
	}
	return $initexp;
}