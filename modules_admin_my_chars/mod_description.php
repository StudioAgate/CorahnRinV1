<?php
$description = $character->get('details_personnage.description');
$histoire = $character->get('details_personnage.histoire');
$faits = $character->get('details_personnage.faits');
?>
<div>
	<label for="details_personnage.description"><?php tr("Description"); ?></label>
	<input type="text" id="details_personnage.description" name="details_personnage.description" placeholder="<?php tr("Insérez ici une description de votre personnage"); ?>" value="<?php echo $description; ?>" />
</div>
<div>
	<label for="details_personnage.histoire"><?php tr("Histoire"); ?></label>
	<textarea id="details_personnage.histoire" name="details_personnage.histoire" rows="5" placeholder="<?php tr("Écrivez ici l'histoire de votre personnage"); ?>"><?php echo $histoire; ?></textarea>
</div>
<div>
	<label for="details_personnage.faits"><?php tr("Faits marquants"); ?></label>
	<textarea id="details_personnage.faits" name="details_personnage.faits" rows="5" placeholder="<?php tr("Écrivez ici les faits marquants dans l'histoire de votre personnage"); ?>"><?php echo $faits; ?></textarea>
</div>