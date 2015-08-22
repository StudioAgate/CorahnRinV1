<?php
/** @var array $before */
/** @var array $after */

//dump($before, $after);

$processed = array();

if ($experience = gv('experience', $before, $after)) {
    $xpBefore = isset($experience['before']['total']) ? $experience['before']['total'] : null;
    $xpAfter = isset($experience['after']['total']) ? $experience['after']['total'] : null;
    if (null !== $xpAfter) {
        if (null !== $xpBefore) {
            $diff = $xpAfter - $xpBefore;
            if ($diff > 0) {
                $diff = '+'.$diff;
            }
            $processed['XP'] = $diff;
        }
    }
    $xpUsedBefore = isset($experience['before']['reste']) ? $experience['before']['reste'] : null;
    $xpUsedAfter = isset($experience['after']['reste']) ? $experience['after']['reste'] : null;
    if (null !== $xpUsedAfter) {
        if (null !== $xpUsedBefore) {
            $diff = $xpUsedAfter - $xpUsedBefore;
            if ($diff < 0) {
                $processed['XP'] = $diff;
            }
        }
    }
}

return $processed;
