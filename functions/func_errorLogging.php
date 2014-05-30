<?php

/**
 * Loggue les erreurs dans un fichier de traçage daté
 *
 * @param int $errno Numéro de l'erreur récupérée
 * @param string $errstr Message d'erreur
 * @param string $errfile Fichier dans lequel se trouve l'erreur
 * @param int $errline Ligne de l'erreur dans le fichier
 * @author Pierstoval 01/06/2013
 */
function error_logging($errno, $errstr, $errfile, $errline) {
	global $_PAGE;
	$phpType = array(
		0 => 'UNCAUGHT EXCEPTION',
		E_ERROR => 'E_ERROR',
		E_WARNING => 'E_WARNING',
		E_PARSE => 'E_PARSE',
		E_NOTICE => 'E_NOTICE',
		E_CORE_ERROR => 'E_CORE_ERROR',
		E_CORE_WARNING => 'E_CORE_WARNING',
		E_COMPILE_ERROR => 'E_COMPILE_ERROR',
		E_COMPILE_WARNING => 'E_COMPILE_WARNING',
		E_USER_ERROR => 'E_USER_ERROR',
		E_USER_WARNING => 'E_USER_WARNING',
		E_USER_NOTICE => 'E_USER_NOTICE',
		E_STRICT => 'E_STRICT',
		E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
		E_DEPRECATED => 'E_DEPRECATED',
		E_USER_DEPRECATED => 'E_USER_DEPRECATED',
	);

	$humanType = array (
		0 => 'Erreur inconnue',
		E_ERROR => 'Erreur fatale',
		E_WARNING => 'Alerte',
		E_PARSE => 'Erreur d\'analyse',
		E_NOTICE => 'Erreur',
		E_CORE_ERROR => 'Erreur fatale interne',
		E_CORE_WARNING => 'Alerte interne',
		E_COMPILE_ERROR => 'Erreur de compilation',
		E_COMPILE_WARNING => 'Alerte de compilation',
		E_USER_ERROR => 'Erreur fatale utilisateur',
		E_USER_WARNING => 'Alerte utilisateur',
		E_USER_NOTICE => 'Erreur utilisateur',
		E_STRICT => 'Erreur standards stricts',
		E_RECOVERABLE_ERROR => 'Erreur recouvrable',
		E_DEPRECATED => 'Technique dépréciée',
		E_USER_DEPRECATED => 'Technique dépréciée (utilisateur)',
	);

	$output = '';
	if (isset($phpType[$errno])) {
		$output = $phpType[$errno];
	}
	if (isset($humanType[$errno])) {
		$error_file = ROOT.DS.'logs'.DS.'error_tracking'.DS.date('Y.m.d').'.log';
		$errfile = str_replace(ROOT, '', $errfile);
		$final = "*|*|*Date=>".json_encode(date(DATE_RFC822))
			.'||Ip=>'.json_encode($_SERVER['REMOTE_ADDR'])
// 			.'||Referer=>'.json_encode(@$_SERVER['HTTP_REFERER'])
			.'||Traçage=>'.json_encode(debug_backtrace())
			.'||Errno=>'.json_encode($errno)
			.'||Errcode=>'.json_encode($phpType[$errno])
			.'||Error=>'.json_encode($humanType[$errno])
			.'||Error_comment=>'.json_encode($errstr)
			.'||Error_file=>'.json_encode($errfile)
			.'||Error_line=>'.json_encode($errline)
			.'||Page.get=>'.json_encode(@$_PAGE['get'])
			.'||Page.request=>'.json_encode(@$_PAGE['request'])
			.'||Page.get_params=>'.json_encode(@urldecode($_GET))
			.'||User.id=>'.json_encode(Users::$id);
		$final = preg_replace('#\n|\r|\t#isUu', '', $final);
		$final = preg_replace('#\s\s+#isUu', ' ', $final);
        if (!is_dir(dirname($error_file))) {
            FileAndDir::createPath(dirname($error_file));
            FileAndDir::put($error_file, '');
        }
		$f = fopen($error_file, 'a');
		fwrite($f, $final);
		fclose($f);
		$errclass = '';
		if ($errno & (E_WARNING | 0 | E_CORE_WARNING | E_USER_WARNING | E_COMPILE_WARNING)) {
			$errclass = 'warning';
		} elseif ($errno & (E_ERROR | E_CORE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR)) {
			$errclass = 'error';
		} elseif ($errno & (E_NOTICE | E_USER_NOTICE | E_PARSE | E_USER_DEPRECATED | E_DEPRECATED | E_STRICT)) {
			$errclass = 'notif';
		}
		if (preg_match('#127\.0\.0\.1#', $_SERVER['HTTP_HOST']) || (defined('P_DEBUG') && P_DEBUG === true)) { $errstr .= ' in file : <strong>'.$errfile.'</strong> on line <strong><span class="underline">'.$errline.'</span></strong>'; }
		$msg = $humanType[$errno].' - <span class="underline">'.date(DATE_RFC822).'</span>';
		if (Users::$acl <= 20) { $msg .= '<br />Message : <small>'.$errstr.'</small>'; }
		$msg .= '<br /><br />'.tr('Veuillez envoyer ce message à l\'administrateur du site', true);
		echo '<div class="thrown_error '.$errclass.'">'.$msg.'</div>';
		if ($errno & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) {
			exit;
		}
	}
}

set_error_handler('error_logging');