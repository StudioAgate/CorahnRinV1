<?php

use App\EsterenChar;
use App\FileAndDir;
use App\Users;

$char_id = isset($_PAGE['request'][0]) ? (int) $_PAGE['request'][0] : 0;
if (!$char_id) {
	redirect(array('val'=>58), 'Vous devez sélectionner un personnage', 'warning');
}
$char = new Esterenchar($char_id, 'db');
if (!$char->id()) {
	redirect(array('val'=>58), 'Aucun personnage trouvé', 'warning');
}
if ($char->user_id() !== Users::$id) {
	redirect(array('val'=>58), 'Vous n\'avez pas le droit de consulter ce personnage', 'error');
}

if (!empty($_POST)) {
	load_module('_post', 'module', array('char'=>$char));
}

$modules_exp = array(
	'combat' => 'Combat',
	'domaines' => 'Domaines',
	'disciplines' => 'Disciplines',
);

?>


<div id="modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><span class="icon-remove"></span></button>
		<h3 class="modal-label"></h3>
	</div>
	<div class="modal-body"></div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php tr("Fermer"); ?></button>
	</div>
</div>

<div class="container">
	<form id="modify_char" class="form-horizontal" method="post" action="<?php echo mkurl(array('params' => $char_id)); ?>">
		<fieldset>
			<h3><?php tr($_PAGE['anchor']); ?></h3>
			<h4><?php echo tr('Personnage', true), ' : ', $char->name(); ?></h4>
			<h3 class="well well-small"><?php echo tr('Expérience ', true), ' : <span id="exp">', (int) $char->get('experience.reste'), '</span>'; ?></h3>
			<p><button id="send_datas" class="btn btn-inverse"><?php tr('Envoyer'); ?></button></p>
			<ul class="nav nav-tabs">
				<?php
					$i = 0;
					foreach ($modules_exp as $file => $title) {
						if (FileAndDir::fexists(ROOT.DS.'modules_'.$_PAGE['get'].DS.'mod_'.$file.'.php')) {
							?><li<?php echo $i === 0 ? ' class="active"' : ''; ?>><a data-toggle="tab" href="#<?php echo $file; ?>"><?php tr($title); ?></a></li><?php
							$i++;
						}
					}
				?>

			</ul>
			<div class="tab-content">
				<?php
					$i = 0;
					foreach ($modules_exp as $file => $title) {
						if (FileAndDir::fexists(ROOT.DS.'modules_'.$_PAGE['get'].DS.'mod_'.$file.'.php')) {
							?><div id="<?php echo $file; ?>" class="tab-pane fade<?php echo $i === 0 ? ' in active' : ''; ?>"><?php
								load_module($file, 'module', array('char'=>$char,'module_name'=>'spend_exp_'.$file), false);
							?></div>
				<?php $i++; }
					}
				?>
			</div>
		</fieldset>
	</form>
</div><!-- /container -->

<script type="text/javascript">
	var trad_title = "<?php tr("Nouvelles disciplines !"); ?>",
		trad_cnt = "<?php tr("Vous pouvez accéder à de nouvelles disciplines pour le domaine suivant :"); ?>",
		trad_send = "<?php tr('Envoyer'); ?>";
</script>
<?php
	buffWrite('css', /** @lang CSS */ <<<CSSFILE
	#modal { margin-top: -140px; }
	.modal-backdrop.fade.in { opacity: 0.2; }
	.progress:hover { cursor: pointer; }
CSSFILE
);
	buffWrite('js', /** @lang JavaScript */ <<<JSFILE

/**
 * Calcule les valeurs en fonction de l'objet cliqué
 * @param string element La valeur incluse dans data-stat
 * @param int increment 1 ou -1 en fonction de l'objet cliqué
 */
function change_val(element, increment) {
	var cost = 0,
		base_val = $(document).data('base_'+element) ? $(document).data('base_'+element) : 0,
		act_val = $(document).data(element) ? $(document).data(element) : 0,
		coef_width = 0,
		act_exp = $(document).data('exp');

	act_val += increment;//On applique l'incrémentation
	if (act_val < 0) { act_val = 0; }//Si inférieur à zéro, on met à zéro

	if (element.match('rapidite')) {
		if (act_val + base_val > 5) { act_val -= increment; }
		coef_width = 20;
		cost = (act_val * 5) + 5;
	} else if (element.match('defense')) {
		if (act_val + base_val > 10) { act_val -= increment; }
		coef_width = 10;
		cost = (act_val * 5) + 5;
	} else if (element.match('domaines') && !element.match('disciplines')) {
		if (act_val + base_val > 5) { act_val -= increment; }
		coef_width = 20;
		cost = 10;
	} else if (element.match('discipline')) {
		if (act_val + base_val < 6) {
			if (increment === 1 && base_val === 0) {
				act_val = 6;
			} else if (increment === -1) {
				act_val = 0;
			}
		}
		if (act_val + base_val > 10) { act_val -= increment; }
		coef_width = 100/15;
		if (act_val + base_val <= 10) {
			cost = 25;
		} else if (act_val + base_val >= 11) {
			cost = 40;
		}
	}
	if (act_exp - cost < 0 && increment === 1) {
		return;
	}

	if (element.match('domaines') && !element.match('disciplines') && act_val + base_val == 5) {
		var txt = trad_cnt + '<br /><strong>' + $('.progress[data-stat="'+element+'"]').prev('p').text() + '</strong>';
		$('#modal .modal-label').text(trad_title);
		$('#modal .modal-body').html(txt);
		$('#modal').modal('show');
	}
	$(document).data(element, act_val);
	$('input[name="'+element+'"]').val(act_val + base_val);
	$('.progress[data-stat="'+element+'"]').find('.bar-white').css('width', act_val * coef_width + '%');
	$('span.progress_text[data-stat="'+element+'"]').text(act_val + base_val);

	exp_calc();
}

/**
 * Calcule les valeurs en fonction de l'objet cliqué
 * @param string element La valeur incluse dans data-stat
 * @param int increment 1 ou -1 en fonction de l'objet cliqué
 */
function exp_calc() {
	var act_exp = $(document).data().base_exp,
		cost,
		datas = $(document).data();

	$('input[type="hidden"]').each(function(){
		var key = this.id;
		if (!key.match('exp') && ('base_'+key in datas) && datas[key]) {
			if (key.match('defense') || key.match('rapidite')) {
				var total = datas[key] + datas['base_'+key];
				start = datas['base_'+key]+1;
				for (var i = start; i <= total; i++) {
					act_exp -= (i * 5) + 5;
				}
			} else if (key.match('domaines') && !key.match('disciplines')) {
				var dom = key.replace(/[^0-9]/g, ''),
					total_val = datas[key] + datas['base_'+key];
					act_exp -= datas[key]*10;
				if (total_val < 5) {
					$('.domain_parent[data-domain='+dom+']').hide();
				} else if (total_val == 5) {
					$('.domain_parent[data-domain='+dom+']').show();
				}
			} else if (key.match('disciplines')) {
				var total = datas[key] + datas['base_'+key];
				start = datas['base_'+key] == 0 ? 6 : datas['base_'+key]+1;
				for (var i = start; i <= total; i++) {
					if (i >= 6 && i <= 10) {
						act_exp -= 25;
					} else if (i >= 11 && i <= 15) {
						act_exp -= 40;
					}
				}
			}
		}
	});

	$(document).data('exp', act_exp);
	$('#exp').text(act_exp);
}

$(document).ready(function(){
	$('#modify_char').submit(function(){
		if (confirm(trad_send + '?')) {
			$('form').prepend(
				$('<input>').attr({'name':'exp','type':'hidden'}).val($(document).data('exp'))
			);
			return true;
		}
	});
	$('#modal').on('hidden', function () {
		$(this).find('.modal-label').text('');
		$(this).find('.modal-body').text('');
	});
	$(document).data({
		exp: (isNaN(parseInt($('#exp').text(), 10)) ? 0 : parseInt($('#exp').text(), 10)),
		base_exp: (isNaN(parseInt($('#exp').text(), 10)) ? 0 : parseInt($('#exp').text(), 10)),
		'base_rapidite.amelioration' : parseInt($('input[name="rapidite.amelioration"]').val(), 10),
		'base_defense.amelioration' : parseInt($('input[name="defense.amelioration"]').val(), 10)
	});
	$('input[type="hidden"][name*="domaines"], input[type="hidden"][name*="disciplines"]').each(function(){
		if ($(this).val()) { $(document).data('base_'+this.id, parseInt($(this).val(), 10)); }
		if (this.name.match('disciplines')) {
			var dom = $(this).parents('.progress').attr('data-domain'),
				dombase = $(document).data('base_domaines.'+dom),
				domval = $(document).data('domaines.'+dom) ? $(document).data('domaines.'+dom) : 0;
			if (dombase + domval < 5) {
				$(this).parents('.domain_parent').hide();
			}
		}
	});
	$('div.progress').click(function(e) { if (this == e.target) { change_val($(this).attr('data-stat'), 1); } });
	$('div.progress div.bar, div.progress span.icon-minus').click(function(e){ if (this == e.target) { change_val($(this).parents('.progress').attr('data-stat'), -1); }});
	$('div.progress span.icon-plus, div.progress span.progress_text').click(function(e){ if (this == e.target) { change_val($(this).parents('.progress').attr('data-stat'), 1); }});
});

JSFILE
);
