<?php

$hash = isset($_PAGE['request'][1]) ? $_PAGE['request'][1] : 0;

if (!$hash) {
	redirect(array('val'=>1));
}

$user = $db->row('SELECT %user_status, %user_id FROM %%users WHERE %user_confirm = ?', array($hash));

if (empty($user) || !$user) {
	redirect(array('val'=>1), 'Aucun utilisateur trouvé', 'error');
} elseif ($user['user_status'] !== 0) {
	redirect(array('val'=>1), 'L\'utilisateur est déjà enregistré', 'notif');
} elseif ($db->noRes('UPDATE %%users SET %user_status = :status WHERE %user_id = :id', array('status'=>1,'id'=>$user['user_id']))) {
	redirect(array('val'=>1), 'Votre inscription a été prise en compte ! Vous pouvez désormais vous connecter !', 'success');
} else {
	redirect(array('val'=>1), 'Une erreur est survenue lors de la récupération du mail', 'error');
}