<?php

use App\FileAndDir;
use App\Users;

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

	if ($errno === E_USER_DEPRECATED) {
	    return;
    }

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

	if (isset($humanType[$errno])) {
		$error_file = ROOT.DS.'logs'.DS.'error_tracking'.DS.date('Y.m.d').'.log';
		$errfile = str_replace(ROOT, '', $errfile);
		$final = "*|*|*Date=>".json_encode(date(DATE_RFC822))
			.'||Ip=>'.json_encode($_SERVER['REMOTE_ADDR'])
			.'||Traçage=>'.json_encode(debug_backtrace())
			.'||Errno=>'.json_encode($errno)
			.'||Errcode=>'.json_encode($phpType[$errno])
			.'||Error=>'.json_encode($humanType[$errno])
			.'||Error_comment=>'.json_encode($errstr)
			.'||Error_file=>'.json_encode($errfile)
			.'||Error_line=>'.json_encode($errline)
			.'||Page.get=>'.json_encode($_PAGE['get'] ?? '')
			.'||Page.request=>'.json_encode($_PAGE['request'] ?? '')
			.'||Page.get_params=>'.json_encode($_SERVER['QUERY_STRING'])
			.'||User.id=>'.json_encode(Users::$id);
		$final = preg_replace('#[\n\r\t]#Uu', '', $final);
		$final = preg_replace('#\s\s+#Uu', ' ', $final);
        if (!is_dir(dirname($error_file))) {
            FileAndDir::createPath(dirname($error_file));
            FileAndDir::put($error_file, '');
        }
		$f = fopen($error_file, 'ab');
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
		if (true === P_DEBUG) {
		    $errstr .= ' in file : <strong>'.$errfile.'</strong> on line <strong><span class="underline">'.$errline.'</span></strong>';
		}

        $msgEcho = $humanType[$errno].' - <span class="underline">'.date(DATE_RFC822).'</span>';
        $trace = '<br />Message : <small>'.$errstr.'</small>';

        $servKeys = [
            'HTTP_COOKIE',
            'HTTP_USER_AGENT',
            'REMOTE_ADDR',
            'REQUEST_URI',
            'REQUEST_METHOD',
            'QUERY_STRING',
        ];

        $serv = [];
        foreach ($servKeys as $key) {
            $serv[$key] = $_SERVER[$key] ?? null;
        }

        $ref = new ReflectionClass(Users::class);
        $user = $ref->getStaticProperties();
        $userData = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ];

        $debugBacktrace = debug_backtrace();

        $msgMail = $msgEcho
            .$trace
            .'<br />Server request:<br /><pre style="font-size: 10px;">'
            .pr($serv, true)
            .'<br />Connected user:<br /><pre style="font-size: 10px;">'
            .pr($userData, true)
            .'</pre><br />Backtrace:<br /><pre style="font-size: 10px;">'
            .pr($debugBacktrace, true)
            .'</pre>';

        try {
            if (true === P_DEBUG) {
                $p = $_PAGE;
                unset($p['list']);
                $msgEcho .= $trace."<br />\nDebug data:".pr(array(
                    '_PAGE' => $p,
                    '_SERVER' => $serv,
                    'User' => $userData,
                    'trace' => $debugBacktrace,
                ), true);
            }
            $msgEcho .= '<br /><br />'.tr('Veuillez envoyer ce message à l\'administrateur du site', true, [], 'general');

            send_mail(P_ERROR_MAIL_TO, 'Error !', $msgMail, 0, P_ERROR_MAIL_FROM);
        } catch (Exception $e) {}
		echo '<pre class="thrown_error '.$errclass.'">'.$msgEcho.'</pre>';
		if ($errno & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) {
			exit;
		}
	}
}

set_error_handler('error_logging');
