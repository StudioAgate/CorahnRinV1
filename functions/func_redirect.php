<?php

/**
 * Redirige vers une page demandée
 * @param array $mkurl Un tableau à placer dans la fonction mkurl()
 * @param string $setflash Une chaîne de caractères à envoyer à Session::setFlash()
 * @param string $flashtype Le type de message flash à afficher. Par défaut "success"
 * @param bool $bypass_get_redirect
 */
function redirect($mkurl, $setflash = '', $flashtype = 'success', $bypass_get_redirect = false) {
	$redir = '';
// 	if (isset($_GET['redirect']) && $_GET['redirect'] && $bypass_get_redirect === false && url_exists($_GET['redirect'])) {
// 		$redir = $_GET['redirect'];
// 	} else
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

	if ($redir) {
		header('Location:'.$redir);
	} else {
		redirect(array('val'=>1), 'Erreur de redirection', 'error');
	}
	exit;
}