<?php
	header('HTTP/1.0 404 Not Found');
	header('Status: 404 Not Found');
?>
<div class="container">

	<div class="hero-unit">
		<h1><?php tr("Erreur 404"); ?></h1>
		<h2><?php tr("Non, vraiment, je n'ai trouvé aucune page qui convienne..."); ?></h2>
		<h3><?php echo Router::link(array('route_name'=>'core_home','route_type'=>'redirect', 'anchor'=>'Retour à l\'accueil', 'translate'=>true, 'force_route'=>true, 'type'=>'tag', 'attr'=>array('class'=>'btn btn-danger btn-large text-white')))?></h3>
	</div>

</div><!-- /container -->