<?php

	$geo = isset($_SESSION[$steps[4]['mod']]) ? $_SESSION[$steps[4]['mod']] : false;
	if ($geo === false) {
		tr("Le lieu de résidence géographique n'a pas été défini, merci de vous rendre à l'étape correspondante", true);
		echo mkurl(array('params'=>4, 'type' => 'TAG', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn bl"'));
		return;
	}
	if ($geo === 'Urbain') { $geo = 11; }
	elseif ($geo === 'Rural') { $geo = 5; }

	$primsec = isset($_SESSION[$steps[13]['mod']]) ? $_SESSION[$steps[13]['mod']] : false;
	if ($primsec === false) {
		tr("Les domaines primaires et secondaires n\'ont pas été définis, merci de vous rendre à l\'étape correspondante.", true);
		mkurl(array('params'=>13, 'type' => 'TAG', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn"'));
		return;
	}
	$primsec = array_map('intval', $primsec);

	$ost = $primsec['ost']; unset($primsec['ost']);
	if (isset($primsec['lettre'])) { $lettre = $primsec['lettre']; }

	$classe = isset($_SESSION[$steps[5]['mod']]) ? $_SESSION[$steps[5]['mod']] : false;
	if ($classe === false) {
		tr("La classe sociale n\'a pas été définie, merci de vous rendre à l\'étape correspondante.", true);
		echo mkurl(array('params'=>5, 'type' => 'TAG', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn"'));
		return;
	}
	unset($classe['classe']);
	$classe = array_map('intval', $classe);

	$bonus = 0;
	if (isset($primsec[$classe['dom1']])) {
		if ($primsec[$classe['dom1']] == 5) {
			$bonus ++;
		} else {
			$primsec[$classe['dom1']] ++;
		}
	} else {
		$primsec[$classe['dom1']] = 1;
	}
	if (isset($primsec[$classe['dom2']])) {
		if ($primsec[$classe['dom2']] == 5) {
			$bonus ++;
		} else {
			$primsec[$classe['dom2']] ++;
		}
	} else {
		$primsec[$classe['dom2']] = 1;
	}

	//Gestion de du lieu de résidence géographique
	if (isset($primsec[$geo]) && $primsec[$geo] == 5) { $bonus ++; } else { $primsec[$geo] = isset($primsec[$geo]) ? $primsec[$geo] + 1 : 1; }
	//Gestion du service d'Ost
	if (isset($primsec[$ost]) && $primsec[$ost] == 5) { $bonus ++; } else { $primsec[$ost] = isset($primsec[$ost]) ? $primsec[$ost] + 1 : 1; }

	$_SESSION['amelio_bonus'] = $bonus;

	if (!$p_stepval) { $p_stepval = array(); }
	$domains = $db->req('SELECT %domain_id, %domain_name FROM %%domains ORDER BY %domain_name ASC ');
	$baseExp = getXPFromAvtg($_SESSION[$steps[11]['mod']], 100);
	$exp = $baseExp;
	foreach($p_stepval as $k => $v) {
		if (!isset($v['primsec'])) { $p_stepval[$k]['primsec'] = 0; }
		if (isset($v['curval'])) { $exp -= 10 * (int) $v['curval']; } else { $p_stepval[$k]['curval'] = 0; }
	}
	for ($i = 1, $c=count($domains); $i <= $c; $i++) {
		if (!isset($primsec[$i])) { $primsec[$i] = 0; }
	}
	?>
	<div class="notif noicon">
		<p><?php tr("Vous pouvez ici dépenser de l'expérience pour ajouter des bonus à vos domaines. 10XP donne un bonus de +1."); ?></p>
		<p><?php tr("N'oubliez pas de <strong>garder de l'expérience</strong> pour améliorer vos autres caractéristiques dans les étapes suivantes !"); ?></p>
		<p><?php tr("Vous pourrez, dans les étapes suivantes, choisir des <strong>Disciplines, pour 20 à 25XP minimum</strong>, ainsi que des <strong>Arts de combats</strong> (si vous possédez au moins 5 en Combat au Contact), <strong>pour 20XP chacun</strong>"); ?></p>
	</div>
	<div class="mt15">
		<input type="hidden" id="baseExp" value="<?php echo $baseExp; ?>" />
		<span id="expaffix" class="well well-small"><span class="icon-star-empty"></span> <?php tr("Expérience"); ?> : <span id="exp" class="well well-small"><?php echo $exp; ?></span></span>
	</div>
	<div class="row-fluid">
	<?php
	$i = 1;
	$countdoms = count($domains);
	foreach($domains as $domain) {##Affichage des domaines
		$total = (isset($p_stepval[$domain['domain_id']]['curval']) ? $p_stepval[$domain['domain_id']]['curval'] : 0) + $primsec[$domain['domain_id']]
		?>
		<div class="span3 domain"
			data-stepid="<?php echo $domain['domain_id']; ?>"
			data-jobval="<?php echo $primsec[$domain['domain_id']]; ?>"
			data-curval="<?php echo isset($p_stepval[$domain['domain_id']]['curval']) ? $p_stepval[$domain['domain_id']]['curval'] : '0'; ?>">
			<h5><?php tr($domain['domain_name']); ?></h5>
			<div class="progress xpdomcalc" data-domid="<?php echo $domain['domain_id']; ?>">
				<div class="bar bar-gray" style="width: <?php echo $primsec[$domain['domain_id']]*20; ?>%;"></div>
				<div class="bar bar-white" style="width: <?php echo isset($p_stepval[$domain['domain_id']]['curval']) ? $p_stepval[$domain['domain_id']]['curval']*20 : 0; ?>%;"></div>
				<span class="icon-minus"></span>
				<span class="icon-plus"></span>
				<span data-domid="<?php echo $domain['domain_id']; ?>" class="domain_value"><?php echo $total; ?></span>
			</div>
		</div>
		<?php
		if ($i != 1 && $i % 4 == 0 && $i < $countdoms) {
			?>
	</div><!--/.row-fluid-->

	<div class="row-fluid">
			<?php
		}
		$i++;
	} // end foreach domains as domain
	?>
	</div><!--/.row-fluid-->
	<?php

	buffWrite('css', '
	.rowfirst { margin-top: 10px; }
	.xpdomcalc { position: relative; }
	.domain_value {
		position: absolute;
		left: 50%;
		font-family: "Carolingia","Times new roman";
		margin-left:-2px;
	}
	.xpdomcalc span[class^=icon-] {
		display: block;
		margin: 3px 5px 0 5px;
		-webkit-transform: scale(0.8);
		-moz-transform: scale(0.8);
		-o-transform: scale(0.8);
		transform: scale(0.8);
		transition-property: all;
		-moz-transition-property: all;
		-webkit-transition-property: all;
		-o-transition-property: all;
		transition-duration: 200ms;
		-moz-transition-duration: 200ms;
		-webkit-transition-duration: 200ms;
		-o-transition-duration: 200ms;
	}
	.xpdomcalc span.icon-minus {
		position: absolute;
	}
	.xpdomcalc span.icon-plus {
		float: right;
	}
	button.btn.disabled {
		outline: none;
	}
	button.btn.disabled span[class^=icon-] {
		visibility: hidden;
	}
	div[class*="span"]:hover h5 { text-shadow: none; }
	h5 { text-align: center; }
	#jobdesc { padding: 10px; }
	#jobdesc p { margin-top: 10px; line-height: 1.2em; font-size: 0.9em; }
	div.row div[class*="span"]:hover { cursor: default; }
	div[class*="span"]:hover h3,
	div[class*="span"]:hover h4 {
		text-shadow: none;
	}
	div.progress {
		margin-top: 4px;
	}
	#exp {
		font-size: 1.2em;
		font-weight: bold;
	}
	#expaffix {
		top: 43px;
		z-index: 9999999;
	}
	.progress:hover { cursor: pointer; }
	select[id^=dom] {
		font-size: 0.8em;
		padding: 2px 4px;
		height: auto;
		width: auto;
	}
	', $page_mod);

	buffWrite('js', <<<JSFILE
		/**
		 * xpdomval
		 * Vérifie la validité de l'expérience des domaines (de 0 à 5)
		 */
		function xpdomval(id, incr) {
			var _this = $('.domain[data-stepid='+id+']'),
				stepval = _this.attr('data-jobval'),
				curval = _this.attr('data-curval'),
				values = { },
				xp = $('#exp').text(),
				xhr;
			curval = parseInt(curval, 10) + incr;
			if (curval < 0) { curval = 0; }
			if (curval > (5 - stepval)) { curval = 5 - stepval; }
			if (xp - incr*10 < 0) {
				curval --;
			}
			_this.attr('data-curval', curval);
			ajsend();
		}
		function ajsend() {
			var values = {},
				xhr,
				act;
			exp = $('#baseExp').val();
			values.etape = {$page_step};
			values['{$page_mod}'] = [];
			$('div.domain').each(function(index){
				var domid = $(this).attr('data-stepid'),
					jobval = parseInt($(this).attr('data-jobval'), 10),
					curval = parseInt($(this).attr('data-curval'), 10);
				$(this).find('.bar-white').width(curval*20+'%');
				exp -= curval*10;
				if (!isNaN(exp)) { $('#exp').text(exp); }
				if (curval > 0 || jobval > 0) {
					values['{$page_mod}'][domid] = {
						'primsec': jobval,
						'curval': curval
					};
				}
				$('.domain_value[data-domid="'+domid+'"]').text(parseInt(jobval+curval, 10));
			});//fin .each()
			if ($('#exp').text() >= 0) {
				sendMaj(values, '{$p_action}');
			} else {
				values['{$page_mod}'] = '';
				$('#gen_send').attr('href', '#').css('visibility', 'hidden');
				xhr = $.ajax({
					url : with_lang+'/ajax/aj_genmaj.php',
					type : 'post',
					data : values
				});
			}
		}
		\$(document).ready(function(){

			//$('#expaffix').piersAffix();

			\$('div.xpdomcalc').click(function(e) { if (this == e.target) { xpdomval($(this).attr('data-domid'), 1); } });
			\$('div.xpdomcalc div.bar, div.xpdomcalc span.icon-minus').click(function(e){ if (this == e.target) { xpdomval($(this).parent().attr('data-domid'), -1); }});
			\$('div.xpdomcalc span.icon-plus').click(function(e){ if (this == e.target) { xpdomval($(this).parent().attr('data-domid'), 1); }});
			ajsend();
		});
JSFILE
, $page_mod);
