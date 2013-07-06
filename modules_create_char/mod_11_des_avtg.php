<?php

if (isset($p_stepval['avantages']) && isset($p_stepval['desavantages'])) {
	$avtgs = $p_stepval['avantages'];
	$desvs = $p_stepval['desavantages'];
	$exp = getXPFromAvtg($p_stepval, 100);
} else {
	$avtgs = $desvs = array();
	$exp = 100;
}

$totlist = $db->req('SELECT %avdesv_id, %avdesv_type, %avdesv_name, %avdesv_xp, %avdesv_desc, %avdesv_double FROM %%avdesv ORDER BY %avdesv_name ASC');//récupération de la liste des avantages

$revers = isset($_SESSION[$steps[7]['mod']]) ? $_SESSION[$steps[7]['mod']] : false;
if ($revers === false) {
	echo 'Les revers n\'ont pas été définis, merci de vous rendre à l\'étape correspondante.<br />',
	mkurl(array('params'=>7, 'type' => 'tag', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn"'));
	return;
}

if (in_array(9, (array)$revers)) { $pauvre = true; } else { $pauvre = false; }

$avtglist = $desvlist = array();

foreach($totlist as $key => $val) {//Formatage d'une liste d'avantages et une autre liste de désavantages
	if ($val['avdesv_type'] == 'avtg') {
		$avtglist[] = $val;
	} elseif ($val['avdesv_type'] == 'desv') {
		$desvlist[] = $val;
	}
}

?>
<div class="row-fluid">
	<div class="span12 notif noicon">
		<p><?php tr("Les avantages coûtent des points d'expérience. Ils sont <strong>facultatifs</strong>."); ?></p>
		<p><?php tr("Vous ne pouvez posséder qu'un maximum de 4 avantages, et 4 désavantages."); ?></p>
		<p><?php tr("N'oubliez pas de <strong>garder de l'expérience</strong> pour améliorer vos autres caractéristiques dans les étapes suivantes !"); ?></p>
		<p><?php tr("Les avantages ou désavantages <span style=\"text-decoration: underline;\">soulignés</span> peuvent être sélectionnés une deuxième fois. Vous ne pouvez sélectionner une 2e fois qu'un seul élément, mais vous pouvez doubler un avantage ET un désavantage."); ?></p>
		<p><strong><?php tr("Le gain d'xp avec des désavantages est limité à 80XP"); ?></strong></p>
	</div>
</div>
<div class="mt15"><span id="expaffix" class="well well-small"><span class="icon-star-empty"></span> <?php tr("Expérience"); ?> : <span id="exp" class="well well-small"><?php echo $exp; ?></span></span></div>
<div class="row-fluid">
	<div class="span6 avdesvlist pull-right">
		<h3><?php tr("Désavantages"); ?></h3>
		<p><?php
		$output = '';
		foreach($desvlist as $desv) {
			if ($pauvre === false || ($pauvre === true && strpos($desv['avdesv_name'], 'Pauvre') === false)) {
				$output .= '
				<div class="ib" style="padding: 0; margin: 2px;">
					<a class="btn btn-small desv';
					if (isset($desvs[$desv['avdesv_id']])) {
						if ($desvs[$desv['avdesv_id']] == 1) {
							$output .= ' btn-inverse';
						} elseif ($desvs[$desv['avdesv_id']] == 2) {
							$output .= ' btn-info';
						} elseif ($desvs[$desv['avdesv_id']] == 3) {
							$output .= ' btn-primary';
						}
					}
					$output .= '" data-stepid="'.$desv['avdesv_id'].'" ';
					$output .= 'data-exp="'.$desv['avdesv_xp'].'" ';
					$output .= 'data-double="'.$desv['avdesv_double'].'">'.tr($desv['avdesv_name'], true).'
					</a>
					<span class="avdesc">'.tr($desv['avdesv_desc'], true).'<br />'.tr('Gain', true).' : '.$desv['avdesv_xp'].'XP</span>
				</div>';
			}
		}
		echo $output;
		?></p>
	</div>
	<div class="span6 avdesvlist pull-left">
		<h3><?php tr("Avantages"); ?></h3>
		<?php
		$output = '';
		foreach($avtglist as $avtg) {
			if (
				strpos($avtg['avdesv_name'], 'Arts de combat') === false
				&& (
					$pauvre === false ||
						($pauvre === true && strpos($avtg['avdesv_name'], 'Aisance financ') === false)
				)
			) {
				$output .= '
				<div class="ib" style="padding: 0; margin: 2px;">
					<a class="btn btn-small avtg';
					if (isset($avtgs[$avtg['avdesv_id']])) {
						if ($avtgs[$avtg['avdesv_id']] == 1) {
							$output .= ' btn-inverse';
						} elseif ($avtgs[$avtg['avdesv_id']] == 2) {
							$output .= ' btn-info';
						} elseif ($avtgs[$avtg['avdesv_id']] == 3) {
							$output .= ' btn-primary';
						}
					}
					$output .= '" data-stepid="'.$avtg['avdesv_id'].'" ';
					$output .= 'data-exp="'.$avtg['avdesv_xp'].'" ';
					$output .= 'data-double="'.$avtg['avdesv_double'].'">'.tr($avtg['avdesv_name'], true).'
					</a>
					<span class="avdesc">'.tr($avtg['avdesv_desc'], true).'<br />'.tr('Coût', true).' : '.$avtg['avdesv_xp'].'XP</span>
				</div>';
			}
		}
		echo $output;
		?>
	</div>
</div><!--/.row-->
	<?php
	buffWrite('css', '
		#formgen div[class*="span"]:hover { cursor: default; }
		div[class^="span"] a.btn {
			margin: 0;
			min-width: 161px;
		}
		div[class^="span"] a.btn[data-double="1"] { text-decoration: underline; }
		h3 { text-align: center; }
		#infodesc {
			position: absolute;
			background: #eee;
			display: none;
			padding: 10px 10px 5px 10px;
			width: 250px;
		}
		.avtg:hover + span.avdesc,
		.desv:hover + span.avdesc {
			display: block;
		}
		.avdesc {
			color: #000;
			width: 170px;
			display: none;
			position: absolute;
			line-height: 1.3em;
			background: #eee;
			padding: 5px;
			border: solid 1px #ddd;
			font-size: 0.8em;
		}
	', $page_mod);
	buffWrite('js', <<<JSFILE
	$(document).ready(function() {
		$('#infodesc').hide().text('');
		$('a.btn.btn-small[data-stepid]').click(function() {
				var act = [ ],
				tclass='',
				i = 0,
				exp = 0,
				desvexp = 0,
				actavtg = [ ],
				values = [ ],
				xhr,
				actdesv = [ ];
			clearSelection();
			exp = 100;
			if ($(this).attr('class').match('avtg')) {
				tclass = 'avtg';
			} else {
				tclass = 'desv';
			}
			if ($(this).html().match('Allié')) {
				for (i = 1; i <= 3; i++) {
					if ($(this).attr('data-stepid') != i) { $('[data-stepid='+i+']').removeClass('btn-inverse'); }
				}
			}
			if ($(this).html().match('Aisance')) {
				for (i = 4; i <= 8; i++) {
					if ($(this).attr('data-stepid') != i) { $('[data-stepid='+i+']').removeClass('btn-inverse'); }
				}
			}

			if ($(this).attr('class').match('btn-inverse')) {
				$(this).removeClass('btn-inverse');
				if ($(this).attr('data-double') == '1') {
					if (tclass === 'avtg' && $(this).attr('data-stepid') != '50') {
						$('div[class*=span] p a.btn.btn-small.avtg[data-stepid]').removeClass('btn-info btn-primary');
					} else if (tclass === 'desv' && $(this).attr('data-stepid') != '50') {
						$('div[class*=span] p a.btn.btn-small.desv[data-stepid!=50]').removeClass('btn-info btn-primary');
					}
					$(this).addClass('btn-info');
				}
			} else if ($(this).attr('class').match('btn-primary')) {
				$(this).removeClass('btn-primary');
			} else if ($(this).attr('class').match('btn-info')) {
				$(this).removeClass('btn-info');
				if ($(this).attr('data-stepid') == '50') {
					$(this).addClass('btn-primary');
				}
			} else {
				$(this).addClass('btn-inverse');
			}
			$(this)
				.parents('.avdesvlist')
				.find('.btn.btn-small')
				.filter(function(){
					return $(this).attr('class').match(/inverse|info|primary/gi);
				})
				.not(this)
				.filter(':gt(2)')
				.removeClass('btn-info btn-inverse btn-primary');
			if ($(this).is('.btn-primary,.btn-info') && $(this).not('[data-stepid=50]')) {
			$(this)
				.parents('.avdesvlist')
				.find('.btn.btn-small')
				.filter(function(){
					return $(this).attr('class').match(/info|primary/gi);
				})
				.not(this)
				.not('[data-stepid=50]')
				.removeClass('btn-info btn-primary');
			}
			desvexp = 0;
			$('a.btn.btn-small[data-stepid]').each(function(){////Boucle de calcul des avantages
				var thisdesvexp = 0;
				if ($(this).attr('class').match(/inverse|info|primary/gi)) {
					if ($(this).attr('class').match('avtg')) { /// AVANTAGES
						actavtg[$(this).attr('data-stepid')] = 0;
						if ($(this).attr('class').match('btn-info')) {
							exp -= parseInt($(this).attr('data-exp')*1.5, 10);
						} else if ($(this).attr('class').match('btn-inverse')) {
							exp -= parseInt($(this).attr('data-exp'), 10);
						}
						if (exp < 0) {
							if ($(this).attr('class').match('btn-info')) {
								$(this).removeClass('btn-info');
								exp += parseInt($(this).attr('data-exp')*1.5, 10);
							} else if ($(this).attr('class').match('btn-inverse')) {
								$(this).removeClass('btn-inverse');
								exp += parseInt($(this).attr('data-exp'), 10);
							}
						} else {
							actavtg[$(this).attr('data-stepid')]++;
							if ($(this).attr('class').match('btn-info')) {
								actavtg[$(this).attr('data-stepid')]++;
							}
						}
					} else {								/// DÉSAVANTAGES
						actdesv[$(this).attr('data-stepid')] = 0;
						if ($(this).attr('class').match('btn-info')) {
							if ($(this).attr('data-stepid') == 50) {
								thisdesvexp += parseInt($(this).attr('data-exp')*2, 10);
							} else {
								thisdesvexp += parseInt($(this).attr('data-exp')*1.5, 10);
							}
						} else if ($(this).attr('class').match('btn-inverse')) {
							thisdesvexp += parseInt($(this).attr('data-exp'), 10);
						} else if ($(this).attr('class').match('btn-primary')) {
							thisdesvexp += parseInt($(this).attr('data-exp')*3, 10);
						}
						desvexp = parseInt(desvexp, 10) + parseInt(thisdesvexp, 10);
						if (desvexp > 80) {
							if ($(this).attr('class').match('btn-info')) {
								$(this).removeClass('btn-info');
								if ($(this).attr('data-stepid') == 20) {
									thisdesvexp -= parseInt($(this).attr('data-exp')*2, 10);
								} else {
									thisdesvexp -= parseInt($(this).attr('data-exp')*1.5, 10);
								}
							} else if ($(this).attr('class').match('btn-inverse')) {
								$(this).removeClass('btn-inverse');
								thisdesvexp -= parseInt($(this).attr('data-exp'), 10);
							} else if ($(this).attr('class').match('btn-primary')) {
								$(this).removeClass('btn-primary');
								thisdesvexp -= parseInt($(this).attr('data-exp')*3, 10);
							}
						} else {
							actdesv[$(this).attr('data-stepid')] = 1;
							if ($(this).attr('class').match('btn-info')) {
								actdesv[$(this).attr('data-stepid')] = 2;
							}
							if ($(this).attr('class').match('btn-primary')) {
								actdesv[$(this).attr('data-stepid')] = 3;
							}
						}
						exp = parseInt(exp, 10) + thisdesvexp;
					}
				}//End if ($(this).attr('class').match(/inverse|info|primary/gi)) {
			});
			act = {
				'avantages' : actavtg,
				'desavantages' : actdesv
			};
			$('#exp').text(exp);
			values = {
				'etape': {$page_step},
				'{$page_mod}': act
			};
			sendMaj(values, '{$p_action}');
		});
		if ($('#formgen .btn-inverse').length == 0) {
			values = {
				'etape': {$page_step},
				'{$page_mod}': {'avantages':[ 0 ],'desavantages':[ 0 ]}
			};
			sendMaj(values, '{$p_action}');
		}
	});
JSFILE
, $page_mod);