<?php

$games_mj = $db->req('
	SELECT %%games.%game_name, %%games.%game_id, %%characters.%char_id, %%characters.%char_name
	FROM %%games
	LEFT JOIN %%characters
		ON %%characters.%game_id = %%games.%game_id
	WHERE %game_mj = ?', Users::$id);

if (!$games_mj) {
	$games_mj = array();
}
$games = array();
foreach($games_mj as $v) {
	$games[$v['game_id']]['name'] = $v['game_name'];
	if ($v['char_id'] && $v['char_id'] !== null) {
		$games[$v['game_id']]['characters'][$v['char_id']] = $v['char_name'];
	}
}
unset($games_mj);

$games_player = $db->req('SELECT
	%%characters.%char_name,%%characters.%char_id,
	%%games.%game_id, %%games.%game_name
	FROM %%games
	LEFT JOIN %%characters
		ON %%characters.%game_id = %%games.%game_id
	WHERE %%characters.%user_id = ?', Users::$id);

?>

<div class="container">

	<div class="content">
		<p><?php
			tr('Aucune campagne ? Créez la votre !');
			echo '&nbsp;', mkurl(array('val'=>61, 'type' => 'tag', 'anchor' => null, 'attr' => 'class="btn"'));
		?></p>
	</div>

	<div class="content">
		<h4><?php tr('En tant que MJ'); ?></h4>
		<?php
		if (!$games) {
			echo '<p>', tr('Aucune partie', true), '</p>';
		} else {
			foreach ($games as $id => $v) {
				echo mkurl(array('type' => 'tag', 'anchor' => $v['name'], 'attr' => 'class="btn btn-link"', 'params' => array(0=>$id)));
				?><p><?php tr('Joueurs'); ?> : <?php
				if (isset($v['characters']) && count($v['characters'])) {
					foreach ($v['characters'] as $char_id => $char_name) {
						if ($char_id) {
							echo mkurl(array('val'=>47, 'anchor'=>$char_name, 'type'=>'tag','attr'=>array('class'=>'btn btn-link'), 'params'=>$char_id));
						}
					}
				} else {
					tr('Aucun personnage');
				}
				?></p><?php
			}
		}
		unset($games_mj, $k, $v); ?>
	</div>

	<div class="content">
		<h4><?php tr('En tant que joueur'); ?></h4>
		<?php
		if (!$games_player) {
			echo '<p>', tr('Aucune partie', true), '</p>';
		} else {
			foreach ($games_player as $k => $v) {
				$anchor = $v['game_name'].' &ndash; Personnage : '.$v['char_name'];
				echo mkurl(array('val'=>47, 'type' => 'tag', 'anchor' => $anchor, 'attr' => 'class="btn btn-block btn-link txtleft"', 'params' => array(0=>$v['char_id'])));
				//echo mkurl(array('val'=>$_PAGE['id'], 'type' => 'tag', 'anchor' => $anchor, 'attr' => 'class="btn btn-link"', 'params' => array('player'=>$v['game_id'])));
			}
		}
		unset($games_player, $k, $v, $anchor); ?>
	</div>

</div><!-- /container -->