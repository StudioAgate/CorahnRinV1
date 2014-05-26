<?php

if (!empty($_POST)) {
    $sources = $_POST['source'];
    $trans = $_POST['trans'];

    if (count($sources) == count($trans)) {

        $nbTranslated = 0;
        foreach ($sources as $id => $source) {
            $nbTranslated += (int) Translate::write_words_en($source, $trans[$id]);
        }
        Session::setFlash($nbTranslated.' traductions effectuées !', 'success');
    } else {
        redirect(array('val'=>$_PAGE['id']), 'Erreur dans les données POST', 'error');
    }
}

foreach (Translate::$words_fr as $word) {
    if (!Translate::check($word['source'], Translate::$words_en)) {
        Translate::write_words_en($word['source'], $word['trans']);
    }
}


?>

<div class="container">

	<form id="managetrad" method="post" action="<?php echo mkurl(array('val'=>$_PAGE['id'])); ?>">

        <div class="w220 bl mid">
            <button class="btn btn-success" type="submit"><?php tr('Valider les traductions'); ?></button>
        </div>
        <div class="row-fluid">
            <div class="span6"><h4><?php tr('Source'); ?></h4></div>
            <div class="span6"><?php tr('Traduction'); ?></div>
        </div>
        <?php foreach (Translate::$words_en as $word) { ?>
            <div class="row-fluid">
                <div class="span6">
                    <?php echo $word['source']; ?>
                </div>
                <div class="span6">
                    <textarea class="hidden" name="source[]"><?php echo $word['source']; ?></textarea>
                    <textarea name="trans[]"><?php echo $word['trans']; ?></textarea>
                </div>
            </div>
            <hr />
        <?php } ?>
	</form>

</div><!-- /container -->

	<?php
	buffWrite('css', <<<CSSFILE
	textarea {
		width: 100%;
		max-width: 100%;
		min-width: 100%;
	}
	fieldset, .word_manage {
		-webkit-box-shadow: 0 0 10px #bbb;
		-moz-box-shadow: 0 0 10px #bbb;
		box-shadow: 0 0 10px #bbb;
		padding: 20px 15px;
		margin: 20px 0;
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
		-webkit-border-radius: 10px;
		-moz-border-radius: 10px;
		border-radius: 10px;
	}
CSSFILE
);
	buffWrite('js', <<<JSFILE

	$(document).ready(function(){
		$('#managetrad input').click(function(){
			var lang = $('<input type="hidden" />').attr('name', 'lang').val($(this).parents('fieldset').attr('id')),
				value = $('<input type="hidden" />').attr('name', 'value').val(this.id);
			$('#managetrad').prepend(lang).prepend(value);
			$('#managetrad').submit();
		});
		$('.manage_buttons button').click(function(){
			if ($(this).is('.btn-inverse')) {
				$(this).parents('.manage_buttons').find('.btn-inverse').removeClass('btn-inverse');
			} else {
				$(this).parents('.manage_buttons').find('.btn-inverse').removeClass('btn-inverse');
				$(this).addClass('btn-inverse');
			}
		});
		$('#send_management').click(function(){
			var texts = [];
			$('.word_manage button.btn-inverse').each(function(i, el) {
				texts[i] = {
					text: $(this).parents('.word_manage').find('textarea.word').val(),
					trad: $(this).parents('.word_manage').find('textarea.trad').val(),
					action: $(this).attr('data-modif')
				};
			});
			return false;
		});
	});

JSFILE
);