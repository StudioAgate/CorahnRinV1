<?php

namespace App;

class Translate {
	public static $words_fr = array();
	public static $words_en = array();
//	public static $propositions_en = array();

    public static $at_least_one_modification = false;

    public static $write_en = false;

    public static $_PAGE;
    public static $domain;
    public static $char_id;

    public function __construct(){}

	/**
	 * Cette fonction initialise la classe et crée les variables disposant du contenu
	 */
	public static function init() {
        self::createTree();
		self::get_words_fr();
		self::get_words_en();
//		self::get_propositions_en();
	}

    public static function createTree() {
        if (!is_dir(ROOT.DS.'translation'.DS.'fr'.DS)) {
            mkdir(ROOT.DS.'translation'.DS.'fr'.DS, 0775, true);
        }
        if (!is_dir(ROOT.DS.'translation'.DS.'en'.DS)) {
            mkdir(ROOT.DS.'translation'.DS.'en'.DS, 0775, true);
        }
        if (!is_dir(ROOT.DS.'translation'.DS.'fr'.DS.'characters'.DS)) {
            mkdir(ROOT.DS.'translation'.DS.'fr'.DS.'characters'.DS, 0775, true);
        }
        if (!is_dir(ROOT.DS.'translation'.DS.'en'.DS.'characters'.DS)) {
            mkdir(ROOT.DS.'translation'.DS.'en'.DS.'characters'.DS, 0775, true);
        }
    }

    /**
     * Cette fonction sert à traduire le texte. Si le mot n'est pas traduit, on l'ajoute à la liste pour qu'il le soit plus tard.
     * @param string  $txt    Le texte à traduire
     * @param boolean|null $return Si false, on fait un echo du texte. Si true, on le retourne.
     * @param array   $params Les paramètres de texte à ajouter
     * @param null    $domain
     * @return mixed Le texte traduit si $return == true, sinon true après echo, sinon false
     */
	public static function translate($txt, $return = false, $params = array(), $domain = null) {

        if  ($return === null) {
            $return = false;
        }

		if (!self::$words_fr) { self::init(); }

		$txt = self::clean_word($txt);

		if (!$txt) { return ''; }

        if (self::$domain) {
            $domain = $domain ?: self::$domain;
        } elseif (self::$char_id) {
            $domain = $domain ?: ('characters.'.self::$char_id);
        } elseif (self::$_PAGE['get']) {
            $domain = $domain ?: self::$_PAGE['get'];
        } else {
            $domain = $domain ?: 'general';
        }

        if (!isset(self::$words_fr[$domain])) {
            self::$words_fr[$domain] = array();
        }

		if (!self::check($txt, self::$words_fr[$domain], false)) {
			self::$words_fr[$domain][] = array('source'=>$txt,'trans'=>$txt);
            self::$at_least_one_modification = true;
		}

        $lang = defined('P_LANG') ? P_LANG : 'fr';

        if ($lang === 'en' && isset(self::$words_en[$domain])) {
            $txt = self::search($txt, self::$words_en[$domain], false);
        } elseif ($lang === 'fr' && isset(self::$words_fr[$domain])) {
            $txt = self::search($txt, self::$words_fr[$domain], false);
        }

        // Change les éventuels paramètres de remplacement à la chaîne de caractères
        if ($params) {
            $txt = str_replace(array_keys($params), $params, $txt);
        }

		if ($return === false) {
			echo $txt;
			return null;
		}

        return $txt;
    }

    /**
     * @param string $txt La chaîne à chercher
     * @param array $source Le tableau source
     * @param boolean $clean Utilise la fonction self::clean_word() si vrai
     * @return string
     */
    public static function check($txt, $source, $clean = true) {
        if ($clean) {
            $txt = self::clean_word($txt);
        }
        $found = false;
        $result = array_filter($source, function($element) use ($txt) {
            return $element['source'] === $txt;
        });
        if (count($result)){
            $found = true;
        }
        return $found;
    }

    /**
     * @param string $txt La chaîne à chercher
     * @param array $source Le tableau source
     * @param boolean $clean Utilise la fonction self::clean_word() si vrai
     * @return string
     */
    public static function search($txt, $source, $clean = true) {
        if ($clean) {
            $txt = self::clean_word($txt);
        }
        $result = array_values(array_filter($source, function($element) use ($txt) {
            return self::clean_word($element['source']) === $txt;
        }));
        if (count($result)){
            sort($result);
            $txt = $result[0]['trans'];
        }
        return $txt;
    }


	/**
	 * Cette fonction récupère les mots français du site
	 * @return array Les mots en français
	 */
	public static function get_words_fr() {
        $dir = ROOT.DS.'translation'.DS.'fr'.DS;

        $files = glob($dir.'*.php');
        foreach ($files as $file) {
            if (is_file($file)) {
                $domain = basename($file, '.php');
                self::$words_fr[$domain] = require $file;
            }
        }

        $files = glob($dir.'*.json');
        foreach ($files as $file) {
            if (is_file($file)) {
                $domain = basename($file, '.json');
                if (isset(self::$words_fr[$domain])) {
                    continue;
                }
                self::$words_fr[$domain] = json_decode(file_get_contents($file), true);
                $export = var_export(self::$words_fr[$domain], true);
                file_put_contents(str_replace('.json', '.php', $file), "<?php return $export;");
            }
        }

        // Character files
        $files = glob($dir.'characters'.DS.'*.json');
        foreach ($files as $file) {
            if (is_file($file)) {
                $domain = 'characters.'.basename($file, '.json');
                self::$words_fr[$domain] = json_decode(file_get_contents($file), true);
            }
        }

        return self::$words_fr;
	}

	/**
	 * Cette fonction récupère les traductions fr=>en
	 * @return array
	 */
	public static function get_words_en() {
        $dir = ROOT.DS.'translation'.DS.'en'.DS;

        $files = glob($dir.'*.php');
        foreach ($files as $file) {
            if (is_file($file)) {
                $domain = basename($file, '.php');
                self::$words_en[$domain] = require $file;
            }
        }

        $files = glob($dir.'*.json');
        foreach ($files as $file) {
            if (is_file($file)) {
                $domain = basename($file, '.json');
                if (isset(self::$words_en[$domain])) {
                    continue;
                }
                self::$words_en[$domain] = json_decode(file_get_contents($file), true);
                $export = var_export(self::$words_en[$domain], true);
                file_put_contents(str_replace('.json', '.php', $file), "<?php return $export;");
            }
        }

        // Character files
        $files = glob($dir.'characters'.DS.'*.json');
        foreach ($files as $file) {
            if (is_file($file)) {
                $domain = 'characters.'.basename($file, '.json');
                self::$words_en[$domain] = json_decode(file_get_contents($file), true);
            }
        }

        return self::$words_en;
	}

	/**
	 * Cette fonction récupère les propositions de traductions fr=>en
	 * @return array Clé = mot en français ; Valeur = proposition de traduction
	 */
//	static function get_propositions_en() {
//        $file = ROOT.DS.'translation'.DS.'en'.DS.'propositions_en.txt';
//        if (FileAndDir::fexists($file)) {
//            $w = FileAndDir::get($file);
//            $w = json_decode($w, true) ?: array();
//            self::$propositions_en = $w;
//            unset($w);
//        }
//        return self::$propositions_en;
//	}

    /**
     * Cette fonction sert à ajouter ou éditer un mot traduit
     * @param string $word_source Le mot ou l'expression à traduire
     * @param string $trans       La traduction
     * @param        $domain
     * @return boolean
     */
	public static function write_words_en($word_source, $trans, $domain) {
        $word_source = self::clean_word($word_source);
		$trans = self::clean_word($trans);

        $changed = false;

        if (!self::$words_en[$domain]) {
            self::$words_en[$domain] = array();
        }

        foreach  (self::$words_en[$domain] as $k => $word) {
            if ($word['source'] === $word_source) {
                self::$words_en[$domain][$k]['trans'] = $trans;
                $changed = true;
            }
        }

        if ($changed === false) {
            self::$words_en[$domain][] = array('source' => $word_source, 'trans' => $trans);
            $changed = true;
        }

        if ($changed) {
            self::$write_en = true;
        }

//        $text_to_write = json_encode(self::$words_en, 480);
//
//		file_put_contents(ROOT.DS.'translation'.DS.'en'.DS.'words.txt', $text_to_write);
        return $changed;
	}

    /**
     * Cette fonction sert à "nettoyer" un mot ou une expression
     * @param string $word Le mot ou l'expression à traduire
     * @return string L'état du mot. 'saved' s'il a été inséré, ou false sinon
     */
	public static function clean_word($word) {
        $word = str_replace(["\n", "\r", "\t", '’', '\\\'', '★'], [' ', ' ', ' ', "'", "'", '&#9733;'], $word);
        $word = trim(preg_replace('#\s\s+#Uu', ' ', $word));

        return $word;
    }

    /**
     * Cette fonction sert à ajouter une proposition de traduction
	 * @param string $word Le mot ou l'expression à traduire
	 * @param string $trans La traduction proposée
	 * @return mixed L'état de l'insertion
	 */
    /*
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
    */

	/**
	 * Cette fonction sert à écrire les mots français dans la liste
	 * @return array
	 */
	public static function translate_writewords() {

        $octets = 0;
        $files = 0;

        if (self::$at_least_one_modification) {
            foreach (self::$words_fr as $domain => $words) {
                if (preg_match('~^characters\.~', $domain)) {
                    $domain = preg_replace('~^characters\.~', 'characters'.DS, $domain);
                }
                $export = var_export($words, true);
                $octets += (int) file_put_contents(ROOT.DS.'translation'.DS.'fr'.DS.$domain.'.php', "<?php return $export;");
                $files++;
            }
        }

        if (self::$write_en) {
            foreach (self::$words_en as $domain => $words) {
                if (preg_match('~^characters\.~', $domain)) {
                    $domain = preg_replace('~^characters\.~', 'characters'.DS, $domain);
                }
                $export = var_export($words, true);
                $octets += (int) file_put_contents(ROOT.DS.'translation'.DS.'en'.DS.$domain.'.php', "<?php return $export;");
                $files++;
            }
        }

		return array('octets'=>$octets,'files'=>$files);
	}
}
