<?php

use App\EsterenChar;
use App\FileAndDir;
use App\Session;
use App\Users;

$character = new Esterenchar($char_id, 'db');
if ($character->user_id() !== Users::$id) {
	Session::setFlash('Vous n\'avez pas le droit de consulter ce personnage', 'error');
	return;
}

$_POST = get_post_datas();

## On va traiter la suppression au cas où l'on demande à supprimer un personnage
$del = isset($_PAGE['request'][1]) ? $_PAGE['request'][1] : '';
if ($del === 'delete') {
	if (!empty($_POST)) {
		if (isset($_POST['delete']) && $_POST['delete'] === 'yes') {
			if ($character->delete_char()) {
                unset($character);
				redirect(array('val'=>58), tr('Le personnage a été correctement supprimé !', true), 'success');
			} else {
				redirect(array('params'=>$_PAGE['request']), 'Personnage non supprimé. #003', 'error');
			}
		}
	}
	?>
	<form method="post" action="<?php echo mkurl(array('params'=>$_PAGE['request'])); ?>">
		<fieldset>
			<h3><?php echo $character->get('details_personnage.name'); ?></h3>
			<p class="error"><?php tr('Voulez-vous vraiment supprimer ce personnage ?'); ?></p>
			<?php echo mkurl(array('type'=>'tag','anchor'=>'&larr; '.tr('Retour à la page précédente', true), 'attr'=>array('class'=>'btn btn-success', 'style'=>'color:#fff'))); ?>
			&nbsp; <button type="submit" class="btn btn-mini btn-danger" name="delete" value="yes" id="delete"><?php tr('Valider la suppression'); ?></button>
		</fieldset>
	</form>
	<?php
	return;
}

##ON VA TRAITER LES DONNÉES POST POUR SAUVEGARDER LE PERSONNAGE
if (!empty($_POST)) {
	$fields_to_explode = array(
		'inventaire.possessions',
		'inventaire.artefacts',
		'inventaire.objets_precieux',
		'ogham',
		'miracles.min',
		'miracles.maj',
	);
	//Chemins autorisés dans le tableau Esterenchar->char
	$available_fields = array(
		'details_personnage.description' => 1,
		'details_personnage.histoire' => 1,
		'details_personnage.faits' => 1,
	);
	foreach($fields_to_explode as $v) {
		$_POST[$v] = explode("\n", $_POST[$v]);
		foreach ($_POST[$v] as $k => $vv) {
			$vv = str_replace("\r", '', $vv);
			$vv = str_replace("\n", '', $vv);
			$_POST[$v][$k] = $vv;
		}
		$available_fields[$v] = 1;
	}
	$err = '';
	foreach($_POST as $key => $value) {
		if (isset($available_fields[$key])) {
			$character->set($key, $value);
		}
	}
	if ($character->update_to_db()) {
		redirect(array('val'=>58), 'Le personnage a été correctement modifié !', 'success');
	}
}


$modules_list = array(
	'description' => 'Description et histoire',
	'inventaire' => 'Inventaire',
	'ancient_arts' => 'Arts anciens',
);
?>
	<form id="modify_char" method="post" action="<?php echo mkurl(array('params' => $char_id)); ?>">
		<fieldset>
			<h3><?php echo $character->get('details_personnage.name'); ?></h3>
			<div><button type="submit" class="btn btn-success" id="modify"><?php tr('Valider toutes les modifications'); ?></button></div>
			<hr />
				<ul class="nav nav-tabs" id="modify_tabs">
					<?php $i = 0; foreach($modules_list as $file => $title) {
						$file_to_load = ROOT.DS.'modules_'.$_PAGE['get'].DS.'mod_'.$file.'.php';
						if (FileAndDir::fexists($file_to_load)) { ?>
						<li<?php echo $i === 0 ? ' class="active"' : ''; ?>><a data-toggle="tab" href="#<?php echo $file; ?>"><?php tr($title); ?></a></li>
						<?php $i++; }
					} ?>
				</ul>
				<div class="tab-content" id="myTabContent">
					<?php $i = 0; foreach($modules_list as $file => $title) {
						$file_to_load = ROOT.DS.'modules_'.$_PAGE['get'].DS.'mod_'.$file.'.php';
						if (FileAndDir::fexists($file_to_load)) {?>
						<div id="<?php echo $file; ?>" class="tab-pane fade<?php echo $i === 0 ? ' in active' : ''; ?>"><?php require $file_to_load; ?></div>
						<?php $i++; }
					} ?>
				</div>

		</fieldset>
	</form>
