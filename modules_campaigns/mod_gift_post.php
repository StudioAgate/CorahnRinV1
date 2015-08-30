<?php

## Récupération de $_POST à partir des réelles données POST, pour obtenir les bons noms de variable entrées en paramètre,
$post = get_post_datas();

foreach ($post['arme'] as $k => $v) {
	if (!$v) { unset($post['arme'][$k]); }
}
foreach ($post['armure'] as $k => $v) {
	if (!$v) { unset($post['armure'][$k]); }
}

$game_id = $post['game_id'];
unset($post['game_id']);
$char_id = $post['char_id'];
unset($post['char_id']);

$char = new Esterenchar($char_id, 'db');

$char->set('experience.total', '+='.$post['exp']);
$char->set('experience.reste', '+='.$post['exp']);

$str = 'SELECT * FROM %%armes WHERE %arme_id IN (%%%in)';
$t = $db->req('SELECT * FROM %%armes WHERE %arme_id IN (%%%in)', array_keys($post['arme']));
$armes = array();
if ($t) {
	foreach ($t as $v) {
		$doms = explode(',', $v['arme_domain']);
		$armes_dom = array();
		foreach($doms as $d) { $armes_dom[$d] = $char->get('domaines.'.$d.'.name'); }
		$armes[$v['arme_id']] = array(
			'id' => $v['arme_id'],
			'name' => $v['arme_name'],
			'degats' => $v['arme_dmg'],
			'domaines' => $armes_dom
		);
	}
}
$char->set('inventaire.armes', $armes);
unset($t,$v);

$t = $db->req('SELECT %armure_id,%armure_name,%armure_prot FROM %%armures WHERE %armure_id IN (%%%in)', array_keys($post['armure']));
if (!$t) { $t = array(); }
$armures = array();
if ($t) {
	foreach ($t as $v) {
		$armures[$v['armure_id']] = array(
			'id' => $v['armure_id'],
			'name' => $v['armure_name'],
			'protection' => $v['armure_prot'],
		);
	}
}
$char->set('inventaire.armures', $armures);
unset($t,$v);

$char->set('inventaire.argent', $char->get_daols(array(
    'braise' => abs((int) $post['daols_braise']),
    'azur' => abs((int) $post['daols_azur']),
    'givre' => abs((int) $post['daols_givre']),
)));

$traumaCurables = abs((int) $post['trauma_curable']);
$traumaPerma = abs((int) $post['trauma_perma']);
if ($traumaCurables + $traumaPerma <= 20) {
    $char->set('traumatismes.curables', $traumaCurables);
    $char->set('traumatismes.permanents', $traumaPerma);
} else {
    Session::setFlash('Les valeurs de traumatismes n\'ont pas été modifiées car leur somme est supérieure à 20. Merci de les vérifier.', 'warning');
}

$endurcissement = abs((int) $post['endur_perma']);
$char->set('endurcissement', $endurcissement < 20 ? $endurcissement : 20);

if ($char->update_to_db()) {
	Session::setFlash('Le personnage a été correctement modifié !', 'success');
	header('Location:'.mkurl(array('val'=>60,'params'=>array($game_id))));
	exit;
} else {
	Session::setFlash('Vous n\'avez spécifié aucune modification pour le personnage.', 'notif');
	header('Location:'.mkurl(array('params'=>array($game_id, $char_id))));
	exit;
}

