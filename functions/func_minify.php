<?php

/**
 * Diminue la taille d'une chaîne de caractère, notamment pour les fichiers css et js
 *
 * @param string $content Le contenu à minifier
 * @param string $type    Le type de contenu envoyé
 *
 * @author Pierstoval 05/06/2013
 * @return mixed|string
 */
function minify($content, $type = 'css') {

	$content = preg_replace('#([^:]|^)//.*(\r|\n)#isUU', '$1', $content);
	$content = preg_replace('#/\*(.+)\*/#isUU', '', $content);

	$content = str_replace("\r", '', $content);
	$content = str_replace("\n", '', $content);
	$content = str_replace("\t", '', $content);
	$content = preg_replace('#\s\s+#isUu', ' ', $content);

	if ($type == 'css') {
		$content = preg_replace('#\.\./(\.\./)*#isUu', BASE_URL.'/', $content);
		$content = str_replace(' }', '}', $content);
		$content = str_replace(';}', '}', $content);
		$content = str_replace(' {', '{', $content);;
		$content = str_replace('{ ', '{', $content);
		$content = str_replace(', ', ',', $content);
		$content = str_replace(': ', ':', $content);
		$content = str_replace(' :', ':', $content);
	} elseif ($type == 'js') {
		$content = str_replace(' }', '}', $content);
		$content = str_replace(';}', '}', $content);
		$content = str_replace(' {', '{', $content);;
		$content = str_replace('{ ', '{', $content);
		$content = str_replace(', ', ',', $content);
		$content = str_replace(': ', ':', $content);
		$content = str_replace(' :', ':', $content);
		$content = str_replace(' !=', '!=', $content);
		$content = str_replace(' =', '=', $content);
		$content = str_replace('= ', '=', $content);
		$content = str_replace(' <', '<', $content);
		$content = str_replace(' >', '>', $content);
		$content = str_replace('< ', '<', $content);
		$content = str_replace('> ', '>', $content);
	}

	$content = str_replace('if (', 'if(', $content);
	$content = str_replace('} else', '}else', $content);
	$content = str_replace('function (', 'function(', $content);

	return $content;
}
