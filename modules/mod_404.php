<?php
	header('HTTP/1.0 404 Not Found');
	header('Status: 404 Not Found');

?>
<div class="container">

	<div class="hero-unit">
		<h1><?php tr("Erreur 404"); ?></h1>
		<h2><?php tr("Non, vraiment, je n'ai trouvé aucune page qui convienne..."); ?></h2>
		<p><?php echo mkurl(array('val'=>1, 'type' => 'tag', 'anchor' => "Retour à l'accueil...", 'attr' => ' class="btn btn-danger btn-large"'));?></p>
	</div>
</div><!-- /container -->

	<?php
	buffWrite('css', <<<CSSFILE
	a.btn.btn-large.btn-danger { color: white; }
CSSFILE
);
	buffWrite('js', <<<JSFILE

JSFILE
);