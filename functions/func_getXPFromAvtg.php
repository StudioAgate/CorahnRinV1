<?php

/**
 * Récupère l'expérience selon les avantages passés en paramètre
 *
 * @param array $avdesv array contenant deux array : 'avantages' et 'desavantages', contenant chacun une combinaison clé=>valeur correspondant à avdesv_id=>multiplicateur(de 0 à 3)
 * @param int $initexp la valeur initiale d'expérience
 * @return int le nombre de points d'XP final
 * @author Pierstoval 28/12/2012
 */
function getXPFromAvtg($avdesv, $initexp = 100) {
	global $db;
	if (!is_array($avdesv)) {
		$return = false;
	} else {
		$exp = (int) $initexp;
		$avtgs = isset($avdesv['avantages'])	? $avdesv['avantages']		: array();
		$desvs = isset($avdesv['desavantages'])	? $avdesv['desavantages']	: array();
		$reqav = array_merge(array_keys($avtgs),array_keys($desvs));
		$tot = '';
		foreach($reqav as $val) {
			$tot .= $val.',';
		}
		$tot = substr($tot, 0, -1);
		if (!preg_match('#^[0-9]+(,[0-9]+)*$#isU', $tot)) {
			$tot = '0';
		}
		$totlist = $db->req('SELECT %avdesv_id,
									%avdesv_type,
									%avdesv_xp,
									%avdesv_double
							FROM %%avdesv
							WHERE %avdesv_id IN ('.$tot.')', array());

		$avtglist = $desvlist = array();

		if ($totlist) {
			foreach($totlist as $key => $val) {
				if ($val['avdesv_type'] == 'avtg') {
					$avtglist[$val['avdesv_id']] = $val;
				} elseif ($val['avdesv_type'] == 'desv') {
					$desvlist[$val['avdesv_id']] = $val;
				}
			}
		}

		// Désavantages
		foreach($desvs as $key => $val) {
			if (isset($desvlist[$key])) {
				if ($key == 50) {
					$exp += (int) ($desvlist[$key]['avdesv_xp'] * $val);
				} else {
					if ($val == 1) {
						$exp += $desvlist[$key]['avdesv_xp'];
					} elseif ($val == 2) {
						$exp += (int) ($desvlist[$key]['avdesv_xp'] * 1.5);
					}
				}
			}
		}
		// Avantages
		foreach($avtgs as $key => $val) {
			if (isset($avtglist[$key])) {
				if ($val == 1) {
					$exp -= $avtglist[$key]['avdesv_xp'];
				} elseif ($val == 2) {
					$exp -= (int) ($avtglist[$key]['avdesv_xp'] * 1.5);
				}
			}
		}
		$return = $exp;
	}
	return $return;
}
