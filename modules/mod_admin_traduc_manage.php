<?php

use App\Translate;

$changed = false;
foreach (Translate::$words_fr as $domain => $words) {
    foreach ($words as $word) {
        if (!isset(Translate::$words_en[$domain])) { Translate::$words_en[$domain] = array(); }
        if (!Translate::check($word['source'], Translate::$words_en[$domain])) {
            $changed = true;
            Translate::write_words_en($word['source'], $word['trans'], $domain);
        }
    }
}
if ($changed) {
    Translate::translate_writewords();
}

if (!empty($_POST)) {
    $source = $_POST['source'];
    $trans = $_POST['trans'];
    $domain = $_POST['domain'];

    $_PAGE['layout'] = 'ajax';

    echo Translate::write_words_en($source, $trans, $domain);
    Translate::translate_writewords();
    return;
}

$url = mkurl(array('val'=>$_PAGE['id']));

$words_en = Translate::get_words_en();

$txt_dom = tr('Domaine de traduction', true);
$txt_trad = tr('Traduire', true);

?>

<div class="container">

    <?php foreach ($words_en as $domain => $words) { ?>
        <h3 class="wrap_list"><a href="#" class="btn btn-block btn-link"><?php echo $txt_dom, ' : ', $domain; ?> (<?php echo count($words); ?>)</a></h3>
        <div class="list">
        <?php foreach ($words as $word) {?>
            <div class="row-fluid">
                <form action="<?php echo $url; ?>">
                    <fieldset>
                    <div class="span5">
                        <?php echo $word['source']; ?>
                        <textarea class="hidden" name="source"><?php echo $word['source']; ?></textarea>
                    </div>
                    <div class="span5">
                        <input type="hidden" name="domain" value="<?php echo $domain; ?>" />
                        <div class="control-group">
                            <div class="controls">
                                <textarea name="trans"><?php echo $word['trans']; ?></textarea>
                            </div>
                        </div>
                    </div>
                        <div class="span2">
                            <button type="button" class="btn btn-block translate"><?php echo $txt_trad; ?></button>
                        </div>
                    </fieldset>
                </form>
            </div>
        <?php } ?>
        </div>
        <hr />
    <?php } ?>

</div><!-- /container -->

	<?php
	buffWrite('css', <<<CSSFILE
	.list {
	    display: none;
    }
	textarea {
		width: 90%;
		min-width: 90%;
		max-width: 100%;
	}
	fieldset {
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
		$('.wrap_list').on('click',function(){
		    $(this).next('.list').slideToggle(400);
		});
		$('button.translate').off('click').on('click', function(){
            var form = $(this).parents('form');
		    var datas = form.serialize();
            var _this = $(this);
		    var ajaxDatas = {
                url: form.attr('action'),
                type: 'post',
                data: datas,
                success: function(response){
                    form.find('.control-group').addClass(response == 1 ? 'success' : 'warning');
                },
                error: function(){
                    form.find('.control-group').addClass('error');
                }
            };
            $.ajax(ajaxDatas);
		});
	});

JSFILE
);
