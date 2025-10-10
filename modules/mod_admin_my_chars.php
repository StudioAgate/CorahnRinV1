<?php

use App\Session;

if (P_LOGGED === false) {
	Session::setFlash('Vous n\'avez pas les droits pour accéder à cette page.', 'error');
	header('Location:'.mkurl(array('val'=>1)));
	exit;
}

$char_id = (int) ($_PAGE['request'][0] ?? 0);

?>
<div class="container">
	<?php
	if ($char_id) {
		$datas = array(
			'char_id' => $char_id,
		);
		load_module('char', 'module', $datas);
	} else {
		load_module('list', 'module');
	}
	?>
</div><!-- /container -->
<?php
buffWrite('css', /** @lang CSS */ <<<CSSFILE
	input, textarea {
		min-width: 90%;
		width: 90%;
		max-width: 90%;
		resize: vertical;
		max-height: 300px;
	}
	#possessions, #artefacts {
		min-width: 100%;
		width: 100%;
		max-width: 100%;
		resize: vertical;
	}
CSSFILE
);
buffWrite('js', /** @lang JavaScript */ <<<JSFILE
	$(document).ready(function(){
		$('#modify_tabs a').click(function (e) {
			e.preventDefault();
			$(this).tab('show');
		});
	});
JSFILE
);
