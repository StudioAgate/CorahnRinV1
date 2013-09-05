<div class="container">

	<p class="info">
		<?php tr('Faites vos propositions !'); ?>
		<br />
		<?php tr('Si vous souhaitez contribuer à Corahn-Rin, n\'hésitez pas et postez vos propositions sur la page wiki du générateur :'); ?>
		<br />
		<?php
		echo Router::link(array('route'=>'http://tests.pierstoval.com/esteren_wiki/wiki/G%C3%A9n%C3%A9rateur','custom'=>true, 'type'=>'tag', 'anchor'=>'Page wiki de Corahn-Rin', 'attr' => array('class'=>'btn btn-link newwindow')));
		?>
	</p>

	<div class="hero-unit">
		<h1><?php tr("Corahn Rin"); ?></h1>
		<h2><?php tr('La plateforme de gestion de personnages pour &laquo;Les Ombres d\'Esteren&raquo;'); ?></h2>
		<p><?php tr("Ce générateur permet de créer un personnage pour \"Les Ombres d'Esteren\", directement en ligne, mais également de modifier son contenu, d'assister à des parties et de dépenser ses points d'expérience, et plein d'autres choses encore."); ?></p>
		<ul>
			<li><?php tr("Plus besoin de se reporter au livre à chaque étape"); ?></li>
			<li><?php tr("Peu de valeurs chiffrée pour une immersion maximale dans le personnage"); ?></li>
		   	<li><?php tr("Très simple : une étape, une information sur le personnage"); ?></li>
			<li><?php tr("La possibilité de revenir aux étapes précédentes"); ?></li>
		</ul>
		<p><small><?php tr("Deux mots d'ordre : Lisez les instructions, et surtout <strong>immergez-vous dans les Ombres...</strong>"); ?></small></p>
		<p><?php
		$anchor = (Session::read('etape') > 1 ? tr("Continuer la création du personnage", true) : tr("Commencer la création d'un personnage", true)) . ' &raquo;';
		echo Router::link(array('route'=>'characters/create', 'type'=>'tag', 'anchor'=>$anchor, 'attr' => array('class'=>'btn')));
		unset($anchor);
		?></p>
	</div>

	<div class="row-fluid">
		<div class="span4">
			<h3><?php tr('Des mises à jour régulières.'); ?></h3>
			<p><?php tr('Corahn Rin est mis à jour à intervalles réguliers pour assurer une compatibilité maximale avec les navigateurs les plus utilisés, mais également pour garantir des performances optimales.'); ?></p>
			<p><?php echo Router::link(array('route'=>'versions.html', 'type'=>'tag', 'anchor'=>'Voir les dernières mises à jour', 'attr' => array('class'=>'btn btn-inverse'))); ?></p>
		</div>
		<div class="span4">
			<h3><?php tr('Une liste de personnages directement utilisables.'); ?></h3>
			<p><?php tr('Vous avez accès à une liste de personnages créés par les visiteurs du site, ceux-ci sont directement utilisables : visualisez les feuilles de personnages, téléchargez-les et imprimez-les !'); ?></p>
			<p><?php echo Router::link(array('route'=>'characters.html', 'type'=>'tag', 'anchor'=>'Voir la liste des personnages', 'attr' => array('class'=>'btn btn-inverse'))); ?></p>
		</div>
		<div class="span4">
			<h3><?php tr('Votre compte pour gérer vos personnages et vos parties.'); ?></h3>
			<p><?php tr('En vous inscrivant, vos personnages seront conservés quoiqu\'il arrive, mais vous pourrez surtout les modifier et rejoindre une campagne, organisée par un Maître de Jeu, où vous pourrez gagner et dépenser votre expérience.'); ?></p>
			<p><?php echo Router::link(array('route_name'=>'core_register', 'type'=>'tag', 'anchor'=>'Inscrivez-vous', 'attr' => array('class'=>'btn btn-inverse'))); ?></p>
		</div>
	</div>
</div><!-- /container -->

<?php

// buffWrite('css', '
// 	body {
// 		/*background: url("'.BASE_URL.'/img/esteren_set1_logo.png'.'");*/
// 		background-size: cover;
// 	}
// 	.hero-unit {
// 		/*background: rgba(240,240,240,0.9);*/
// 	}
// ');
// buffWrite('js', '');
