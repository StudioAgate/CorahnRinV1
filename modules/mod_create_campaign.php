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
	ORDER BY %%users.%user_name ASC');

$users = array();

if (!is_array($characters)) {
    Session::setFlash('Erreur dans la liste des personnages...', 'error');
    redirect(1);
}

foreach ($characters as $k => $v) {
	if ($v['user_name'] && $v['char_status'] == 0) {
		$users[$v['user_id']]['name'] = $v['user_name'];
		$users[$v['user_id']]['characters'][$v['char_id']] = $v;
	}
}

$send = false;
if (!empty($_POST)) {
	if (!isset($_POST['game_summary']) || !isset($_POST['game_name'])) {
		Session::setFlash('Une erreur est survenue, veuillez recommencer...', 'error');
		$_POST = array();
		header('Location:'.mkurl($_PAGE['id']));
	}
	if (!isset($_POST['char_select'])) {
		Session::setFlash('Vous devez sélectionner au moins un personnage à intégrer à votre campagne', 'warning');
	}
	if (isset($_POST['game_name']) && !$_POST['game_name']) {
		Session::setFlash('Vous devez au moins donner un nom à votre campagne', 'error');
	}
	if (isset($_POST['char_select'])) {
		foreach ($_POST['char_select'] as $k => $v) {
			if ($v) {
				$_POST['char_select'][$k] = $k;
			} else {
				unset($_POST['char_select'][$k]);
			}
		}
	}

	if (isset($_POST['game_name'], $_POST['game_summary']) && !empty($_POST['game_name'])) {
		$send = true;
	}
}

if ($send === true) {
	$game = array(
		'game_name' => $_POST['game_name'],
		'game_summary' => $_POST['game_summary'],
		'game_mj' => Users::$id,
	);

	$result1 = $db->noRes('INSERT INTO %%games SET %%%fields', $game);
	$id = $db->last_id();
	if ($id) {
		$result1 = true;
	} else {
		$result1 = false;
	}
	$data = array(
		'game_id' => $id,
		'char_status' => 0,
	);
	$msg_invite = $db->row('SELECT %mail_id, %mail_contents, %mail_subject FROM %%mails WHERE %mail_code = ?', 'campaign_invite');
	$subj = tr($msg_invite['mail_subject'], true, null, 'mails');
    $result2 = [];
	if (!empty($_POST['char_select'])) {
        $send_chars =  $db->req('
            SELECT %%characters.%char_id, %%characters.%char_name, %%characters.%user_id,
                %%users.%user_name, %%users.%user_email
            FROM %%characters
            LEFT JOIN %%users
                ON %%users.%user_id = %%characters.%user_id
            WHERE %%characters.%char_id IN (%%%in)', array_values($_POST['char_select']));

		foreach ($send_chars as $k => $v) {
			unset($data['char_confirm_invite'], $data['char_id']);
			$sql = 'UPDATE %%characters SET %game_id = :game_id, %char_status = :char_status, %char_confirm_invite = :char_confirm_invite WHERE %char_id = :char_id ';
			$data['char_confirm_invite'] = md5($v['char_name'].microtime(true));
			$data['char_id'] = $v['char_id'];
			$result2[] = $db->noRes($sql, $data);

			$txt = tr($msg_invite['mail_contents'], true, null, 'mails');
			$txt = str_replace('{user_name}', $v['user_name'], $txt);
			$txt = str_replace('{cp_name}', $game['game_name'], $txt);
			$txt = str_replace('{char_name}', $v['char_name'], $txt);
			$txt = str_replace('{cp_mj}', Users::$name, $txt);
			$txt = str_replace('{link}', mkurl(array('val'=>64,'type'=>'tag','anchor'=>'Confirmer l\'invitation','params'=>array('confirm_campaign_invite', $data['char_confirm_invite']))), $txt);

			$dest = array(
				'mail' => $v['user_email'],
				'name' => $v['user_name'],
			);

			if (!send_mail($dest, $subj, $txt, $msg_invite['mail_id'])) {
				Session::setFlash('La partie a été créée, mais une erreur est survenue dans l\'envoi de l\'email de confirmation à l\'un des joueurs...', 'warning');
			}
		}
	}
	if ($result1 && !in_array(false, $result2, true)) {
		if ($send_chars) {
			redirect(array('val'=>60),'La partie a été correctement créée !<br />Les joueurs vont être avertis par mail et devront cliquer sur un lien dans ce mail pour participer à votre campagne. N\'hésitez pas à les prévenir, et à leur demander de vérifier (au cas où) leur boîte de courrier indésirable !', 'success noicon');
		} else {
			redirect(array('val'=>60),'La partie a été correctement créée !<br />Vous pouvez désormais inviter des joueurs à cette partie', 'success noicon');
		}
	}
} else {
	?>
	<form id="game_name" action="<?php echo mkurl($_PAGE['id']); ?>" method="post" class="form-horizontal">
	<fieldset>
	<div class="container">
		<h3><?php echo $_PAGE['anchor']; ?></h3>
		<div class="row-fluid">
			<div class="span4">
				<div class="control-group">
					<label class="control-label" for="game_name"><?php tr('Nom de la partie'); ?></label>
					<div class="controls"><input type="text" name="game_name" id="game_name" placeholder="<?php tr('Nom de la partie'); ?>" value="<?php echo isset($_POST['game_name']) ? $_POST['game_name'] : ''; ?>" /></div>
				</div>
				<div class="control-group">
					<label class="control-label" for="game_summary"><?php tr('Description'); ?></label>
					<div class="controls"><textarea name="game_summary" id="game_summary" placeholder="<?php tr('Description'); ?>"><?php echo isset($_POST['game_summary']) ? $_POST['game_summary'] : ''; ?></textarea></div>
				</div>
				<input type="submit" id="create_game" class="btn btn-block" value="<?php tr('Créer la partie'); ?>" />
			</div>
			<div class="span7 offset1">
				<h4><?php tr('Invitez les joueurs à participer à votre campagne !'); ?></h4>
				<p class="info"><?php tr('Sélectionnez les personnages des joueurs que vous voulez intégrer à votre campagne.')?></p>
				<div class="row-fluid">
				<?php
				foreach ($users as $user_id => $user) {
					$i = 0; ?>
					<h5><?php echo $user['name'], ' <small>', count($user['characters']), ' ', tr('personnage(s) disponible(s)', true), '</small>'; ?></h5>
					<div class="row-fluid char_list">
							<?php foreach ($user['characters'] as $k => $v) {
								if ($v['user_id'] == $user_id) {
									$btnchecked = isset($_POST['char_select']) ? (in_array($v['char_id'], $_POST['char_select']) ? ' btn-inverse' : '') : '';
									$inputval = isset($_POST['char_select']) ? (in_array($v['char_id'], $_POST['char_select']) ? '1' : '0') : '0';
									?>
								<a class="select_char span3 btn<?php echo $btnchecked; ?>" data-valid="char_select[<?php echo $v['char_id']; ?>]"><?php echo $v['char_name']; ?></a>
								<input type="hidden" name="char_select[<?php echo $v['char_id']; ?>]" value="<?php echo $inputval; ?>" />
							<?php
								$i++;
								if ($i % 3 === 0) { ?></div><div class="row-fluid"><?php }
							} }?>
					</div>
				<?php } ?>
				</div>
			</div>
		</div>
	</div><!-- /container -->
	</fieldset>
	</form>
	<?php
}
	buffWrite('css', <<<CSSFILE
	select[name*=char_select] {
		max-width: 100%;
	}
	select[name*=char_select] {
		max-width: 100%;
		min-width: 50%;
	}
	textarea, input {
		max-width: 100%;
	}
CSSFILE
);
	buffWrite('js', <<<JSFILE
$(document).ready(function(){
	$('.select_char').click(function(){
		$(this).toggleClass('btn-inverse').next('input[name="'+$(this).attr('data-valid')+'"]').val($(this).is('.btn-inverse') ? '1' : '0');
	});
});
JSFILE
);
