<?php

/**
 * Alias de confort pour la fonction Translate::translate()
 *
 * @see Translate::translate()
 */
function tr($word, $return = false) {
	return Translate::translate($word, $return);
}

class Translate {
	public static $words_fr;
	public static $words_en;
	public static $propositions_en;

	function __construct() {
		self::init();
	}

	/**
	 * Cette fonction initialise la classe et crée les variables disposant du contenu
	 */
	static function init() {
		self::get_words_fr();
		self::get_words_en();
		self::get_propositions_en();
	}

	/**
	 * Cette fonction sert à traduire le texte. Si le mot n'est pas traduit, on l'ajoute à la liste pour qu'il le soit plus tard.
	 * @param string $txt Le texte à traduire
	 * @param boolean $return Si false, on fait un echo du texte. Si true, on le retourne.
	 * @return mixed Le texte traduit si $return == true, sinon true après echo, sinon false
	 */
	static function translate($txt, $return = false) {

		if (!self::$words_fr) { self::init(); }

		$txt = self::clean_word($txt);

		if (!$txt) { return ''; }

		if (!in_array($txt, self::$words_fr)) {
			self::$words_fr[] = $txt;
		}

		if (defined('P_LANG') && P_LANG == 'en' && isset(self::$words_en[$txt])) {
			$txt = self::$words_en[$txt];
		}

		if ($return === false) {
			echo $txt;
			return;
		} else {
			return $txt;
		}
	}


	/**
	 * Cette fonction récupère les mots français du site
	 * @return array Les mots en français
	 */
	static function get_words_fr() {
		$w = file_get_contents(ROOT.DS.'translation'.DS.'fr'.DS.'words.txt');
		$w = explode('*|*|*', $w);
		self::$words_fr = $w;
		asort($w);
		unset($w);
		return self::$words_fr;
	}

	/**
	 * Cette fonction récupère les traductions fr=>en
	 * @return array Clé = mot en français ; Valeur = mot traduit en anglais
	 */
	static function get_words_en() {
		$w = file_get_contents(ROOT.DS.'translation'.DS.'en'.DS.'words.txt');
		$w = explode('*|*|*', $w);
		foreach($w as $k => $v) {
			if ($v) {
				unset($w[$k]);
				$v = explode('=>',$v);
				$w[$v[0]] = $v[1];//PHRASE => PROPOSITION
			}
		}
		ksort($w);
		asort($w);
		self::$words_en = $w;
		asort($w);
		unset($w);
		return self::$words_en;
	}

	/**
	 * Cette fonction récupère les propositions de traductions fr=>en
	 * @return array Clé = mot en français ; Valeur = proposition de traduction
	 */
	static function get_propositions_en() {
		$w = file_get_contents(ROOT.DS.'translation'.DS.'fr'.DS.'propositions_en.txt');
		$w = explode('*|*|*', $w);
		foreach($w as $k => $v) {
			if ($v) {
				unset($w[$k]);
				$v = explode('=>',$v);
				$w[$v[0]] = $v[1];//PHRASE => PROPOSITION
			}
		}
		ksort($w);
		asort($w);
		self::$propositions_en = $w;
		unset($w);
		return self::$propositions_en;
	}
	/**
	 * Cette fonction sert à ajouter ou éditer un mot traduit
	 * @param string $word Le mot ou l'expression à traduire
	 * @param string $trans La traduction
	 * @return string L'état du mot. 'edited' si le mot a été réenregistré, 'saved' s'il a été inséré, ou false sinon
	 */
	static function write_words_en($word, $trans) {
		$word = self::clean_word($word);
		$trans = self::clean_word($trans);
		$words_en = self::$words_en;
		if (!isset($words_en[$word])) {
			$words_en[$word] = $trans;
			$ret = 'saved';
		} else {
			$words_en[$word] = $trans;
			$ret = 'edited';
		}
		asort($words_en);
		ksort($words_en);

		$text_to_write = '';
		foreach($words_en as $w => $t) {
			if ($w && $t) {
				if ($text_to_write) { $text_to_write .= '*|*|*'; }
				$text_to_write .= $w.'=>'.$t;
			}
		}

		file_put_contents(ROOT.DS.'translation'.DS.'en'.DS.'words.txt', $text_to_write);
		return $ret;
	}

	/**
	 * Cette fonction sert à "nettoyer" un mot ou une expression
	 * @param string $word Le mot ou l'expression à traduire
	 * @param string $trans La traduction proposée
	 * @return string L'état du mot. 'saved' s'il a été inséré, ou false sinon
	 */
	static function clean_word($word) {
// 		$word = Encoding::toISO8859($word);
// 		$word = Encoding::toUTF8($word);
// 		$word = preg_replace('#\n|\r#sUu', '', $word);
		$word = preg_replace('#\s\s+#sUu', ' ', $word);
		$word = str_replace('’', "'", $word);
		$word = str_replace('\\\'', "'", $word);
		$word = str_replace('★', '&#9733;', $word);
		$word = trim($word);
		return $word;
	}

	/**
	 * Cette fonction sert à ajouter une proposition de traduction
	 * @param string $word Le mot ou l'expression à traduire
	 * @param string $trans La traduction proposée
	 * @return mixed L'état de l'insertion
	 */
	static function write_propos_en($word, $trans) {
		$propositions_en = self::$propositions_en;
		if ($word && $trans && $word != $trans) {
			$word = self::clean_word($word);
			$trans = self::clean_word($trans);
			if (!isset($propositions_en[$word])) {
				$propositions_en[$word] = $trans;
			} else {
				while (isset($propositions_en[$word])) {
					$word .= ' ';
				}
				$propositions_en[$word] = $trans;
			}
			asort($propositions_en);
			ksort($propositions_en);

			$text_to_write = '';
			$_SESSION['words'][] = self::clean_word($word);
			foreach($propositions_en as $w => $t) {
				if ($text_to_write) { $text_to_write .= '*|*|*'; }
				$text_to_write .= $w.'=>'.$t;
			}
		}

	}

	/**
	 * Cette fonction sert à écrire les mots français dans la liste
	 * @return boolean Résultat de l'opération
	 */
	static function translate_writewords() {
		$words_for_translation = self::$words_fr;
		$words_for_translation = implode("*|*|*", $words_for_translation);

		return file_put_contents(ROOT.DS.'translation'.DS.'fr'.DS.'words.txt', $words_for_translation);
	}
}