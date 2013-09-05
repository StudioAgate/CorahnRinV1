<?php
/**
 * Classe d'exception pour gérer un affichage simple mais un blocage systématique du script
 * @author Pierstoval 01/08/2013
 * @version 1.0
 */
class PException extends ErrorException {

	/**
	 * Le fichier dans lequel sera sauvegardé le log de l'exception.
	 * Par défaut : ROOT.DS.'logs'.DS.'error_tracking'.DS.date('Y.m.d').'.log'
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	protected $error_log_file = '';

	function __construct($message = null, $code = null, $severity = null, $filename = null, $lineno = null, $previous = null) {
		parent::__construct($message, $code, $severity, $filename, $lineno, $previous);

		$this->error_log_file = ROOT.DS.'logs'.DS.'error_tracking'.DS.date('Y.m.d').'.log';
	}

	/**
	 * Affiche le contenu de l'exception et loggue celle-ci dans un fichier log
	 *
	 * @param boolean $return Si passé à true, renvoie le dump de l'erreur
	 * @param string $msg Le message à afficher en plus
	 * @return string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public function show($return = false, $msg = '') {

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

// 		'errno' => entier 2
// 		'errstr' => chaîne (37) 'Missing argument 1 for Pages::index()'
// 		'errfile' => chaîne (99) 'D:\Fichiers mixtes\Sites web\hebergement\JdR\esteren\modules\pages\controllers\Pages.controller.php'
// 		'errline' => entier 4
		$errno = $this->code;
		$errline = $this->line;
		$errstr = $msg .'<br /><em>'. $this->message.'</em>';
		$errfile = str_replace(ROOT, '', $this->file);
		$errfile = str_replace('\\', '/', $errfile);
		$errfile = trim($errfile, '/');

		$output = '';
		if (isset($phpType[$errno])) {
			$output = $phpType[$errno];
		}
		if (isset($humanType[$errno])) {
			$error_file = $this->error_log_file;
			$final = array(
				'date' => date(DATE_RFC822),
				'ip' => $_SERVER['REMOTE_ADDR'],
				'trace' => $this->getTrace(),
				'errno' => $errno,
				'errcode' => $phpType[$errno],
				'error' => $humanType[$errno],
				'error_comment' => $errstr,
				'error_line' => $errline,
			);
// 			$final = @json_encode($final, P_JSON_ENCODE);
			$final = print_r($final, true);
			file_put_contents($error_file, ','."\n".$final, FILE_APPEND);
			$errclass = '';
			if ($errno & (E_WARNING | 0 | E_CORE_WARNING | E_USER_WARNING | E_COMPILE_WARNING)) {
				$errclass = 'warning';
			} elseif ($errno & (E_ERROR | E_CORE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR)) {
				$errclass = 'error';
			} elseif ($errno & (E_NOTICE | E_USER_NOTICE | E_PARSE | E_USER_DEPRECATED | E_DEPRECATED | E_STRICT)) {
				$errclass = 'notif';
			}
			if (strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false || (defined('P_DEBUG') && P_DEBUG === true)) {
				$errstr .= '<br />'.$errfile.'::'.$errline;
			}
			$errstr = str_replace(ROOT, '', $errstr);
			$errstr = str_replace(array('/', '\\'), array('/','/'), $errstr);
			$msg = $humanType[$errno].' - <span class="underline">'.ucfirst(utf8_encode(strftime('%A %d %B %Y, %H:%M:%S'))).'</span>';
			$msg .= '<br />'.$errstr.'';
			$msg .= '<br />'.tr('Veuillez signaler ce message à l\'administrateur du site', true).'';
			$msg = '<div class="thrown_error '.$errclass.'">'.$msg.'</div>';

			if ($return === true) {
				return $msg;
			} else {
				echo $msg;
			}

			if ($errno & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) {
				exit;
			}
		}
	}
}