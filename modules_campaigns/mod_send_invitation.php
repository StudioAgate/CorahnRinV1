<?php

use App\Session;
use App\Users;

/** @var int $game_mj */
/** @var int $char_id */

	$send_char =  $db->row('
		SELECT %%characters.%char_id, %%characters.%char_name, %%characters.%user_id,
			%%users.%user_name, %%users.%user_email
		FROM %%characters
		LEFT JOIN %%users
			ON %%users.%user_id = %%characters.%user_id
		WHERE %%characters.%char_id = ?
    ', $char_id);

	$game = $db->row('
		SELECT %%games.%game_name
		FROM %%games
		WHERE %%games.%game_name = ?
    ', $game_mj);

    if (!$send_char) {
        Session::setFlash('Une erreur est survenue...', 'error');
        redirect(array('val'=>60));
    }

    $msg_invite = $db->row('SELECT %mail_id, %mail_contents, %mail_subject FROM %%mails WHERE %mail_code = ?', 'campaign_invite');
    $subj = tr($msg_invite['mail_subject'], true, null, 'mails');

    $sql = 'UPDATE %%characters SET %char_confirm_invite = :char_confirm_invite WHERE %char_id = :char_id ';
    $data = [
        'char_confirm_invite' => md5($send_char['char_name'].microtime(true)),
        'char_id' => $char_id,
    ];
    $result = $db->noRes($sql, $data);

    $txt = tr($msg_invite['mail_contents'], true, array(
        '{user_name}' => $send_char['user_name'],
        '{cp_name}' => $game['game_name'],
        '{char_name}' => $send_char['char_name'],
        '{cp_mj}' => Users::$name,
        '{link}' => mkurl(array('val'=>64,'type'=>'tag','anchor'=>'Confirmer l\'invitation','trans'=>true,'params'=>array('confirm_campaign_invite', $data['char_confirm_invite']))),
    ), 'mails');

    $dest = array(
        'mail' => $send_char['user_email'],
        'name' => $send_char['user_name'],
    );

    try {
        send_mail($dest, $subj, $txt, $msg_invite['mail_id']);
        Session::setFlash('Le mail a bien été renvoyé à l\'utilisateur.');
    } catch (Exception $e) {
        $result = false;
    }
	if ($result) {
		redirect(array('val'=>60),'Les joueurs vont être avertis par mail et devront cliquer sur un lien dans ce mail pour participer à votre campagne. N\'hésitez pas à les prévenir, et à leur demander de vérifier (au cas où) leur boîte de courrier indésirable !', 'success');
	} else {
		Session::setFlash('Une erreur est survenue...', 'error');
        redirect(array('val'=>60));
    }
