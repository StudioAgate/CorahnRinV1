<?php

use App\Session;

/**
 * Redirige vers une page demandée
 * @param array|string $mkurl Un tableau à placer dans la fonction mkurl()
 * @param string $setflash Une chaîne de caractères à envoyer à Session::setFlash()
 * @param string $flashtype Le type de message flash à afficher. Par défaut "success"
 * @param bool $bypass_get_redirect
 */
function redirect($mkurl, $setflash = '', $flashtype = 'success', $bypass_get_redirect = false) {
	$redir = '';
// 	if (isset($_GET['redirect']) && $_GET['redirect'] && $bypass_get_redirect === false && url_exists($_GET['redirect'])) {
// 		$redir = $_GET['redirect'];
// 	} else

    if (isset($mkurl['http_code'])) {
        httpCode($mkurl['http_code']);
        unset($mkurl['http_code']);
    } else {
        httpCode(302);
    }

	$setflash = (string) $setflash;
	$flashtype = (string) $flashtype;
	if ($setflash) {
		Session::setFlash($setflash, $flashtype);
	}
	if (is_array($mkurl)) {
		$mkurl = (array) $mkurl;
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
		header('Location:'.$redir);
	} else {
		redirect(array('val'=>1), 'Erreur de redirection', 'error');
	}
	exit;
}
