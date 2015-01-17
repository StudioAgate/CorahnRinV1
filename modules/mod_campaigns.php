<?php

$sendmail = isset($_PAGE['request']['sendmail']) ? (int) $_PAGE['request']['sendmail'] : 0;

$game_mj = isset($_PAGE['request'][0]) ? (int) $_PAGE['request'][0] : 0;
$char_id = isset($_PAGE['request'][1]) ? (int) $_PAGE['request'][1] : 0;

if ($game_mj && $char_id) {
	load_module('gift', 'module', array('game_mj'=>$game_mj, 'char_id'=>$char_id));
} elseif ($game_mj && !$char_id) {
	load_module('gm', 'module', array('game_mj' => $game_mj));
} elseif (!$game_mj && !$char_id) {
	load_module('list', 'module');
}
unset($sendmail, $game_mj, $game_player, $char_id);

buffWrite('css', <<<CSSFILE
	.give_exp { margin-right: 5px; }
CSSFILE
);

buffWrite('js', <<<JSFILE
\$(document).ready(function(){
	$('.select_char').click(function(){
		$(this).toggleClass('btn-inverse').next('input[name="'+$(this).attr('data-valid')+'"]').val($(this).is('.btn-inverse') ? '1' : '0');
	});
});
JSFILE
);