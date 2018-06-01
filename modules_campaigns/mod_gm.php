<?php

use App\EsterenChar;
use App\Session;
use App\Users;

$game_id = isset($_PAGE['request'][0]) ? (int) $_PAGE['request'][0] : 0;

$invite = isset($_PAGE['request'][1]) && $_PAGE['request'][1] === 'invite_char' ? $_PAGE['request'][1] : '';

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

if ($invite) {
	load_module('gm_invite_char','module',array('game_id'=>$game_id, 'game'=>$game));
	return;
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
			$chars = array();
			?>
			<p class="warning"><?php tr('Aucun personnage'); ?></p>
			<?php
		}
		?>

	<?php echo mkurl(array(
		'type'=>'tag',
		'attr'=>array('class'=>'btn btn-inverse'),
		'anchor'=>'Inviter des joueurs',
		'params'=>array($game_id, 'invite_char'),
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
                            'params' => array(0=>$game_id,1=>$char['char_id'],'sendmail'))
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
                            'params' => array(0=>$game_id,1=>$char['char_id']))
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
                            'params' => array(0=>$game_id,1=>$char['char_id'],'delete'))
                        );
                    }
				?></td>
			</tr>
		<?php } ?>

	</table>
</div>
