<?php
$equipement = $character->get('inventaire.possessions');
$objets_precieux = (array) $character->get('inventaire.objets_precieux');
?>
<div class="row-fluid">
	<div class="span6">
		<label for="inventaire.possessions"><?php tr("Possessions"); ?> <small>(<?php tr('Une par ligne'); ?>)</small></label>
		<textarea id="inventaire.possessions" name="inventaire.possessions" rows="5" placeholder="<?php tr("Ajoutez ici les possessions de votre personnage"); ?>"><?php echo implode("\n", $equipement); ?></textarea>
	</div>
	<div class="span6">
		<label for="inventaire.objets_precieux"><?php tr("Objets précieux"); ?> <small>(<?php tr('Une par ligne'); ?>)</small></label>
		<textarea id="inventaire.objets_precieux" name="inventaire.objets_precieux" rows="5" placeholder="<?php tr("Ajoutez ici les objets précieux de votre personnage"); ?>"><?php echo implode("\n", $objets_precieux); ?></textarea>
	</div>
</div>