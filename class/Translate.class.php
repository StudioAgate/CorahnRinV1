<?php

class Translate {
	public static $file = '';
	public static $file_log = '';
	public static $words = array();
	public static $insert_count = 0;

	function __construct() {}

	/**
	 * Cette fonction initialise la classe et crée les variables disposant du contenu
	 */
	static function init() {
		self::$file = ROOT.DS.'translation'.DS.'words_'.P_LANG.'.php';
		self::$file_log = ROOT.DS.'translation'.DS.'words_'.P_LANG.'.log';
		if (FileAndDir::fexists(self::$file)) {
			$cnt = FileAndDir::get(self::$file);
			$cnt = json_decode($cnt, true);
			if ($cnt) {
				self::$words = $cnt;
			}
		} else {
			self::$words = array();
		}
	}

	/**
	 * Sauvegarde des informations dans le fichier log de traduction
	 *
	 * @param unknown $params
	 */
	static function log($type, $arg1, $arg2 = null, $arg3 = null) {
		global $global_time;
		$data = array(
			'type'=>$type,
			'date'=>date(DATE_RFC822),
			'ip'=>$_SERVER['REMOTE_ADDR'],
			'user_id'=>Users::$id,
			'exectime'=>$global_time,
		);
		$save = true;
		if ($type === 'new_word') {
			$data['contents'] = array('word'=>$arg1);
		} elseif ($type === 'new_translation') {
			$data['contents'] = array('word'=>$arg1,'translation'=>$arg2);
		} elseif ($type === 'update_translation') {
			$data['contents'] = array('word'=>$arg1,'translation'=>$arg2,'old_translation'=>$arg3);
		} else {
			$save = false;
		}

		if ($save === true) {
			$data = json_encode($data, P_JSON_ENCODE);
			FileAndDir::put(self::$file_log, $data, FILE_APPEND);
		}
	}

	/**
	 * Cette fonction sert à traduire le texte. Si le mot n'est pas traduit, on l'ajoute à la liste pour qu'il le soit plus tard.
	 *
	 * @param string $txt Le texte à traduire
	 * @param boolean $return Si false, on fait un echo du texte. Si true, on le retourne.
	 * @param array $params Une liste de paramètres à ne pas traduire, pour éviter d'avoir à traduire plusieurs fois le même texte
	 * @return mixed Le texte traduit si $return == true, sinon true après echo, sinon false
	 */
	static function translate($txt, $return = false, $params = array()) {
		if (!self::$words) { self::init(); }
		$txt = self::clean_word($txt);

		if (!$txt) { return ''; }

		if (count($params)) {
			ksort($params);
			$params_string = $params_numeric = array();
			//Reformatage des deux tableaux (pour palier aux erreurs, forcer les accolades sur la clé, et réinitialiser les valeurs numériques)
			foreach ($params as $k => $v) {
				if (is_string($k)) {
					$k = preg_replace('~\{|\}~isUu', '', $k);
					$k = '{'.$k.'}';
					$params_string[$k] = $v;
				} elseif (is_numeric($k)) {
					$params_numeric[] = $v;
				}
			}

			//Changement des chaînes
			foreach ($params_string as $k => $v) {
				$txt = str_replace($k, $v, $txt);
			}
			foreach ($params_numeric as $v) {
				$txt = preg_replace('~\{\}~U', $v, $txt, 1);
			}
		}

		if (P_LANG === 'fr') {//Par défaut si le site est en français, on n'a rien à traduire
			if ($return === false) { echo $txt; return; } else { return $txt; }
		}

		if (!array_key_exists($txt, self::$words)) {
			self::$words[$txt] = '';
			self::log('new_word', $txt);
			self::$insert_count ++;
		} elseif (self::clean_word(self::$words[$txt])) {
			$txt = clean_word(self::$words[$txt]);
		}

		if ($return === false) {
			echo $txt;
			return;
		} else {
			return $txt;
		}
	}

	/**
	 * Cette fonction sert à ajouter ou éditer un mot traduit
	 *
	 * @param string $word Le mot ou l'expression à traduire
	 * @param string $trans La traduction
	 * @return int|boolean L'état du mot. 1 s'il a été inséré, 2 si le mot a été réenregistré, false sinon
	 */
	static function write_translation($word, $trans) {

		$word = self::clean_word($word);
		$trans = self::clean_word($trans);

		$words = self::$words;

		$ret = false;

		if ($word && $trans) {
			if (!array_key_exists($word, $words)) {
				$ret = 1;
				self::log('new_translation', $word, $trans);
			} else {
				$ret = 2;
				self::log('update_translation', $word, $trans);
			}
			self::$words[$word] = $trans;
			self::$insert_count ++;
		}

		return $ret;
	}

	/**
	 * Cette fonction sert à "nettoyer" un mot ou une expression
	 *
	 * @param string $word Le mot ou l'expression à traduire
	 * @return string Le mot "nettoyé"
	 */
	static function clean_word($word) {
		$word = preg_replace('#\s\s+#sUu', ' ', $word);
		$word = str_replace('’', "'", $word);
		$word = str_replace('\\\'', "'", $word);
		$word = str_replace('★', '&#9733;', $word);
		$word = trim($word);
		return $word;
	}

	/**
	 * Cette fonction sert à écrire les traductions dans le fichier dédié
	 *
	 * @return boolean Résultat de l'opération
	 */
	static function translate_writewords() {
		if (P_LANG === 'fr') { return true; }
		$words_for_translation = self::$words;
		ksort($words_for_translation);
		$words_for_translation = json_encode($words_for_translation, P_JSON_ENCODE);

		if (self::$insert_count > 0) {
			return FileAndDir::put(self::$file, $words_for_translation);
		}
	}
}