<?php

$character = new Esterenchar($char_id, 'db');
if ($character->user_id() !== Users::$id) {
	Session::setFlash('Vous n\'avez pas le droit de consulter ce personnage', 'error');
	return;
}

$_POST = get_post_datas();

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
		Session::setFlash('Le personnage <u>'.$character->get('details_personnage.name').'</u> a été correctement modifié !', 'success');
		header('Location:'.mkurl(array('val'=>58)));
		exit;
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
