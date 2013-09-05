<?php
unset($filename);

// On minimise les css et js de base
buffWrite('js', FileAndDir::get(WEBROOT.DS.'js'.DS.'main.js'), WEBROOT.DS.'js'.DS.'main.min.js');
buffWrite('css', FileAndDir::get(WEBROOT.DS.'css'.DS.'main.css'), WEBROOT.DS.'css'.DS.'main.min.css');

// Création de la liste des fichiers css
$css = array(
	BASE_URL.'/css/jquery-ui.css',
	BASE_URL.'/css/bootstrap.min.css',
	BASE_URL.'/css/bootstrap-responsive.min.css',
	BASE_URL.'/css/main.min.css',
	BASE_URL.'/css/icons-large.css',
	BASE_URL.'/css/pages/pg_'.$this->controller->module.'.css'
);
if (isset($css_for_layout)) {
	foreach ((array) $css_for_layout as $v) { $css[] = $v; }// Ajout des fichiers css supplémentaires demandés dans $_PAGE
	unset($v);
}

?><!DOCTYPE html>
<!--[if lt IE 7]>		<html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="fr"> <![endif]-->
<!--[if IE 7]>			<html class="no-js lt-ie9 lt-ie8" lang="fr"> <![endif]-->
<!--[if IE 8]>			<html class="no-js lt-ie9" lang="fr"> <![endif]-->
<!--[if gt IE 8]><!-->	<html class="no-js" lang="fr"> <!--<![endif]-->
	<head>

		<meta http-equiv="Content-type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">
		<meta name="generator" content="<?php echo str_replace('{version}', P_VERSION_CODE, P_META_GENERATOR); ?>" />
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo BASE_URL; ?>/img/favicon-144.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo BASE_URL; ?>/img/favicon-114.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo BASE_URL; ?>/img/favicon-72.png">
		<link rel="apple-touch-icon-precomposed" href="<?php echo BASE_URL; ?>/img/favicon-57.png">
		<link rel="shortcut icon" href="<?php echo BASE_URL; ?>/img/favicon.png">

		<title><?php echo tr('Corahn Rin', true), ($title_for_layout ? ' - ' : ''), tr($title_for_layout, true); ?></title>

		<?php foreach($css as $v) {
			if (FileAndDir::fexists(mkurl_to_internal_url($v))) {?>
<link rel="stylesheet" type="text/css" href="<?php echo $v; ?>" />
		<?php } else { ?>
<!-- wrong css : <?php echo $v; ?> -->
		<?php
		}

		} unset($css,$v); ?>

	</head>
	<body<?php echo isset($layout_style) ? ' id="'.$layout_style.'"' : ''; ?>>
		<!--[if lt IE 7]>
			<p class="chromeframe">Vous utilisez un navigateur <strong>trop vieux</strong>. Merci de <a href="http://www.mozilla.org/">mettre à jour votre navigateur</a> ou <a href="http://www.google.com/chromeframe/?redirect=true">activer Google Chrome Frame</a> pour une expérience optimale du web.</p>
		<![endif]-->

		<div class="container" id="navigation">
			<?php $this->render_controller('Pages', 'menu'); ?>
		</div><!-- /div#navigation.container -->

		<?php
		//Affichage du message flash s'il existe
		if (Session::check('Flash')) {
			$message = Session::read('Flash.message');
			$err = Session::read('Flash.type');
			$tr_params = Session::read('Flash.tr_params');
			$err_ok = array('info','success','error','warning','notif','info noicon','success noicon','error noicon','warning noicon','notif noicon');
			if (!in_array($err, $err_ok)) { $err = ''; }

			if (is_array($message)) {
				foreach ($message as $k => $v) {
					?><div class="container"><?php
						if (isset($err[$k])) { ?><div class="<?php echo $err[$k]; ?>"><?php }
						tr($v, null, $tr_params);
						if (isset($err[$k])) { ?></div><?php }
					?></div><?php
				}
			} else {
				?><div class="container"><?php
					if ($err) { ?><div class="<?php echo $err; ?>"><?php }
					tr($message, null, $tr_params);
					if ($err) { ?></div><?php }
				?></div><?php
			}

			if (isset($_SESSION['send_mail'])) {
				echo '<div class="container">',
					mkurl(array('val'=>64,'type'=>'tag', 'anchor'=>'Renvoyer le mail de confirmation ?', 'attr' => array('class'=>'btn btn-info'), 'params'=>array('resend_register',$_SESSION['send_mail']))),
				'</div>';
				unset($_SESSION['send_mail']);
			}

			Session::delete('Flash');
			unset($message,$err,$err_ok);
		}

		//Affichage de la boîte de changement pour revenir à l'utilisateur précédent
		if (Session::check('userchanged')) {
			$prev_user = Session::read('userchanged');
			?><div class="container"><?php
			echo mkurl(array('val'=>1, 'type'=>'tag', 'attr'=>array('class'=>'btn btn-link'), 'anchor'=>'Réinitialiser les droits', 'get'=>array('userchange'=>$prev_user)));
			?></div><?php
			if (isset($_GET['userchange'])) {
				Users::init($prev_user);
				Session::delete('userchanged');
				redirect(array(), 'Revenu correctement à l\'utilisateur précédent', 'success');
			}
		}
		?>

		<div id="corps">
			<div id="err"></div>
			<?php echo $content_for_layout; ## MODULE ## ?>
		</div>

		<div class="container" id="footer">
			<hr />
			<footer>
				<p><?php
					echo tr('Version', true).' : '.P_VERSION_CODE;
				?> &ndash; <?php
					echo tr('Dernière mise à jour', true).' : '.P_VERSION_DATE;
				?></p>
				<p><?php tr("Tous droits réservés &ndash; Pierstoval"); ?> 2012-2013</p>
				<p><?php tr("Tous les contenus sont générés par l'auteur du site, mais proviennent des livres des Ombres d'Esteren,<br />et appartiennent au collectif Forgesonges, et édité par Agate Editions."); ?></p>
				<p><?php tr('Page générée en {PAGE_TIME} millisecondes'); ?></p>
			</footer>
		</div>
		<div class="hide">1000100110010111000111101111110010011001010100000111010011010001101001111001101000001100001110111011001000100000110110111000011101001110110001000001101101110010101000001100001111010001000001110000110100111001011110010111001111101001101111111011011000011101100100000011001111101101110000111010011101100010111011000111101111110110101000001110111110100111101001101000010000011101001101000110100111100110100000110111011101011101101110001011001011110010010000001110100100000011001101100000110000011010001110000111001<?php
			//Just for fun
// 			$str = 'Decode this and mail me at pierstoval@gmail.com with this number : 300489';
// 			for ($i = 0; $i < strlen($str); $i++) {
// 				$val = decbin(ord($str[$i]));
// 				while (strlen($val) < 7) {
// 					$val = '0'.$val;
// 				}
// 				echo $val;
// 			}
// 			unset($str,$i,$val);
			?></div>

		<?php
		// Création de la liste des fichiers js
		$js = array(
// 			BASE_URL.'/js/vendor/modernizr-2.6.2-respond-1.1.0.min.js',
			BASE_URL.'/js/vendor/jquery-1.8.3.min.js',
// 			BASE_URL.'/js/vendor/jquery-1.9.0.js',
			BASE_URL.'/js/vendor/jquery-ui-min.js',
			BASE_URL.'/js/vendor/bootstrap.js',
			BASE_URL.'/js/main.min.js',
			BASE_URL.'/js/pages/pg_'.$this->controller->module.'.js'
		);
		if (isset($js_for_layout)) {
			foreach ((array) $js_for_layout as $v) { $js[] = $v; }// Ajout de fichiers JS si demandés dans $_PAGE
			unset($v);
		}

		file_put_contents(WEBROOT.DS.'js'.DS.'base_url.js', 'const corahn_rin = "'.BASE_URL.'";');
		array_unshift($js, BASE_URL.'/js/base_url.js');

		foreach($js as $v) {
		if (FileAndDir::fexists(mkurl_to_internal_url($v))) {?>
<script type="text/javascript" src="<?php echo $v; ?>"></script>
		<?php } } unset($css,$js,$v); ?>

	</body>
</html>