<?php

use App\Hash;

/**
 * "gv" for "get value"
 * Workaround to get values from "before" and "after" arrays.
 *
 * @param string $key
 * @param array  $before
 * @param array  $after
 *
 * @return array|null
 */
function gv($key, array $before = array(), array $after = array()) {
    $beforeValue = Hash::get($before, $key);
    $afterValue = Hash::get($after, $key);

    if ($beforeValue || $afterValue) {
        return ['before' => $beforeValue, 'after' => $afterValue];
    }

    return null;
}
