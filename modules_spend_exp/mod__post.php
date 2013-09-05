<?php

$t = $db->req('SELECT %disc_id,%disc_name FROM %%disciplines WHERE %disc_rang = "Professionnel"');
$discs = array();
foreach ($t as $v) {
	$discs[$v['disc_id']] = $v;
}
$_POST = get_post_datas();

$send = true;

$required_post_datas = array(//Champs de formulaires obligatoires
	'rapidite.amelioration',
	'defense.amelioration',
	'exp',
);
foreach ($required_post_datas as $v) {
	if (!isset($_POST[$v])) {
		$send = false;
	}
}

if ($send === false) {
	redirect(array('val'=>50), 'Le formulaire envoyé comporte des erreurs', 'error');
}

foreach ($_POST as $k => $v) {
	if ($v && strpos($k, 'exp') === false) {
		if (preg_match('#^domaines\.[0-9]+$#isUu', $k)) {
			$k .= '.val';
			$char->set($k, $v);
		} elseif (strpos($k, 'disciplines') !== false) {
			//$k .= '.val';
			$k = preg_replace('#{n}\[id=([0-9]+)\]#isUu', '$1', $k);
			$id = (int) preg_replace('#^.*disciplines\.([0-9]+)$#isUu', '$1', $k);
			if ($id) {
				$v = array(
					'id' => $id,
					'name' => $discs[$id]['disc_name'],
					'val' => $v,
				);
				$char->set($k, $v);
			}
		} else {
			$char->set($k, $v);
		}
	}
}
$char->set('experience.reste', $_POST['exp']);

$char->update_to_db();
unset($t,$discs,$k,$v,$id);

redirect(array('val' => 58), 'Le personnage a été correctement modifié !');