<?php

$hash = isset($_PAGE['request'][1]) ? $_PAGE['request'][1] : 0;

if (!$hash) {
	redirect(array('val'=>1));
}

$char = $db->row('SELECT %char_id, %char_status FROM %%characters WHERE %char_confirm_invite = ?', array($hash));

if (!empty($char) && $char['char_status'] === 0) {
	if ($char['char_id']) {
		$db->noRes('UPDATE %%characters SET %char_status = :status WHERE %char_id = :id', array('status'=>1,'id'=>$char['char_id']));
		redirect(array('val'=>60), 'Vous avez désormais accès à cette campagne !', 'success');
	}
} elseif (empty($char)) {
	redirect(array('val'=>60), 'Aucun personnage trouvé', 'error');
} elseif ($char['char_status'] !== 0) {
	redirect(array('val'=>60), 'Ce personnage est déjà dans une campagne', 'error');
}