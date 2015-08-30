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
	BASE_URL.'/css/pages/pg_'.$_PAGE['get'].'.css'
);
if (isset($_PAGE['more_css'])) {
	foreach ((array) $_PAGE['more_css'] as $v) { $css[] = $v; }// Ajout des fichiers css supplémentaires demandés dans $_PAGE
	unset($v);
}

?><!DOCTYPE html>
<!--[if lt IE 7]>		<html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="<?php echo P_LANG; ?>"> <![endif]-->
<!--[if IE 7]>			<html class="no-js lt-ie9 lt-ie8" lang="<?php echo P_LANG; ?>"> <![endif]-->
<!--[if IE 8]>			<html class="no-js lt-ie9" lang="<?php echo P_LANG; ?>"> <![endif]-->
<!--[if gt IE 8]><!-->	<html class="no-js" lang="<?php echo P_LANG; ?>"> <!--<![endif]-->
	<head>

		<meta http-equiv="Content-type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">
		<meta name="generator" content="<?php echo str_replace('{version}', $_PAGE['version']['code'], P_META_GENERATOR); ?>" />
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo BASE_URL; ?>/img/favicon-144.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo BASE_URL; ?>/img/favicon-114.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo BASE_URL; ?>/img/favicon-72.png">
		<link rel="apple-touch-icon-precomposed" href="<?php echo BASE_URL; ?>/img/favicon-57.png">
		<link rel="shortcut icon" href="<?php echo BASE_URL; ?>/img/favicon.png">

		<title><?php echo tr('Corahn Rin', true), ' - ', (isset($_PAGE['title_for_layout']) && $_PAGE['title_for_layout'] ? $_PAGE['title_for_layout'] : tr($_PAGE['anchor'], true)); ?></title>

		<?php foreach($css as $v) {
			if (FileAndDir::fexists(mkurl_to_internal_url($v))) {?>
<link rel="stylesheet" type="text/css" href="<?php echo $v; ?>" />
		<?php } } unset($css,$v); ?>

	</head>
	<body<?php echo $_PAGE['style'] ? ' id="'.$_PAGE['style'].'"' : ''; ?>>
		<!--[if lt IE 7]>
			<p class="chromeframe">Vous utilisez un navigateur <strong>trop vieux</strong>. Merci de <a href="http://www.mozilla.org/">mettre à jour votre navigateur</a> ou <a href="http://www.google.com/chromeframe/?redirect=true">activer Google Chrome Frame</a> pour une expérience optimale du web.</p>
		<![endif]-->

		<div class="container" id="navigation">
			<?php echo $_PAGE['nav_for_layout']; ## NAVIGATION ## ?>
		</div><!-- /div#navigation.container -->

		<?php
		//Affichage du message flash s'il existe
		if (Session::check('flash_bag')) {
            $flashBag = Session::getFlashbag();
			$err_ok = array('info','success','error','warning','notif','info noicon','success noicon','error noicon','warning noicon','notif noicon');

			?><div class="container"><?php
                foreach ($flashBag as $type => $messages) {
                    $err = in_array($type, $err_ok);
                    if ($err) { ?><div class="alert <?php echo $type; ?>"><?php }
                    $i = 0;
                    foreach ($messages as $message) {
                        if ($i>0){echo'<br>';}else{$i++;}
                        tr($message, false, array(), 'flash_messages');
                    }
                    if ($err) { ?></div><?php }
                }
			?></div><?php

			if (isset($_SESSION['send_mail'])) {
				echo '<div class="container">',
					mkurl(array('val'=>64,'type'=>'tag', 'anchor'=>'Renvoyer le mail de confirmation ?', 'attr' => array('class'=>'btn btn-info'), 'params'=>array('resend_register',$_SESSION['send_mail']))),
				'</div>';
				unset($_SESSION['send_mail']);
			}

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
			<?php echo $_PAGE['content_for_layout']; ## MODULE ## ?>
		</div>

        <?php Translate::$domain = 'footer'; ?>
		<div class="container" id="footer">
			<hr />
			<footer>
				<p><?php
					echo tr('Version', true).' : '.$_PAGE['version']['code'];
				?> &ndash; <?php
					echo tr('Dernière mise à jour', true).' : '.$_PAGE['version']['date'];
				?></p>
				<p><?php tr("Tous droits réservés - Pierstoval"); ?> 2011-2015</p>
				<p><?php tr("Tous les contenus sont générés par l'auteur du site, mais proviennent des livres des Ombres d'Esteren,<br />et appartiennent au collectif Forgesonges, et édité par Agate Editions."); ?></p>
				<p><?php tr('Page générée en'); ?> {PAGE_TIME} <?php tr('millisecondes'); ?></p>
                <div>{QUERIES}</div>
			</footer>
		</div>
		<div style="display: none;"><?php
			//Just for fun
			$str = 'Decode this and mail me at pierstoval@gmail.com with this number : 300489';
			for ($i = 0; $i < strlen($str); $i++) {
				$val = decbin(ord($str[$i]));
				while (strlen($val) < 7) {
					$val = '0'.$val;
				}
				echo $val;
			}
			unset($str,$i,$val);
			?></div>

		<?php
		// Création de la liste des fichiers js
		$js = array(
			BASE_URL.'/js/vendor/modernizr-2.6.2-respond-1.1.0.min.js',
			BASE_URL.'/js/vendor/jquery-1.8.3.min.js',
		// 	BASE_URL.'/js/vendor/jquery-1.9.0.js',
			BASE_URL.'/js/vendor/jquery-ui-min.js',
			BASE_URL.'/js/vendor/bootstrap.js',
			BASE_URL.'/js/main.min.js',
			BASE_URL.'/js/pages/pg_'.$_PAGE['get'].'.js'
		);
		if (isset($_PAGE['more_js'])) {
			foreach ((array) $_PAGE['more_js'] as $v) { $js[] = $v; }// Ajout de fichiers JS si demandés dans $_PAGE
			unset($v);
		}
		?>

		<script type="text/javascript">var corahn_rin = '<?php echo BASE_URL.'/'.P_LANG; ?>';</script>
		<?php foreach($js as $v) { ?>
<script type="text/javascript" src="<?php echo $v; ?>"></script>

		<?php } unset($css,$js,$v); ?>
<?php /*<!--Analytics--><script type="text/javascript">(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', 'UA-43812649-1', 'pierstoval.com');ga('send', 'pageview');</script>*/ ?>

        <?php Translate::$domain = null; ?>
	</body>
</html>
