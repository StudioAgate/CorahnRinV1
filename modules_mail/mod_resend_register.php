<?php

$hash = isset($_PAGE['request'][1]) ? $_PAGE['request'][1] : 0;

if (!$hash) {
	redirect(array('val'=>1));
}

$user = $db->row('SELECT %user_name,%user_email,%user_status FROM %%users WHERE %user_confirm = ?', array($hash));

if (empty($user)) {
	redirect(array('val'=>1), 'Aucun utilisateur trouvé', 'error');
} elseif (!$user['user_status']) {
	$dest = array('name' => $user['user_name'], 'mail' => $user['user_email']);
	$mail_msg = $db->row('SELECT %mail_id, %mail_contents, %mail_subject FROM %%mails WHERE %mail_code = ?', 'register');
	if (isset($mail_msg['mail_contents']) && isset($mail_msg['mail_subject'])) {
		$subj = $mail_msg['mail_subject'];
		$txt = $mail_msg['mail_contents'];
		$txt = str_replace('{name}', htmlspecialchars($user['user_name']), $txt);
		$txt = str_replace('{link}', mkurl(array('val'=>64,'type'=>'tag','anchor'=>'Confirmer l\'adresse mail','params'=>array('confirm_register', $hash))), $txt);
		if (send_mail($dest, $subj, $txt, $mail_msg['mail_id'])) {
			redirect(array('val'=>48), 'Le mail de confirmation a été renvoyé !', 'success');
		} else {
			redirect(array('val'=>1), 'Erreur dans l\'envoi du mail. Votre adresse mail n\'est peut-être pas correcte', 'error');
		}
	} else {
		redirect(array('val'=>1), 'Une erreur est survenue lors de la récupération du mail', 'error');
	}
} else {
	redirect(array('val'=>1), 'L\'utilisateur est déjà enregistré', 'error');
}