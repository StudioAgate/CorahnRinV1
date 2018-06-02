<?php

function base_url($withLang = false)
{
    static $base;
    static $baseWithLang;

    if ($withLang && $baseWithLang) {
        return $baseWithLang;
    }

    if (!$withLang && $base) {
        return $base;
    }

    $baseUrl = BASE_URL;

    if (strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) === 0) {
        $baseUrl .= $_SERVER['SCRIPT_NAME'].'/';
    } else {
        $baseUrl .= '/';
    }

    if ($withLang) {
        $baseUrl .= P_LANG;
    }

    $baseUrl = rtrim($baseUrl, '/');

    if ($withLang && !$baseWithLang) {
        $baseWithLang = $baseUrl;
    } elseif (!$withLang && !$base) {
        $base = $baseUrl;
    }

    return $baseUrl;
}
