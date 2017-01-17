<?php
/**
 * Classe de gestion de fichiers locaux et distants.
 */
class FileAndDir {

/**
 * Crée une arborescence (vérifie son existence) en fonction du chemin indiqué
 */
	public static function createPath($path, $mod = 0777) {
		$path_pieces = explode(DS, $path);
		$path = '';
		while(!is_null($piece = array_shift($path_pieces))) {
			$path .= $piece.DS;
			if(!is_dir($path)) { self::createDirectory($path, $mod); }
		}
	}

/**
 * Crée un répertoire à l'endroit spécifié.
 * @param   string  $path    Dossier à créer.
 * @param   int     $mod     Nouveaux droits du fichier (en octal). Exemple : 0777
 */
	public static function createDirectory($path, $mod) {
		umask(0);
		mkdir($path, $mod);
	}

/**
 * Vérifie l'existence du fichier.
 * @return  bool Retourne true si le fichier existe, false le cas contraire.
 */
	public static function fexists($path) {
		clearstatcache();
		return file_exists($path);
	}

/**
 * Vérifie l'existence du dossier.
 * @return  bool Retourne true si le dossier existe, false le cas contraire.
 */
	public static function dexists($path) {
		clearstatcache();
		return is_dir($path);
	}

/**
 * Vérifie l'existence du dossier.
 * @return  bool Retourne true si le dossier existe, false le cas contraire.
 */
	public static function dwritable($path) {
		clearstatcache();
		return is_writable($path);
	}

/**
 * Modifie les droits d'un fichier.
 * @param int $mod Nouveaux droits du fichier (en octal). Exemple : 0777
 */
	public static function chProperties($path, $mod) {
		umask(0);
		chmod($path, $mod);
	}

/**
 * Supprime le fichier.
 * @return  bool Retourne true si le fichier a pu être supprimé, false sinon.
 */
	public static function remove($path) {
		if(file_exists($path)) { return unlink($path); }
		else { return false; }
	}

	public static function fcopy($source, $dest) {
		return copy($source, $dest);
	}

/**
 * Récupère le contenu du dossier
 * @param string $directory Le chemin à vérifier
 * @return array La liste des fichiers dans le dossier
 */
	public static function directoryContent($directory) {
		$files = array();
		$dir = opendir($directory);
		while($file = readdir($dir)) {
			if($file != '.' && $file != '..' && $file != 'empty' && !is_dir($directory.$file)) {
				//$directory.$file
				$files[] = $file;
			}
		}
		closedir($dir);
		return $files;
	}

/**
 * Supprime tous les fichiers et sous-dossiers du dossier concerné, ainsi que le dossier lui-même
 * @param string $chemin Le chemin à vérifier
 */
	public static function remove_directory($chemin) {
		// vérifie si le nom du repertoire contient "/" à la fin
		if($chemin[strlen($chemin)-1] != DS) { $chemin .= DS; } // rajoute '/'
		if(is_dir($chemin)) {
			$sq = opendir($chemin); // lecture
			while($f = readdir($sq)) {
				if($f != '.' && $f != '..') {
					$fichier = $chemin.$f; // chemin fichier
					if (is_dir($fichier)) {
						self::remove_directory($fichier); // rappel la fonction de manière récursive
					} else {
						unlink($fichier);
					} // supprime le fichier
				}
			}
			closedir($sq);
			rmdir($chemin); // sup le répertoire
		} elseif (is_file($chemin)) {
			unlink($chemin);  // sup le fichier
		}
	}

/**
 * Supprime tous les fichiers contenus dans le dossier, mais pas les sous-dossiers
 * @param string $dir Le chemin à vérifier
 */
	public static function delete_directory_file($dir) {
		foreach(self::directoryContent($dir) as $file) { self::remove($dir.$file); } //On supprime le fichier
	}

	/**
	 * Renvoie le contenu du fichier s'il existe, un message d'erreur sinon.
	 * @author  Josselin Willette
	 * @param   string    $type     Type de contenu à retourner (string (valeur par défaut), array) (facultatif).
	 * @param   string    $retour   Langage de retour dans le cas d'une erreur (facultatif).
	 * @param   int       $offset   Position à laquelle on commence à lire (facultatif).
	 * @param   int       $maxlen   Taille maximale d'octets (facultatif).
	 * @return  mixed               Contenu sous la forme du type passé en paramètre.
	 */
	public static function get($filename, $type = 'string', $retour = '', $offset = null, $maxlen = null) {
		$contents = '';
		if(file_exists($filename)) { $contents = file_get_contents($filename); }
		return $contents;
	}

	/**
	 * Ecrit le contenu passé en paramètre dans un fichier.
	 * @author  Josselin Willette
	 * @param   string    $content    Contenu à écrire.
	 * @param   int       $append     Précise si on écrase le fichier ou si on écrit à la fin (0 par défaut : écrase) (facultatif).
	 * @return  bool                  Retourne true en cas de succès et false en cas d'échec.
	 */
	public static function put($filename, $content, $append = 0) {
		try {
			if (!file_put_contents($filename, $content, $append)) {
				$message = error_get_last();
				$message = $message['message'];
				throw new Exception('Impossible d\'écrire dans ' . $filename . "\n" . $message);
			}
			return true;
		}
		catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Renomme le fichier.
	 * @author  Josselin Willette
	 * @param   string    $newName  Nouveau nom du fichier.
	 * @param   bool      $change   Change le nom du fichier de l'objet courant.
	 * @return  bool                Retourne true si le fichier a pu être renommé, false sinon.
	 */
	public function rename($filename, $newName, $change = false)
	{
		$o = rename($filename, $newName);
		if ($change == true) { $filename = $newName; }
		return $o;
	}

}
