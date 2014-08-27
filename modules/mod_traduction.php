<?php

redirect(array('val'=>1), 'Module indisponible', 'error');

$propositions_en = Translate::get_propositions_en();
$words_fr = Translate::get_words_fr();
$words_en = Translate::get_words_en();

$list = array();//Préparation de la liste des mots
if (!isset($_SESSION['words'])) { $_SESSION['words'] = array(); }
foreach($words_fr as $word) {//Boucle sur les mots récupérés par la fonction tr()
	if (
		!isset($words_en[strip_tags(Translate::clean_word($word))])//Si le mot n'est pas dans les mots français
		&& !in_array(Translate::clean_word($word), $_SESSION['words'])//Et que le mot n'est pas dans les mots proposés en session
	) {
		$list[] = $word;
	}
}

?>
<div class="container">
	<h3>Propositions de traduction</h3>
	<div class="info">
		<p><?php tr('Ici vous pouvez proposer à la traduction tous les textes qui n\'ont pas encore été traduits en anglais.'); ?></p>
		<p><?php tr('Lorsque vous soumettez la traduction, celle-ci est examinée avant d\'être publiée.'); ?></p>
		<p><?php tr('Lorsque des traductions sont publiées, elles disparaissent de cette liste.'); ?></p>
		<p><?php tr('Lorsque vous avez soumis une traduction, vous ne pourrez plus le faire pendant cette session.'); ?></p>
	</div>
	<?php
		foreach($list as $i => $word) { ?>
			<div class="row-fluid wordproposition">
				<div class="span6"><label for="Word<?php echo $i; ?>"><?php echo htmlspecialchars($word); ?></label></div>
				<div class="span5"><textarea rows="2" cols="50" name="words[<?php echo $i; ?>]" id="Word<?php echo $i; ?>"></textarea></div>
				<div class="span1"><button data-wordid="Word<?php echo $i; ?>" class="submitpropos btn btn-small">Soumettre</button></div>
			</div>
			<?php
		}
	?>
</div><!-- /container -->

	<?php
	buffWrite('css', <<<CSSFILE
		textarea {
			width: 100%;
			max-width: 100%;
			min-width: 100%;
		}
		.wordproposition:before {
			display: block;
			content: "";
			width: 100%;
			height: 2px;
			-webkit-box-shadow: 0 0 10px #888;
			-moz-box-shadow: 0 0 10px #444;
			box-shadow: 0 0 10px #444;
			margin-bottom: 25px;
			margin-top: 15px;
		}
CSSFILE
);
	buffWrite('js', <<<JSFILE
	var ajax_xhr;
	$(document).ready(function(){
		$('.submitpropos').click(function(){
			var wordid = $(this).attr('data-wordid'),
				propos = $('label[for="'+wordid+'"]').text(),
				trad = $('#'+wordid).val(),
				__this = $(this);
			if (!trad) { return false; }
			if (confirm('Voulez-vous vraiment soumettre cette traduction :'+"\\n\\nPhrase initiale : \\n"+propos+"\\n\\nTraduction proposée : \\n"+trad)) {
				ajax_xhr = $.ajax({
					url: 'ajax/aj_tradpropos.php',
					type: 'post',
					data : {
						'propos' : propos,
						'trad' : trad
					},
					success : function(msg) {
						__this.parents('.wordproposition').slideUp(400, function(){ $(this).remove(); });
// 						$('#err').html(msg);
					}
				});
			}
		});
	});
JSFILE
);