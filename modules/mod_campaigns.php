<?php

$submodule = $_PAGE['request'][0] ?? 'list';

if ($submodule === 'send_invitation') {
	load_module('send_invitation', 'module');
} elseif ($submodule === 'rewards') {
	load_module('gift', 'module');
} elseif ($submodule === 'gm') {
	load_module('gm', 'module');
} elseif ($submodule === 'list') {
	load_module('list', 'module');
} else {
    redirect(['val' => 60]);
}
unset($submodule);

buffWrite('css', /** @lang CSS */ <<<CSSFILE
	.give_exp { margin-right: 5px; }
CSSFILE
);

buffWrite('js', /** @lang JavaScript */ <<<JSFILE
\$(document).ready(function(){
	$('.select_char').click(function(){
		$(this).toggleClass('btn-inverse').next('input[name="'+$(this).attr('data-valid')+'"]').val($(this).is('.btn-inverse') ? '1' : '0');
	});
});
JSFILE
);
