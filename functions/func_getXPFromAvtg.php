<?php

/**
 * Récupère l'expérience selon les avantages passés en paramètre
 *
 * @param array $avdesv array contenant deux array : 'avantages' et 'desavantages', contenant chacun une combinaison clé=>valeur correspondant à avdesv_id=>multiplicateur(de 0 à 3)
 * @param int $initexp la valeur initiale d'expérience
 * @return int le nombre de points d'XP final
 * @author Pierstoval 28/12/2012
 */
function getXPFromAvtg($avdesv, $initexp = 100): int {
	global $db;

	if (!is_array($avdesv)) {
		throw new \RuntimeException('Impossible de récupérer l\'expérience utilisée pour les avantages, une erreur inconnue est survenue.');
	}

    $exp = (int) $initexp;
    $avtgs = $avdesv['avantages'] ?? [];
    $desvs = $avdesv['desavantages'] ?? [];
    $totals = array_values(array_merge(array_keys($avtgs),array_keys($desvs))) ?: [0];
    $totalsIndices = implode(', ', array_fill(0, count($totals), '?'));
    $totlist = $db->req('
        SELECT %avdesv_id,
                %avdesv_type,
                %avdesv_xp,
                %avdesv_double
        FROM %%avdesv
        WHERE %avdesv_id IN ('.$totalsIndices.')
    ', $totals);

    $avtglist = $desvlist = [];

    if ($totlist) {
        foreach($totlist as $key => $val) {
            if ($val['avdesv_type'] === 'avtg') {
                $avtglist[$val['avdesv_id']] = $val;
            } elseif ($val['avdesv_type'] === 'desv') {
                $desvlist[$val['avdesv_id']] = $val;
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

    // Désavantages
    $additionalFromDisadvantages = 0;
    foreach($desvs as $key => $val) {
        if (isset($desvlist[$key])) {
            if ($key == 50) {
                $additionalFromDisadvantages += (int) ($desvlist[$key]['avdesv_xp'] * $val);
            } else {
                if ($val == 1) {
                    $additionalFromDisadvantages += $desvlist[$key]['avdesv_xp'];
                } elseif ($val == 2) {
                    $additionalFromDisadvantages += (int) ($desvlist[$key]['avdesv_xp'] * 1.5);
                }
            }
        }
    }

    if ($additionalFromDisadvantages > 80) {
        throw new \RuntimeException("Le total d'expérience pour les désavantages achetés dépasse 80.");
    }

    $exp += $additionalFromDisadvantages;

    if ($exp < 0) {
        throw new \RuntimeException("Trop de points d'expérience ont été utilisés pour acheter des avantages, et l'achat de désavantages n'a pas permis de compenser.");
    }

    return $exp;
}
