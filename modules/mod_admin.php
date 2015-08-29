<?php

$admin_module = isset($_PAGE['request'][0]) ? $_PAGE['request'][0] : '';


if (P_LOGGED === false) {
	header('Location:'.mkurl(array('val'=>48)));
	exit;
} elseif (P_LOGGED === true) {

    // The next functions are used to generate automatic getters to the EsterenChar class
    /*
    function a(array $content, $charContent, $nestedKey = '') {
        $k = array_keys($content);
        foreach ($k as $key) {
            if (is_numeric($key)) { continue; }
            echo '<br>    * @method get'.ucfirst(trim($nestedKey, '.').ucfirst($key));
            if (isset($charContent[$nestedKey.$key]) && is_array($charContent[$nestedKey.$key])) {
                $content = $charContent[$nestedKey.$key];
                if (!is_numeric($key)) {
                    a($content, $charContent, $key.'.');
                }
            }
        }
    }
    $content = $charObj->get();

    $charContent = $charObj->get();
    a($charContent, $charContent);
    */

	?><div class="container"><h3><?php tr("Bienvenue sur le panneau d'administration"); ?></h3></div><?php

	//Chargement des modules spécifiques aux utilisateurs
	if (!$admin_module) {
		for ($i = 50; $i >= Users::$acl; $i--) {

			$admin_module_file = ROOT.DS.'modules_'.$_PAGE['get'].DS.'mod_'.$i.'.php';
			if (file_exists($admin_module_file)) {
				?><div class="container module_admin_container"><?php
					load_module($i, 'module', array(), false);
				?></div><?php
			}
		}
		unset($i);
	}

	if (!$admin_module && P_DEBUG === true) { $admin_module = 'modify_pages'; } elseif (!$admin_module && P_DEBUG === false) { $admin_module = 'modify_char'; }
	//Chargement du module spécifique à la requête
	if ($admin_module) {
		?><div class="container module_admin_container"><?php
			load_module($admin_module, 'module', array(), false);
		?></div><?php
	}

}//endif logged true
unset($module,$admin_module_file, $admin_module);

buffWrite('css', '
	label:hover { cursor: pointer; }
	.module_admin_container {
		-webkit-box-shadow: 0 0 15px #888;
		   -moz-box-shadow: 0 0 15px #888;
				box-shadow: 0 0 15px #888;
		-webkit-border-radius: 25px;
		   -moz-border-radius: 25px;
				border-radius: 25px;
		-webkit-box-sizing: border-box;
		   -moz-box-sizing: border-box;
				box-sizing: border-box;
		padding: 25px;
	}
	.listlinks a { color: #0088CC; }
	.centerlinks span[class*=icon-] { display: block; margin: 0 auto; }
	.centerlinks, .listlinks, .centerlinks *, .listlinks * { text-align: center; }
	.pageid { width: 9%; }
	.pageshow { width: 9%; }
	.pagelogin { width: 9%; }
	.pagegetmod { width: 25%; text-align: left; }
	.pageanchor { width: 30%; text-align: left; }
	.pageacl { width: 8%; text-align: center; }
	.pageadmin { width: 10%; text-align: center; }
	#debugmode, section.debugger { margin: 0px auto; width: 710px; }
	#createlink { text-align: center; width: 690px; margin: 0 auto; }
	ul.unstyled.inline { margin-top: 15px; }
	ul.unstyled.inline li.ib { margin: 2px; }
	#debugmode { text-align: center; }
	.debugactivated,
	#debugdes {
		width: 230px;
		padding: 10px;
		margin: 0 auto;
		text-align: center;
	}

');
buffWrite('js', "");
