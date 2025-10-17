<?php
/**
 * Récupère l'XP en fonction des domaines passés en paramètre
 *
 * @param array $avdesv contenant une combinaison clé=>valeur correspondant à domain_id=>multiplicateur(de 0 à 5)
 * @param int $initexp la valeur initiale d'expérience
 * @return int le nombre de points d'XP final
 * @author Pierstoval 28/12/2012
 */
function getXPFromDoms(array $xpdom, $initexp = 100): int {
    $initexp = (int) $initexp;

    foreach($xpdom as $val) {
        $currentValue = ($val['curval'] ?? 0);
        if (!is_numeric($currentValue)) {
            throw new \RuntimeException(sprintf('Invalid current value "%s" in domains xp calculation.', $val['curval']));
        }
        if ($currentValue === 0) {
            continue;
        }
        $initexp -= $currentValue * 10;
    }

    if ($initexp < 0) {
        throw new \RuntimeException("Trop de points d'expérience ont été utilisés pour améliorer des domaines par rapport à ce dont vous aviez à disposition.");
    }

    return $initexp;
}