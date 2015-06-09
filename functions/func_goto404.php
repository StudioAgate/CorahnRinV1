<?php

/**
 * Exécute une redirection vers la page 404 et stocke l'erreur dans le fichier log
 * @author Pierstoval 13/05/2013
 */
function goto_404 () {
	global $_PAGE;
    Translate::$domain = null;

	$error_file = ROOT.DS.'logs'.DS.'404'.DS.date('Y.m.d').'.log';
    if (!is_dir(dirname($error_file))) {
        FileAndDir::createPath(dirname($error_file));
        file_put_contents($error_file, '');
    }
	$final = "*|*|*Date=>".json_encode(date(DATE_RFC822))
		.'||Ip=>'.json_encode($_SERVER['REMOTE_ADDR'])
		.'||Referer=>'.json_encode(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '')
// 		.'||LOCAL_REFERER=>'.@print_r($_PAGE['referer'], true)
		.'||Page.get=>'.json_encode($_PAGE['get'])
		.'||Page.request=>'.json_encode(@$_PAGE['request'])
// 		.'||REQUEST_URI=>'.urldecode($_SERVER['REQUEST_URI'])
		.'||Page.get_params=>'.json_encode($_GET)
		.'||User.id=>'.json_encode(Session::read('user'));
	$final = preg_replace('#\n|\r|\t#isUu', '', $final);
	$final = preg_replace('#\s\s+#isUu', ' ', $final);
	$f = fopen($error_file, 'a');
	fwrite($f, $final);
	fclose($f);

	if ($_PAGE['id'] != 53) {
		header('Location:'.mkurl(array('val'=>53)));
		exit;
	} else {
// 		Session::setFlash('Erreur de redirections 404', 'error');
// 		header('Location:'.mkurl(array('val'=>1)));
// 		exit;
	}
}