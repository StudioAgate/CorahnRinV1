<?php
/**
 * Récupère le nombre de points d'xp en fonction des disciplines passées en paramètre
 *
 * @param array $avdesv contenant une combinaison clé=>valeur correspondant à disc_id=>valeur (à partir de 6)
 * @param int $initexp la valeur initiale d'expérience
 * @return int le nombre de points d'XP final
 * @author Pierstoval 28/12/2012
 */
function getXPFromDiscs(array $discs, $initexp = 100): int {
	global $db;

	$t = $db->req('SELECT %gen_step,%gen_mod,%gen_anchor FROM %%steps ORDER BY %gen_step ASC');//On génère la liste des étapes
	$steps = [];
	foreach ($t as $v) {//On formate la liste des étapes
		$steps[$v['gen_step']] = array(
				'step' => $v['gen_step'],
				'mod' => $v['gen_mod'],
				'title' => $v['gen_anchor'],
		);
	}
	unset($t,$v);

	$avtgs = $_SESSION[$steps[11]['mod']] ?? [];
	//Si l'avantage 2 est présent, alors l'avantage "Mentor" a été sélectionné
	if (isset($avtgs['avantages'][2])) { $mentor = true; } else { $mentor = false; }

	$primsec = $_SESSION[$steps[13]['mod']] ?? [];
	$amelio = $_SESSION[$steps[14]['mod']] ?? [];

	$bonusdom = $_SESSION[$steps[15]['mod']] ?? [];
	$sess_bonus = (int) ($_SESSION['bonusdom'] ?? 0);

	$disciplines = $discs ?: $_SESSION[$steps[16]['mod']];

	$totaldoms = [];
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
	$domains = $db->req('SELECT %domain_id, %domain_name FROM %%domains WHERE %domain_id IN ('.implode(',',$dom_ids).') ORDER BY %domain_name ASC ') ?: [];
	$t = [];
	foreach($domains as $v) { $t[$v['domain_id']] = $v; }
	$domains = $t;
    unset($t);
	$disc = $db->req('SELECT %%disciplines.%disc_name, %%discdoms.%disc_id, %%discdoms.%domain_id
		FROM %%discdoms
		INNER JOIN %%disciplines ON %%disciplines.%disc_id = %%discdoms.%disc_id
		WHERE %%disciplines.%disc_rang = "Professionnel"
		AND %%discdoms.%domain_id IN ('.implode(',',$dom_ids).')') ?: [];
	$mentor_disc_id = [];
	foreach($disc as $v) {
		$domains[$v['domain_id']]['disciplines'][$v['disc_id']] = $v;
		if ($v['domain_id'] == $mentor_domain_id) { $mentor_disc_id[$v['disc_id']] = $v['disc_id']; }//On détermine la liste des disciplines affectées par un potentiel mentor
	}

	$baseExp = getXPFromAvtg($_SESSION[$steps[11]['mod']] ?? [], $initexp);
	$baseExp = getXPFromDoms($_SESSION[$steps[14]['mod']] ?? [], $baseExp);
	$basePoints = $sess_bonus;
	$points = $basePoints;
	$exp = $baseExp;

	foreach($disciplines as $disc_id => $v) {
		if ($v['bonus']) {
            if ($points <= 0) {
                throw new \RuntimeException(sprintf(
                    "La discipline \"%s\" a été ajoutée par le biais d'un point bonus, mais il n'y en avait pas suffisamment.",
                    array_values(array_filter($disc, static function ($d) use ($disc_id) { return $d['disc_id'] == $disc_id; }))[0]['disc_name'] ?? $disc_id,
                ));
            }
			--$points;
		} elseif ($v['exp']) {
            if ($exp <= 0) {
                throw new \RuntimeException(sprintf(
                    "La discipline \"%s\" a été ajoutée par le biais des points d'expérience, mais il n'y en avait pas suffisamment.",
                    array_values(array_filter($disc, static function ($d) use ($disc_id) { return $d['disc_id'] == $disc_id; }))[0]['disc_name'] ?? $disc_id,
                ));
            }
			$cost = 25;
			if ($mentor === true && $disc_id && isset($mentor_disc_id[$disc_id])) { $cost = 20; }
			$exp -= $cost;
		} else {
            throw new \RuntimeException(E_USER_NOTICE, sprintf(
                "Erreur dans la gestion des calculs d'xp pour les disciplines. Attendu clés \"bonus\" et \"exp\" pour la discipline avec identifiant \"%s\", et reçu cette valeur: %s",
                $disc_id, print_r($v, true)
            ));
        }
	}

    if ($exp < 0) {
        throw new \RuntimeException("Trop de points d'expérience ont été utilisés pour acheter des disciplines.");
    }

	return $exp;
}
