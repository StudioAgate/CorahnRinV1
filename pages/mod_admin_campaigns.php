<?php
$sql = 'SELECT
		%%games.%game_name,							%%games.%game_id,
		%%characters.%char_id,						%%characters.%char_name,
		%char_users.%user_name	as %user_name,		%char_users.%user_id	as %user_id,
		%mj_users.%user_name	as %mj_name,		%mj_users.%user_id		as %mj_id
	FROM %%games
	LEFT JOIN %%characters
		ON %%characters.%game_id = %%games.%game_id
	LEFT JOIN %%users as %mj_users
		ON %%games.%game_mj = %mj_users.%user_id
	LEFT JOIN %%users as %char_users
		ON %%characters.%user_id = %char_users.%user_id';
$t = $db->req($sql);
unset($sql);
$games = array();
if ($t) {
	foreach ($t as $v) {
		$games[$v['game_id']]['id'] = $v['game_id'];
		$games[$v['game_id']]['name'] = $v['game_name'];
		$games[$v['game_id']]['mj_id'] = $v['mj_id'];
		$games[$v['game_id']]['mj_name'] = $v['mj_name'];
		if ($v['char_id']) {
			$games[$v['game_id']]['characters'][$v['char_id']] = array(
				'id'=>$v['char_id'],
				'name'=>$v['char_name'],
				'user_id'=>$v['user_id'],
				'user_name'=>$v['user_name'],
			);
		}
	}
}
unset($t);
?>

<div class="container">

<p><?php echo count($games), ' ', tr('parties', true);?></p>

<ul>
<?php
foreach ($games as $game_id => $game) {
// pr($game);
	if (!isset($game['characters'])) { $game['characters'] = array(); }?><li>
		<?php echo $game['mj_name'], ' &ndash; <strong>',
		$game['name'],
		'</strong> '; ?><br />
		<!--<?php tr('Joueurs'); ?> :<br />-->
		<?php if ($game['characters']) { ?>
		<ul>
			<?php foreach ((array) $game['characters'] as $char_id => $char) { ?>
			<li><?php echo $char['user_name'],
			mkurl(array('val'=>47, 'params'=>array($char_id), 'anchor'=>$char['name'], 'type'=>'tag', 'attr'=>array('class'=>'btn btn-link')))
			//, ', ',
			//tr('possesseur', true),
			//' : ',
			; ?></li>
			<?php } ?>
		</ul>
		<?php } else { tr('Aucun joueur'); } ?>
	</li>
	<?php
}
?>
</ul>

</div><!-- /container -->

<?php
buffWrite('css', <<<CSSFILE

CSSFILE
);
buffWrite('js', <<<JSFILE

JSFILE
);