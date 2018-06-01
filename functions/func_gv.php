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
    $result = null;
    $beforeValue = Hash::get($before, $key);
    $afterValue = Hash::get($after, $key);
    return $beforeValue || $afterValue ? array('before' => $beforeValue, 'after' => $afterValue) : null;
}
