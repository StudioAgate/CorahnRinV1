<?php

/**
 * Charge un élément spécifique
 *
 * @param string $module_name Le nom de l'élément à charger
 * @param string $module_type Le type d'élément. Définit le dossier de chargement du fichier
 * @param array $additionnal_vars Un tableau où les ensembles clé=>valeurs créent des variables locales à utiliser dans le module
 */
function load_module($module_name = '', $module_type = 'page', $additionnal_vars = array(), $show_err = true) {
	global $_PAGE, $db;
	$module_name = (string) $module_name; //On s'assure de l'intégrité des valeurs
	$module_type = (string) $module_type; //On s'assure de l'intégrité des valeurs

	$filename = array(//On définit les types d'éléments
		'page'		=> ROOT.DS.'modules'.DS.'mod_'.$module_name.'.php',
		'module'	=> ROOT.DS.'modules_'.$_PAGE['get'].DS.'mod_'.$module_name.'.php',
		'menu'		=> ROOT.DS.'includes'.DS.'inc_nav'.$module_name.'.php',
		'inc'		=> ROOT.DS.'includes'.DS.'inc_'.$module_name.'.php',
		'layout'	=> ROOT.DS.'layouts'.DS.'layout_'.$module_name.'.php',
	);
	if (isset($filename[$module_type]) && FileAndDir::fexists($filename[$module_type])) {//Si le type de module et le fichier existent
		$additionnal_vars = (array) $additionnal_vars; // On s'assure de l'intégrité des données à envoyer
		foreach ($additionnal_vars as $k => $v) { //On récupère toutes les variables à envoyer au module
			$k = (string) $k; //On s'assure de l'intégrité des clés
			if (!is_numeric($k)) {//On vérifie que la clé n'est pas une chaîne numérique
				if (strpos($k, '.') === false) {
					$$k = $v;//On crée les variables additionnelles qui seront ajoutées dans le scope local de la fonction
				} else {
					if (preg_match('#^([^.]+)\.(.+)$#isUu', $k, $matches) && isset($matches[1]) && isset($matches[2])) {
						${$matches[1]} = Hash::insert(${$matches[1]}, $matches[2], $v);
					}
				}
			}
		}
		//On supprime les variables qui deviennent inutiles si elles n'ont pas été envoyées au module
		if (!isset($additionnal_vars['k'])) { unset($k); }
		if (!isset($additionnal_vars['v'])) { unset($v); }
		if (!isset($additionnal_vars['module_name'])) { unset($module_name); }
		if (!isset($additionnal_vars['additionnal_vars'])) { unset($additionnal_vars); }
		require $filename[$module_type];
	} else {
		if ($show_err === true) {
			Session::setFlash('Le module "'.$module_name.'" n\'existe pas dans l\'élément "'.$module_type.'"', 'error');
		}
		return;
	}
}