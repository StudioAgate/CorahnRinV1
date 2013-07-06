<?php

$game_id = isset($_PAGE['request'][0]) ? (int) $_PAGE['request'][0] : 0;

if (!$game_id) {
	Session::setFlash('Une partie doit être sélectionnée', 'error');
	header('Location:'.mkurl());
	exit;
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

if (!empty($_POST)) {
	load_module('gift_post', 'module');
}

if (!isset($char_id)) { return; }

$char = $db->row('SELECT * FROM
		%%characters WHERE %char_id = :char_id && %game_id = :game_id && (%char_status = :pj || %char_status = :pnj)', array('char_id'=>$char_id,'game_id'=>$game_id,'pj'=>1,'pnj'=>2));

if (!$char) {
	Session::setFlash('Vous ne pouvez pas donner de récompense à ce personnage.', 'error');
	header('Location:'.mkurl(array('params'=>array(0=>$game_id))));
	exit;
}

unset($char);
$char = new EsterenChar($char_id, 'db');

$modules_list = array(
	'experience' => 'Expérience',
	'armes' => 'Armes',
	'armures' => 'Armures',
);

?>
<div class="container">
<form action="<?php echo mkurl(array('params'=>array($game_id,$char_id))); ?>" method="post" class="form-horizontal">
<fieldset>
	<input type="hidden" name="game_id" value="<?php echo $game_id; ?>" />
	<input type="hidden" name="char_id" value="<?php echo $char_id; ?>" />
	<h3><?php tr('Offrir des récompenses à un personnage joueur'); ?></h3>

	<ul class="nav nav-tabs" id="modify_tabs">
	<?php
		$i = 0; foreach($modules_list as $file => $title) {
			$file_to_load = ROOT.DS.'modules_'.$_PAGE['get'].DS.'mod_gift_'.$file.'.php';
			if (FileAndDir::fexists($file_to_load)) { ?>
			<li<?php echo $i === 0 ? ' class="active"' : ''; ?>><a data-toggle="tab" href="#<?php echo $file; ?>"><?php tr($title); ?></a></li>
			<?php $i++; }
		}
	?>
	</ul>
	<div class="tab-content" id="myTabContent">
		<?php $i = 0; foreach($modules_list as $file => $title) {
			$file_to_load = ROOT.DS.'modules_'.$_PAGE['get'].DS.'mod_gift_'.$file.'.php';
			if (FileAndDir::fexists($file_to_load)) {?>
			<div id="<?php echo $file; ?>" class="tab-pane fade<?php echo $i === 0 ? ' in active' : ''; ?>"><?php require $file_to_load; ?></div>
			<?php $i++; }
		} ?>
	</div>
	<button id="send" class="btn btn-inverse"><?php tr('Envoyer'); ?></button>
</fieldset>
</form>
</div>

<script type="text/javascript">var valid_txt = '<?php tr('Valider les récompenses envoyées au personnage ?'); ?>';</script>

<?php
$_PAGE['more_js'][] = BASE_URL.'/js/pages/pg_'.$_PAGE['get'].'_gift.js';

buffWrite('js', <<<JSFILE
	function remove_chars() {
		var text = $('#exp').val().replace(/[^0-9]+/gi, '');
		//alert(text);
		$('#exp_slider').slider('option', 'value', Number(text));
		$('#exp').val(text);
	}
	$(document).ready(function(){
		$('form').submit(function(){
			return confirm(valid_txt);
		});
		$('#exp_slider').slider({
			range: 'min',
			value: 0,
			min: 0,
			max: 100,
			slide: function( event, ui ) {
				$('#exp').val(ui.value);
			}
		});
		$('.change_value.btn').click(function(){
			$(this).toggleClass('btn-inverse')
				.next('input[type="hidden"]').val($(this).is('.btn-inverse') ? '1' : '0');
		});
		$('#exp').on('click blur focus keydown keyup', function () { remove_chars(); });
	});
JSFILE
, $_PAGE['get'].'_gift');