<?php

	$res = $db->req('SELECT %voie_id,%voie_name,%voie_shortname,%voie_desc FROM %%voies ORDER BY %voie_id ASC LIMIT 5');

	$voies = array();
	foreach($res as $key => $val) {
		foreach($val as $vkey => $vval) {
			if (is_int($vkey)) { unset($val[$vkey]); }
		}
		$voies[$val['voie_id']] = $val;
	}

	if (!$p_stepval) {
		$p_stepval = array();
	}
?>
<p class="notif"><?php tr("La somme totale des voies doit être égale à 15, et vous devez avoir au moins une voie avec un score de 1, ou de 5."); ?></p>
<div class="mt15"><span id="p_somme" class="well well-small"><span class="icon-plus"></span> <?php tr("Somme"); ?> : <span id="somme" class="well well-small"><?php echo $p_stepval ? '15' : '5'; ?></span></span></div>
	<div class="row">
		<?php
		$output = '';
			foreach($voies as $val) {
				$output .= '<div class="span12">
				<h3>'.tr($val['voie_name'], true).'</h3>
					<a class="ib btn moins">-</a>
					<span id="'.$val['voie_shortname'].'" class="btn btn-inverse disabled voie">'.($p_stepval ? $p_stepval[$val['voie_id']] : '1').'</span>
					<a class="ib btn plus">+</a>
					<p>'.tr($val['voie_desc'], true).'</p>
				</div>';
			}
		echo $output;
		?>
	</div>
	<?php
	buffWrite('css', '
		#formgen #p_somme * { z-index: 99999;vertical-align: baseline; }
		#formgen div.row div[class*="span"] { display: block; float: none; }
		[class*="span"] p {
			width: 60%;
			padding: 10px;
			display: inline-block;
		}
		#formgen div h3 span { font-size: 0.7em; }
		#formgen div.row div h3,
		#formgen div.row div a.ib,
		#formgen div.row [class*="span"] p,
		#formgen div.row div span {
			display: inline-block;
			vertical-align: middle;
		}
		#somme {
			margin: 0;
			padding: 4px 10px;
		}
		#formgen div.row div h3{
			width: 270px;
			padding-left: 10px;
		}', $page_mod);
	buffWrite('js', "
		$(document).ready(function() {
			var values = { }, xhr;
			values.etape = ".$page_step.";
			values['".$page_mod."'] = '';
			$('a.plus, a.moins').click(function() {
				var pm = $(this).attr('class').match('plus') ? 1 : -1,
					sommevoies = 0,
					act = 0,
					thisid = '';
				clearSelection();
				pm = parseInt(pm, 10);
				if (pm < 0) {
					act = parseInt($(this).next('span').text(), 10);
					thisid = $(this).next('span').attr('id');
				}
				if (pm > 0) {
					act = parseInt($(this).prev('span').text(), 10);
					thisid = $(this).prev('span').attr('id');
				}
				act = act + pm;
				if (act > 5) {
					act = 5;
				} else if (act < 1) {
					act = 1;
				}
				$('#' + thisid).text(act);
				sommevoies = 0;
				values['".$page_mod."'] = {

				};
				somevoies = 0;
				values['".$page_mod."'] = '';
				$('span.voie').each(function() {
					values['".$page_mod."'] += $(this).text() + ',';
					sommevoies += parseInt($(this).text(), 10);
				});
				while (sommevoies > 15) {
					act--;
					$('#' + thisid).text(act);
					sommevoies = 0;
					values['".$page_mod."'] = '';
					$('span.voie').each(function(index) {
						values['".$page_mod."'] += $(this).text();
						if (index < 4) { values['".$page_mod."'] += ','; }
						sommevoies += parseInt($(this).text(), 10);
					});
				}
				values['".$page_mod."'] = values['".$page_mod."'].replace(/,$/gi, '');
				if (values['".$page_mod."'] == '3,3,3,3,3') {
					act--;
					$('#' + thisid).text(act);
					sommevoies = 0;
					values['".$page_mod."'] = '';
					$('span.voie').each(function(index) {
						values['".$page_mod."'] += $(this).text();
						if (index < 4) { values['".$page_mod."'] += ','; }
						sommevoies += parseInt($(this).text(), 10);
					});
				}
				if (sommevoies == 15 && !values['".$page_mod."'].match(/1|5/gi)) {
					act--;
					$('#' + thisid).text(act);
					sommevoies = 0;
					values['".$page_mod."'] = '';
					$('span.voie').each(function(index) {
						values['".$page_mod."'] += $(this).text();
						if (index < 4) { values['".$page_mod."'] += ','; }
						sommevoies += parseInt($(this).text(), 10);
					});
				}
				$('#somme').text(sommevoies);
				if (sommevoies == 15) {
					sendMaj(values, '".$p_action."');
				} else {
					values['".$page_mod."'] = '';
					$('#gen_send').attr('href', '#').css('visibility', 'hidden');
					xhr = $.ajax({
						url : with_lang+'/ajax/aj_genmaj',
						type : 'post',
						data : values
					});
				}
				$('#' + thisid).text(act);
			});
		});
		", $page_mod);
