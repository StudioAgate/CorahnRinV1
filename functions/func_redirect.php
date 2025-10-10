<?php

use App\Session;

/**
 * Redirige vers une page demandée
 * @param array|string|int $mkurl Un tableau à placer dans la fonction mkurl()
 * @param string $setflash Une chaîne de caractères à envoyer à Session::setFlash()
 * @param string $flashtype Le type de message flash à afficher. Par défaut "success"
 *
 * @return never
 */
function redirect($mkurl, string $setflash = '', string $flashtype = 'success') {
	$redir = '';

    if (isset($mkurl['http_code'])) {
        httpCode($mkurl['http_code']);
        unset($mkurl['http_code']);
    } else {
        httpCode(302);
    }

	if ($setflash) {
		Session::setFlash($setflash, $flashtype);
	}
    if (is_numeric($mkurl)) {
        $mkurl = ['val' => (int) $mkurl];
    }
	if (is_array($mkurl)) {
		$redir = mkurl($mkurl);
	} elseif (is_string($mkurl)) {
		$redir = $mkurl;
	}

    if (in_array(httpCode(), [302, 307], true)) {
        // Force le navigateur à ne pas mettre en cache une redirection
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');
        header('Cache-Control: post-check=0, pre-check=0', false);
    }

	if ($redir) {
		header('Location: '.$redir);
	} else {
        Session::setFlash('Erreur de redirection', 'error');
        header('Location: '.base_url());
	}

	exit;
}
