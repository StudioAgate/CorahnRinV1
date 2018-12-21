<?php
/** @var array $before */
/** @var array $after */
/** @var array $referenceDomains */
/** @var array $referenceDisciplines */

$processed = [];

if ($experience = gv('experience', $before, $after)) {
    $xpBefore = isset($experience['before']['total']) ? $experience['before']['total'] : 0;
    $xpAfter = isset($experience['after']['total']) ? $experience['after']['total'] : 0;
    if (null !== $xpAfter) {
        if (null !== $xpBefore) {
            $diff = $xpAfter - $xpBefore;
            if ($diff > 0) {
                $diff = '+'.$diff;
            }
            if ($diff) {
                $processed['XP'] = $diff;
            }
        }
    }
    $xpUsedBefore = isset($experience['before']['reste']) ? (int) $experience['before']['reste'] : 0;
    $xpUsedAfter = isset($experience['after']['reste']) ? (int) $experience['after']['reste'] : 0;
    if (null !== $xpUsedAfter) {
        if (null !== $xpUsedBefore) {
            $diff = $xpUsedAfter - $xpUsedBefore;
            if ($diff < 0) {
                $processed['XP'] = $diff;
            }
        }
    }
}

if ($inventaire = gv('inventaire', $before, $after)) {
    if ($armures = gv('armures', $inventaire['before']?:[], $inventaire['after']?:[])) {
        $filter = function($result, $armure) { $result[$armure['id']] = $armure['name']; return $result; };
        $armures['before'] = array_reduce($armures['before']?:[], $filter, []);
        $armures['after'] = array_reduce($armures['after']?:[], $filter, []);
        $processed['armures'] = [];
        foreach (array_diff($armures['before']?:[], $armures['after']?:[]) as $diffBefore) { $processed['armures'][] = '- '.$diffBefore; }
        foreach (array_diff($armures['after']?:[], $armures['before']?:[]) as $diffAfter) { $processed['armures'][] = '+ '.$diffAfter; }
    }
    if ($armes = gv('armes', $inventaire['before']?:[], $inventaire['after']?:[])) {
        $filter = function($result, $arme) { $result[$arme['id']] = $arme['name']; return $result; };
        $armes['before'] = array_reduce($armes['before']?:[], $filter, []);
        $armes['after'] = array_reduce($armes['after']?:[], $filter, []);
        $processed['armes'] = [];
        foreach (array_diff($armes['before'], $armes['after']) as $diffBefore) { $processed['armes'][] = '-'.$diffBefore; }
        foreach (array_diff($armes['after'], $armes['before']) as $diffAfter) { $processed['armes'][] = '+'.$diffAfter; }
    }

    if ($possessions = gv('possessions', $inventaire['before']?:[], $inventaire['after']?:[])) {
        $processed['possessions'] = [];
        foreach (array_diff($possessions['before']?:[], $possessions['after']?:[]) as $diffBefore) { if (!trim($diffBefore)) { continue; } $processed['possessions'][] = '-'.$diffBefore; }
        foreach (array_diff($possessions['after']?:[], $possessions['before']?:[]) as $diffAfter) { if (!trim($diffAfter)) { continue; } $processed['possessions'][] = '+'.$diffAfter; }
        if (!count($processed['possessions'])) {
            unset($processed['possessions']);
        }
    }

    if ($objets_precieux = gv('objets_precieux', $inventaire['before']?:[], $inventaire['after']?:[])) {
        $processed['objets_precieux'] = [];
        foreach (array_diff($objets_precieux['before']?:[], $objets_precieux['after']?:[]) as $diffBefore) { if (!trim($diffBefore)) { continue; } $processed['objets_precieux'][] = '-'.$diffBefore; }
        foreach (array_diff($objets_precieux['after']?:[], $objets_precieux['before']?:[]) as $diffAfter) { if (!trim($diffAfter)) { continue; } $processed['objets_precieux'][] = '+'.$diffAfter; }
        if (!count($processed['objets_precieux'])) { unset($processed['objets_precieux']); }
    }

    if ($artefacts = gv('artefacts', $inventaire['before']?:[], $inventaire['after']?:[])) {
        $processed['artefacts'] = [];
        foreach (array_diff($artefacts['before']?:[], $artefacts['after']?:[]) as $diffBefore) { if (!trim($diffBefore)) { continue; } $processed['artefacts'][] = '- '.$diffBefore; }
        foreach (array_diff($artefacts['after']?:[], $artefacts['before']?:[]) as $diffAfter) { if (!trim($diffAfter)) { continue; } $processed['artefacts'][] = '+ '.$diffAfter; }
        if (!count($processed['artefacts'])) { unset($processed['artefacts']); }
    }
}

if ($ogham = gv('ogham', $before, $after)) {
    $processed['ogham'] = [];
    foreach (array_diff($ogham['before']?:[], $ogham['after']?:[]) as $diffBefore) { if (!trim($diffBefore)) { continue; } $processed['ogham'][] = '- '.$diffBefore; }
    foreach (array_diff($ogham['after']?:[], $ogham['before']?:[]) as $diffAfter) { if (!trim($diffAfter)) { continue; } $processed['ogham'][] = '+ '.$diffAfter; }
    if (!count($processed['ogham'])) { unset($processed['ogham']); }
}

if ($details_personnage = gv('details_personnage', $before, $after)) {
    if (isset($details_personnage['before']['description']) || isset($details_personnage['after']['description'])) {
        $processed['description'] = tr('Description mise à jour...', true);
    }
    if (isset($details_personnage['before']['histoire']) || isset($details_personnage['after']['histoire'])) {
        $processed['histoire'] = tr('Histoire mise à jour...', true);
    }
    if (isset($details_personnage['before']['faits']) || isset($details_personnage['after']['faits'])) {
        $processed['faits'] = tr('Fais marquants mis à jour...', true);
    }
}

if ($miracles = gv('miracles', $before, $after)) {
    if ($maj = gv('maj', $miracles['before']?:[], $miracles['after']?:[])) {
        $processed['miracles_maj'] = [];
        foreach (array_diff($maj['before']?:[], $maj['after']?:[]) as $diffBefore) { if (!trim($diffBefore)) { continue; } $processed['miracles_maj'][] = '- '.$diffBefore; }
        foreach (array_diff($maj['after']?:[], $maj['before']?:[]) as $diffAfter) { if (!trim($diffAfter)) { continue; } $processed['miracles_maj'][] = '+ '.$diffAfter; }
        if (!count($processed['miracles_maj'])) { unset($processed['miracles_maj']); }
    }
    if ($min = gv('min', $miracles['before']?:[], $miracles['after']?:[])) {
        $processed['miracles_min'] = [];
        foreach (array_diff($min['before']?:[], $min['after']?:[]) as $diffBefore) { if (!trim($diffBefore)) { continue; } $processed['miracles_min'][] = '- '.$diffBefore; }
        foreach (array_diff($min['after']?:[], $min['before']?:[]) as $diffAfter) { if (!trim($diffAfter)) { continue; } $processed['miracles_min'][] = '+ '.$diffAfter; }
        if (!count($processed['miracles_min'])) { unset($processed['miracles_min']); }
    }
}

if ($daols = gv('daols', $before, $after)) {
    $finalDomains = [];
    $disciplines = [];
    foreach ($daols as $type => $list) {
        if (!$list) {
            continue;
        }
        foreach ($list as $id => $val) {
            if (isset($val['val'])) {
                $finalDomains[$referenceDomains[$id]['domain_name']][$type] = $val['val'];
            }
            if (isset($val['disciplines'])) {
                foreach ($val['disciplines'] as $discId => $disc) {
                    $disciplines[$referenceDisciplines[$discId]['disc_name']][$type] = $disc['val'];
                }
            }
        }
    }
    foreach ($finalDomains as $id => $domain) {
        $domain = (isset($domain['after'])?$domain['after']:0) - (isset($domain['before'])?$domain['before']:0);
        $domain = ($domain>0?'+':'-').' '.abs($domain);
        $finalDomains[$id] = $domain;
    }
    if (count($finalDomains)) {
        $processed['daols'] = $finalDomains;
    }
    foreach ($disciplines as $id => $disc) {
        $disc = (isset($disc['after'])?$disc['after']:0) - (isset($disc['before'])?$disc['before']:0);
        $disc = ($disc>0?'+':'-').' '.abs($disc);
        $disciplines[$id] = $disc;
    }
    if (count($disciplines)) {
        $processed['disciplines'] = $disciplines;
    }
}

if ($daols = gv('inventaire.argent', $before, $after)) {
    foreach (array('braise', 'azur', 'givre') as $daol) {
        $dBefore = isset($daols['before'][$daol]) ? $daols['before'][$daol] : 0;
        $dAfter = isset($daols['after'][$daol]) ? $daols['after'][$daol] : 0;
        if (null !== $dAfter) {
            if (null !== $dBefore) {
                $diff = $dAfter - $dBefore;
                if ($diff > 0) {
                    $diff = '+'.$diff;
                }
                if ($diff) {
                    $processed[tr('Daols-'.ucfirst($daol), true)] = $diff;
                }
            }
        }
    }
}

$intValues = array(
    'traumatismes.permanents' => 'Trauma-permanent',
    'traumatismes.curables' => 'Trauma-curable',
    'endurcissement' => 'Endurcissement',
);

foreach ($intValues as $intKey => $intProcessKey) {
    $values = gv($intKey, $before, $after);

    $dBefore = isset($values['before']) ? $values['before'] : 0;
    $dAfter = isset($values['after']) ? $values['after']: 0;
    if (null !== $dAfter) {
        if (null !== $dBefore) {
            $diff = $dAfter - $dBefore;
            if ($diff > 0) {
                $diff = '+'.$diff;
            }
            if ($diff) {
                $processed[tr($intProcessKey, true)] = $diff;
            }
        }
    }
}

return $processed;
