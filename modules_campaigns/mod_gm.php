<?php

use App\bdd;
use App\EsterenChar;
use App\Session;
use App\Users;

/** @var bdd $db */

$game_id = (int) ($_PAGE['request'][1] ?? 0);
$submodule = $_PAGE['request'][2] ?? 'list';
$char_id = (int) ($_PAGE['request'][3] ?? 0);

if (!$game_id) {
	Session::setFlash('Une partie doit être sélectionnée', 'error');
	return;
}

$game = $db->row('SELECT %game_name,%game_id,%game_mj FROM %%games WHERE %game_id = ?', $game_id);

if (!$game) {
	Session::setFlash('Aucune partie trouvée', 'warning');
	header('Location:'.mkurl());
	exit;
}

if ($game['game_mj'] != Users::$id) {
	Session::setFlash('Vous n\'êtes pas le maître de jeu de cette partie', 'error');
	header('Location:'.mkurl());
	exit;
}

if ($submodule === 'invite_char') {
	load_module('gm_invite_char','module',array('game'=>$game));
	return;
} elseif ($submodule === 'delete_char') {
    // Retrait du personnage de la campagne en cours
    $sql = 'UPDATE %%characters SET %game_id = :game_id, %char_status = :char_status WHERE %char_id = :char_id ';
    $datas['game_id'] = null;
    $datas['char_status'] = 0;
    $datas['char_id'] = $char_id;
    $db->noRes($sql, $datas);
    Session::setFlash('Le personnage a été correctement retiré de la campagne.');
    redirect(array('params' => array('gm', $game_id)));
} elseif ($submodule === 'sendmail') {
    $result = $db->row('
            SELECT %c.%char_name, %c.%char_confirm_invite,
                %g.%game_id, %g.%game_name,
                %uMj.%user_name %gm_name,
                %u.%user_name, %u.%user_email
            FROM %%characters %c
            LEFT JOIN %%games %g ON %c.%game_id = %g.%game_id
            LEFT JOIN %%users %u ON %c.%user_id = %u.%user_id
            LEFT JOIN %%users %uMj ON %g.%game_mj = %u.%user_id
            WHERE %c.%char_id = :char_id
              AND %c.%char_status = :status
              AND %g.%game_id = :game_id
            ', array('char_id' => $char_id, 'status' => 0, 'game_id' => $game_id));

    if (!$result) {
        Session::setFlash('Erreur : personnage non trouvé, ou le personnage est déjà inscrit à une campagne.');
        redirect(array('params' => array(0=>$game_id)));
    }

    $msg_invite = $db->row('SELECT %mail_id, %mail_contents, %mail_subject FROM %%mails WHERE %mail_code = ?', array('campaign_invite'));
    $subj = tr($msg_invite['mail_subject'], true, null, 'mails');
    $txt = tr($msg_invite['mail_contents'], true, array(
        '{user_name}' => $result['user_name'],
        '{cp_name}' => $result['game_name'],
        '{char_name}' => $result['char_name'],
        '{cp_mj}' => $result['gm_name'],
        '{link}' => mkurl(array('val'=>64,'type'=>'tag','anchor'=>'Confirmer l\'invitation','trans'=>true,'params'=>array('confirm_campaign_invite', $result['char_confirm_invite']))),
    ), 'mails');

    $dest = array(
        'mail' => $result['user_email'],
        'name' => $result['user_name'],
    );

    try {
        send_mail($dest, $subj, $txt, $msg_invite['mail_id']);
        Session::setFlash('Le mail a bien été renvoyé à l\'utilisateur.');
    } catch (Exception $e) {
        Session::setFlash('Une erreur est survenue dans l\'envoi de l\'email de confirmation au joueur...', 'warning');
    }
    redirect(array('params' => array('gm', $game_id)));
}

$sql = 'SELECT
		%%characters.%char_name, %%characters.%char_job, %%characters.%char_status, %%characters.%char_id,
		%%jobs.%job_name, %%users.%user_name
	FROM %%characters
	LEFT JOIN %%jobs
		ON %%jobs.%job_id = %%characters.%char_job
	LEFT JOIN %%users
		ON %%users.%user_id = %%characters.%user_id
	WHERE %%characters.%game_id = ?';
$chars = $db->req($sql, $game['game_id']);

?>

<div class="container">

	<h2><?php echo $game['game_name']; ?></h2>

	<?php
		if (!$chars) {
			$chars = [];
			?>
			<p class="warning"><?php tr('Aucun personnage'); ?></p>
			<?php
		}
		?>

	<?php echo mkurl(array(
		'type'=>'tag',
		'attr'=>array('class'=>'btn btn-inverse'),
		'anchor'=>'Inviter des joueurs',
		'params'=>array('gm', $game_id, 'invite_char'),
	));?>

	<table class="table table-condensed table-striped table-hover">
		<tr>
			<th>#</th>
			<th><?php tr('Joueur'); ?></th>
			<th><?php tr('Personnage'); ?></th>
			<th><?php tr('Métier'); ?></th>
			<th><?php tr('Expérience'); ?></th>
			<th><?php tr('Statut'); ?></th>
			<th><?php tr('Actions'); ?></th>
		</tr>
		<?php foreach ($chars as $char) {
			$char = (array) $char;
			foreach($char as $k => $v) { if (is_numeric($v)) { $char[$k] = (int) $v; } }
			$character = new Esterenchar($char['char_id'], 'db'); ?>
			<tr>
				<td><?php echo $char['char_id']; ?></td>
				<td><?php echo $char['user_name']; ?></td>
				<td><strong><?php echo $char['char_name']; ?></strong></td>
				<td><?php
					if (!$char['job_name']) { echo tr('Métier personnalisé', true), ' : ', $char['char_job']; }
					else { echo $char['job_name']; }
				?></td>
				<td><?php
					echo $character->get('experience.reste'), '/', $character->get('experience.total');
				?></td>
				<td><?php
					if ($char['char_status'] === 0) {
						tr('Invitation envoyée...');
					} elseif ($char['char_status'] === 1) {
						tr('Invitation acceptée : PJ');
					} elseif ($char['char_status'] === 2) {
						tr('PNJ');
					} elseif ($char['char_status'] === 3) {
						tr('Mort...');
					}
				?></td>
				<td style="width: 220px"><?php
                    if ($char['char_status'] == 0) {
                        echo mkurl(array(
                            'type' => 'tag',
                            'anchor' => 'Renvoyer l\'invitation',
                            'trans' => true,
                            'attr' => array(
                                'title' => tr('Renvoyer l\'invitation', true),
                                'class' => 'btn btn-mini btn-block give_exp btn-info',
                                'style' => 'color: white;',
                            ),
                            'params' => array('gm', $game_id, 'sendmail', $char['char_id']))
                        );
                    } elseif ($char['char_status'] == 1 || $char['char_status'] == 2) {
                        echo mkurl(array(
                            'type' => 'tag',
                            'anchor' => 'Récompenses',
                            'trans' => true,
                            'attr' => array(
                                'title' => tr('Ajouter une récompense', true),
                                'class' => 'btn btn-mini btn-block give_exp',
                            ),
                            'params' => array('rewards', $game_id, $char['char_id']))
                        );
                        echo mkurl(array(
                            'val' => 47,
                            'type' => 'tag',
                            'anchor' => 'Voir le personnage',
                            'trans' => true,
                            'attr' => array(
                                'title' => tr('Voir le personnage', true),
                                'class' => 'btn btn-mini btn-block',
                            ),
                            'params' => array($char['char_id']))
                        );
                        echo mkurl(array(
                            'type' => 'tag',
                            'anchor' => 'Retirer de la campagne',
                            'trans' => true,
                            'attr' => array(
                                'title' => tr('Retirer de la campagne', true),
                                'class' => 'btn btn-mini btn-block give_exp btn-danger',
                                'style' => 'color: white;',
                                'onclick' => 'return confirm(\''.tr('Retirer le personnage de la campagne ?', true).'\');',
                            ),
                            'params' => array('gm', $game_id, 'delete_char', $char['char_id']))
                        );
                    }
				?></td>
			</tr>
		<?php } ?>

	</table>
</div>
