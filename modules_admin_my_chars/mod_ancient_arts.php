<?php
$path[0] = 'ogham';
$ogham = (array) @$character->get($path[0]);

$path[1] = 'inventaire.artefacts';
$artefacts = (array) @$character->get($path[1]);

$path[2] = 'miracles.min';
$path[3] = 'miracles.maj';
$miracles_min = (array) @$character->get($path[2]);
$miracles_maj = (array) @$character->get($path[3]);
?>
<div class="row-fluid">
	<div class="span4">
		<h2><?php tr('Arts Demorthèn'); ?></h2>
		<label for="<?php echo $path[0]; ?>"><?php tr("Ogham"); ?> <small>(<?php tr('Un par ligne'); ?>)</small></label>
		<textarea id="<?php echo $path[0]; ?>" name="<?php echo $path[0]; ?>" rows="5" placeholder="<?php tr("Ajoutez ici les ogham que possède votre personnage"); ?>"><?php echo implode("\n", $ogham); ?></textarea>
	</div>
	<div class="span4">
		<h2><?php tr('Magience'); ?></h2>
		<label for="<?php echo $path[1]; ?>"><?php tr("Artefacts"); ?> <small>(<?php tr('Un par ligne'); ?>)</small></label>
		<textarea id="<?php echo $path[1]; ?>" name="<?php echo $path[1]; ?>" rows="5" placeholder="<?php tr("Ajoutez ici les artefacts que possède votre personnage"); ?>"><?php echo implode("\n", $artefacts); ?></textarea>
	</div>
	<div class="span4">
		<h2><?php tr('Culte du Temple'); ?></h2>
		<div>
			<label for="<?php echo $path[2]; ?>"><?php tr("Miracles mineurs"); ?> <small>(<?php tr('Un par ligne'); ?>)</small></label>
			<textarea id="<?php echo $path[2]; ?>" name="<?php echo $path[2]; ?>" rows="5" placeholder="<?php tr("Ajoutez ici les miracles mineurs que possède votre personnage"); ?>"><?php echo implode("\n", $miracles_min); ?></textarea>
		</div>
		<div>
			<label for="<?php echo $path[3]; ?>"><?php tr("Miracles majeurs"); ?> <small>(<?php tr('Un par ligne'); ?>)</small></label>
			<textarea id="<?php echo $path[3]; ?>" name="<?php echo $path[3]; ?>" rows="5" placeholder="<?php tr("Ajoutez ici les miracles majeurs que possède votre personnage"); ?>"><?php echo implode("\n", $miracles_maj); ?></textarea>
		</div>
	</div>
</div>
<?php unset($path); ?>