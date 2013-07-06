<?php

$lang = isset($_POST['lang']) ? $_POST['lang'] : false;
$file = isset($_POST['value']) ? $_POST['value'] : false;

if ($lang && $file) {
	$manage = true;
	$lang_ok = array('en'=>1,'fr'=>1);
	$file_ok = array('words_en'=>1,'words_fr'=>1,'propositions_en'=>1);
	if (isset($lang_ok[$lang]) && isset($file_ok[$file])) {
		$method = 'get_'.$file;
		if (method_exists(new Translate(), $method)) {
			$contents = Translate::$method();
		}
	} else {
		tr('Erreur');
	}
} else {
	$manage = false;
}

pr($_POST);

?>

<div class="container">

	<form id="managetrad" method="post" action="<?php echo BASE_URL.'/'.mkurl(array('val'=>$_PAGE['id'])); ?>">
		<div class="row-fluid">
			<div class="span4">
				<fieldset id="fr">
					<legend><?php tr('Français'); ?></legend>

					<input type="button" id="words_fr" class="btn btn-large" value="Words" />
				</fieldset>
			</div>
			<div class="span4">
				<fieldset id="en">
					<legend><?php tr('Anglais'); ?></legend>
					<input type="button" id="propositions_en" class="btn btn-large" value="Propositions" />
					<input type="button" id="words_en" class="btn btn-large" value="Words" />
				</fieldset>
			</div>
			<div class="span4">
					<?php if (isset($contents) && is_array($contents)) {?>
				<fieldset>
					<legend><?php tr('Valider les modifications'); ?></legend>
					<button class="btn btn-block btn-large btn-inverse" id="send_management"><?php tr('Envoyer'); ?></button>
				</fieldset>
					<?php } ?>
			</div>
		</div>
	</form>

	<?php
		if (isset($contents) && is_array($contents)) {
			$i = 0;
			?>
			<?php
			foreach($contents as $k => $word) {
				if (is_numeric($k)) {
				$i = $k; ?>
				<div class="row-fluid word_manage">
					<div class="span9"><textarea rows="2" cols="50" name="words[<?php echo $i; ?>]" id="Word<?php echo $i; ?>"><?php echo $word;?></textarea></div>
					<div class="span3">
						<div class="btn-group manage_buttons">
							<button class="btn" data-modif="update"><?php tr('Modifier'); ?></button>
							<button class="btn" data-modif="delete"><?php tr('Supprimer'); ?></button>
						</div>
					</div>
				</div>
				<?php
				} elseif (is_string($k)) {
					$trad = $word;
					$word = $k;
				?>
				<div class="row-fluid word_manage">
					<div class="span5"><textarea rows="2" cols="50" class="word"><?php echo $word;?></textarea></div>
					<div class="span5"><textarea rows="2" cols="50" class="trad"><?php echo $trad;?></textarea></div>
					<div class="span1">
						<div class="btn-group manage_buttons">
							<button class="btn" data-modif="update"><?php tr('Modifier'); ?></button>
							<button class="btn" data-modif="delete"><?php tr('Supprimer'); ?></button>
						</div>
					</div>
				</div>
				<?php
					$i++;
				}
			}
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