<?php

//use JShrink\Minifier;

/**
 * Cette fonction permet d'écrire le contenu des fichiers CSS et JS compris dans chaque module.
 * Le contenu n'est écrit que dans le cas où le fichier n'existe pas déjà, où que l'on est sur le serveur local
 *
 * @param string $type Correspond à l'extension de fichier voulu (par défaut css ou js) défini dans la constante P_GEN_FILES_TYPES
 * @param string $content Contenu à écrire dans le fichier du type correspondant
 * @param string $dest Le fichier de destination
 * @author Pierstoval 26/12/2012
 */
function buffWrite($type, $content, $dest = '') {

		global $_PAGE;
		$type = (string) $type;
		$dest = (string) $dest;
		$content = (string) $content;
		if (!$dest) {
			$dest = WEBROOT.DS.$type.DS.'pages'.DS.'pg_'.$_PAGE['get'].'.'.$type;
		} else {
			if (strpos($dest, WEBROOT) === false) {
				$dest = WEBROOT.DS.$type.DS.'pages'.DS.'pg_'.$dest.'.'.$type;
			}
		}
// 		$type = strtolower($type);
// 		$types = explode(',', strtolower(P_GEN_FILES_TYPES));
// 		if (in_array($type, $types)) {
	if (P_GEN_FILES_ONLOAD === true || $_SERVER['HTTP_HOST'] === '127.0.0.1' || !FileAndDir::fexists($dest)) {
		//if ($type == 'js') { $content = Minifier::minify($content); }
		//$content = Minifier::minify($content);
		$content = minify($content, $type);
        if (!is_dir(dirname($dest))) {
            FileAndDir::createPath(dirname($dest));
            touch($dest);
        }
		FileAndDir::put($dest, $content);
	}
}

