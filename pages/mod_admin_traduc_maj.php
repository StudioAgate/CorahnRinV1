<?php

$propositions_en = Translate::get_propositions_en();
$words_en = Translate::get_words_en();

$list = array();//Préparation de la liste des mots
foreach($propositions_en as $word => $trad) {//Boucle sur les mots récupérés par la fonction tr()
	if (!isset($words_en[Translate::clean_word($word)])) {
		$list[$word] = $trad;
	}
}
asort($list);
ksort($list);
?>
<div class="container">
	<h3>Propositions de traduction</h3>
	<?php
		$i = 0;
		foreach($list as $word => $trans) {
			$word = trim(preg_replace('/\s\s+/sUu', ' ', $word));
			$word = str_replace("\n", "", $word);
			$trans = trim(preg_replace('/\s\s+/sUu', ' ', $trans));
			$trans = preg_replace('#\n#sUu', "", $trans);
			$trans = str_replace('[b]', '<strong>', $trans);
			$trans = str_replace('[/b]', '</strong>', $trans);
			$trans = str_replace('[u]', '<span class="underline">', $trans);
			$trans = str_replace('[/u]', '</span>', $trans);
			?>
			<div class="row-fluid wordproposition">
				<div class="span6"><label for="Word<?php echo $i; ?>"><?php echo htmlspecialchars($word); ?></label></div>
				<div class="span5"><textarea rows="2" cols="50" name="words[<?php echo $i; ?>]" id="Word<?php echo $i; ?>"><?php echo $trans; ?></textarea></div>
				<div class="span1">
					<button data-wordid="Word<?php echo $i; ?>" data-yesno="yes" class="submitpropos btn btn-block btn-primary btn-small">Oui</button>
					<button data-wordid="Word<?php echo $i; ?>" data-yesno="no" class="submitpropos btn btn-block btn-danger btn-small">Non</button>
				</div>
			</div>
			<?php
			$i++;
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
		.submitpropos + .submitpropos { margin-top: 5px; }
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
			if ($(this).attr('data-yesno') == 'no') {
				__this.parents('.wordproposition').slideUp(400, function(){ $(this).remove(); });
				return false;
			}
// 			if (confirm('Voulez-vous vraiment soumettre cette traduction :'+"\\n\\nPhrase initiale : \\n"+propos+"\\n\\nTraduction proposée : \\n"+trad)) {
				ajax_xhr = $.ajax({
					url: 'ajax/aj_tradpropos.php',
					type: 'post',
					data : {
						'maj_propos' : propos,
						'maj_trad' : trad
					},
					success : function(msg) {
						__this.parents('.wordproposition').slideUp(200, function(){ $(this).delay(10).remove(); });
						$('#err').html(msg);
					}
				});
// 			}
		});
	});
JSFILE
);