<?php

use App\Session;
use App\Users;

$characters = $db->req('
	SELECT %%characters.%char_id, %%characters.%char_name, %%characters.%user_id, %%characters.%char_status,
		%%users.%user_name
	FROM %%characters
	LEFT JOIN %%users
		ON %%users.%user_id = %%characters.%user_id
	WHERE %%characters.%char_status = 0
	OR %%characters.%char_status IS NULL
	ORDER BY %%users.%user_name ASC
') ?: [];

$users = array();

foreach ($characters as $k => $v) {
	if ($v['user_name'] && $v['char_status'] == 0) {
		$users[$v['user_id']]['name'] = $v['user_name'];
		$users[$v['user_id']]['characters'][$v['char_id']] = $v;
	}
}

$send = false;
if (!empty($_POST)) {

	$countchar = false;
	if (!isset($_POST['char_select'])) {
		Session::setFlash('Vous devez sélectionner au moins un personnage à intégrer à votre campagne', 'warning');
	} else {
		foreach ($_POST['char_select'] as $k => $v) {
			if ($v) {
				$_POST['char_select'][$k] = $k;
			} else {
				unset($_POST['char_select'][$k]);
			}
		}
		if (count($_POST['char_select'])) {
			$countchar = true;
		} else {
			Session::setFlash('Choisissez au moins un personnage à inviter', 'warning');
		}
	}

	if ($countchar === true) {
		$send = true;
	}
}

if ($send === true) {
	$send_chars =  $db->req('
		SELECT %%characters.%char_id, %%characters.%char_name, %%characters.%user_id,
			%%users.%user_name, %%users.%user_email
		FROM %%characters
		LEFT JOIN %%users
			ON %%users.%user_id = %%characters.%user_id
		WHERE %%characters.%char_id IN (%%%in)', array_values($_POST['char_select']));

	$datas = array(
		'game_id' => $game_id,
		'char_status' => 0,
	);
	$msg_invite = $db->row('SELECT %mail_id, %mail_contents, %mail_subject FROM %%mails WHERE %mail_code = ?', 'campaign_invite');
    $subj = tr($msg_invite['mail_subject'], true, null, 'mails');
	if ($send_chars) {
		foreach ($send_chars as $k => $v) {
			unset($datas['char_confirm_invite'], $datas['char_id']);
			$sql = 'UPDATE %%characters SET %game_id = :game_id, %char_status = :char_status, %char_confirm_invite = :char_confirm_invite WHERE %char_id = :char_id ';
			$datas['char_confirm_invite'] = md5($v['char_name'].microtime(true));
			$datas['char_id'] = $v['char_id'];
			$result[] = $db->noRes($sql, $datas);


            $txt = tr($msg_invite['mail_contents'], true, array(
                '{user_name}' => $v['user_name'],
                '{cp_name}' => $game['game_name'],
                '{char_name}' => $v['char_name'],
                '{cp_mj}' => Users::$name,
                '{link}' => mkurl(array('val'=>64,'type'=>'tag','anchor'=>'Confirmer l\'invitation','trans'=>true,'params'=>array('confirm_campaign_invite', $datas['char_confirm_invite']))),
            ), 'mails');

			$dest = array(
				'mail' => $v['user_email'],
				'name' => $v['user_name'],
			);

            try {
                send_mail($dest, $subj, $txt, $msg_invite['mail_id']);
                Session::setFlash('Le mail a bien été renvoyé à l\'utilisateur.');
            } catch (Exception $e) {
                Session::setFlash('La partie a été créée, mais une erreur est survenue dans l\'envoi de l\'email de confirmation à l\'un des joueurs...', 'warning');
            }
		}
	}
	if (!in_array(false, $result)) {
		redirect(array('val'=>60),'Les joueurs vont être avertis par mail et devront cliquer sur un lien dans ce mail pour participer à votre campagne. N\'hésitez pas à les prévenir, et à leur demander de vérifier (au cas où) leur boîte de courrier indésirable !', 'success');
	} else {
		Session::setFlash('Une erreur est survenue...', 'error');
	}
}
?>
<form id="game_name" action="<?php echo mkurl(array('params'=>$_PAGE['request'])); ?>" method="post" class="form-horizontal">
	<fieldset>
	<div class="container">
		<h3><?php tr('Campagne'); ?> : <?php echo $game['game_name']; ?></h3>
		<h4><?php tr('Invitez les joueurs à participer à votre campagne !'); ?></h4>
		<input type="submit" id="invite_players" class="btn btn-large btn-success" value="<?php tr('Inviter les joueurs'); ?>" />
		<div class="row-fluid">
		<?php
		foreach ($users as $user_id => $user) {
			$i = 0; ?>
			<h5><?php echo $user['name'], ' <small>', count($user['characters']), ' ', tr('personnage(s) disponible(s)', true), '</small>'; ?></h5>
			<div class="row-fluid char_list">
					<?php foreach ($user['characters'] as $k => $v) {
						if ($v['user_id'] == $user_id) {
							$btnchecked = isset($_POST['char_select']) ? (isset($_POST['char_select'][$v['char_id']]) ? ' btn-inverse' : '') : '';
							$inputval = isset($_POST['char_select']) ? (isset($_POST['char_select'][$v['char_id']]) ? '1' : '0') : '0';
						?>
						<a class="select_char span2 btn<?php echo $btnchecked; ?>" data-valid="char_select[<?php echo $v['char_id']; ?>]"><?php echo $v['char_name']; ?></a>
						<input type="hidden" name="char_select[<?php echo $v['char_id']; ?>]" value="<?php echo $inputval; ?>" />
					<?php
						$i++;
						if ($i % 4 === 0) { ?></div><div class="row-fluid"><?php }
					} }?>
			</div>
		<?php } ?>
		</div>
	</div><!-- /container -->
	</fieldset>
</form>
