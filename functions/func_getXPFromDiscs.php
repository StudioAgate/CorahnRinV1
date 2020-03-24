<?php
/**
 * Récupère le nombre de points d'xp en fonction des disciplines passées en paramètre
 *
 * @param array $avdesv contenant une combinaison clé=>valeur correspondant à disc_id=>valeur (à partir de 6)
 * @param int $initexp la valeur initiale d'expérience
 * @return int le nombre de points d'XP final
 * @author Pierstoval 28/12/2012
 */
function getXPFromDiscs($discs, $initexp = 100) {
	global $db;

	$t = $db->req('SELECT %gen_step,%gen_mod,%gen_anchor FROM %%steps ORDER BY %gen_step ASC');//On génère la liste des étapes
	$steps = array();
	foreach ($t as $v) {//On formate la liste des étapes
		$steps[$v['gen_step']] = array(
				'step' => $v['gen_step'],
				'mod' => $v['gen_mod'],
				'title' => $v['gen_anchor'],
		);
	}
	unset($t,$v);

	$avtgs = isset($_SESSION[$steps[11]['mod']]) ? $_SESSION[$steps[11]['mod']] : array();
	//Si l'avantage 2 est présent, alors l'avantage "Mentor" a été sélectionné
	if (isset($avtgs['avantages'][2])) { $mentor = true; } else { $mentor = false; }

	$primsec = isset($_SESSION[$steps[13]['mod']]) ? $_SESSION[$steps[13]['mod']] : array();
	$amelio = isset($_SESSION[$steps[14]['mod']]) ? $_SESSION[$steps[14]['mod']] : array();

	$bonusdom = isset($_SESSION[$steps[15]['mod']]) ? $_SESSION[$steps[15]['mod']] : array();
	$sess_bonus = isset($_SESSION['bonusdom']) ? (int) $_SESSION['bonusdom'] : array();

	$disciplines = isset($_SESSION[$steps[16]['mod']]) ? $_SESSION[$steps[16]['mod']] : array();

	$totaldoms = array();
	$mentor_domain_id = 0;
	foreach($amelio as $id => $v) {
		if (!isset($totaldoms[$id])) {
			$totaldoms[$id] = $v['primsec'];
		}
		$totaldoms[$id] += $v['curval'];
		if ($v['primsec'] == 5) { $mentor_domain_id = $id; }//Récupération de l'id du domaine associé au mentor
	}
	foreach($primsec as $id => $v) {
		if ($v != 5 && $v != 3) {
			unset($totaldoms[$id]);
		}
	}
	foreach($bonusdom as $id => $v) {
		if (!isset($totaldoms[$id])) {
			$totaldoms[$id] = $v;
		} else {
			$totaldoms[$id] += $v;
		}
	}
	foreach($totaldoms as $k => $v) {
		if ($v < 5) { unset($totaldoms[$k]); }
	}

	$dom_ids = array_keys($totaldoms) ?: array(0);
	$domains = $db->req('SELECT %domain_id, %domain_name FROM %%domains WHERE %domain_id IN ('.implode(',',$dom_ids).') ORDER BY %domain_name ASC ') ?: array();
	$t = array();
	foreach($domains as $k => $v) { $t[$v['domain_id']] = $v; }
	$domains = $t; unset($t);
	$disc = $db->req('SELECT %%disciplines.%disc_name, %%discdoms.%disc_id, %%discdoms.%domain_id
		FROM %%discdoms
		INNER JOIN %%disciplines ON %%disciplines.%disc_id = %%discdoms.%disc_id
		WHERE %%disciplines.%disc_rang = "Professionnel"
		AND %%discdoms.%domain_id IN ('.implode(',',$dom_ids).')') ?: array();
	$mentor_disc_id = array();
	foreach($disc as $k => $v) {
		$domains[$v['domain_id']]['disciplines'][$v['disc_id']] = $v;
		if ($v['domain_id'] == $mentor_domain_id) { $mentor_disc_id[$v['disc_id']] = $v['disc_id']; }//On détermine la liste des disciplines affectées par un potentiel mentor
	}

	$baseExp = getXPFromAvtg(isset($_SESSION[$steps[11]['mod']]) ? $_SESSION[$steps[11]['mod']] : array(), 100);
	$baseExp = getXPFromDoms(isset($_SESSION[$steps[14]['mod']]) ? $_SESSION[$steps[14]['mod']] : array(), $baseExp);
	$basePoints = $sess_bonus;
	$points = $basePoints;
	$exp = $baseExp;

	foreach($disciplines as $disc_id => $v) {
		if ($v['bonus']) {
			$points -= 1;
		} elseif ($v['exp']) {
			$cost = 25;
			if ($mentor === true && isset($disc_id) && isset($mentor_disc_id[$disc_id])) { $cost = 20; }
			$exp -= $cost;
		}
	}

	return $exp;
}
