<?php

	$primsec = isset($_SESSION[$steps[14]['mod']]) ? $_SESSION[$steps[14]['mod']] : false;
	if ($primsec === false) {
		tr("Les améliorations des domaines par dépense d\'XP n\'ont pas été définis, merci de vous rendre à l\'étape correspondante.<br />");
		echo mkurl(array('params'=>14, 'type' => 'TAG', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn"'));
		return;
	}
	$bonus = isset($_SESSION['amelio_bonus']) ? (int) $_SESSION['amelio_bonus'] : false;
	if ($bonus === false) {
		tr("Les domaines primaires et secondaires n\'ont pas été définis, merci de vous rendre à l\'étape correspondante.<br />");
		echo mkurl(array('params'=>13, 'type' => 'TAG', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn"'));
		return;
	}

	$avtgs = isset($_SESSION[$steps[11]['mod']]) ? $_SESSION[$steps[11]['mod']] : false;
	if ($avtgs === false) {
		tr("Les avantages n\'ont pas été définis, merci de vous rendre à l\'étape correspondante.<br />");
		echo mkurl(array('params'=>11, 'type' => 'TAG', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn"'));
		return;
	}
	if (isset($avtgs['avantages'][2])) {//Si l'avantage 2 est présent, alors l'avantage "Mentor" a été sélectionné
		$mentor = 1;
	} else {
		$mentor = 0;
	}

	$age = isset($_SESSION[$steps[6]['mod']]) ? (int) $_SESSION[$steps[6]['mod']] : false;
	if ($age === false) {
		tr('L\'âge n\'est pas défini, merci de vous rendre à l\'étape correspondante.<br />');
		echo mkurl(array('params'=>6, 'type' => 'TAG', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn"'));
		return;
	}

	if ($age <= 20) {
		$basePoints = 0;
		$_SESSION[$page_mod] = array(0);
		$_SESSION['etape']++;
	} elseif (21 <= $age && $age <= 25) {
		$basePoints = 1;
	} elseif (26 <= $age && $age <= 30) {
		$basePoints = 2;
	} elseif (31 <= $age && $age <= 35) {
		$basePoints = 3;
	} else {
		tr("L\'âge n\'est pas défini correctement car supérieur à 35, merci de vous rendre à l\'étape correspondante.<br />");
		echo mkurl(array('params'=>6, 'type' => 'TAG', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn"'));
		return;
	}
	$basePoints += $bonus;
	$basePoints += $mentor;

	$total_doms = array();
	$doms_maxed = array();
	foreach($primsec as $i => $val) {
		$total_doms[$i] = 0;
		if (isset($primsec[$i]['primsec'])) { $total_doms[$i] += (int) $primsec[$i]['primsec']; }
		if (isset($primsec[$i]['curval'])) { $total_doms[$i] += (int) $primsec[$i]['curval']; }
		if ($total_doms[$i] > 5) { tr("Une erreur est survenue lors du calcul des domaines, veuillez recommencer", true); $_SESSION[$page_mod] = ''; return; }
		if ($total_doms[$i] == 5) { unset($total_doms[$i]); $doms_maxed[] = $i;; }
	}
	$dom_ids = array_keys($total_doms);

	$points = $basePoints;

	if ($basePoints === 0 && P_DEBUG === false && $p_stepval != array(0=>0)) {
		$_SESSION[$page_mod] = array(0=>0);
		$_SESSION['bonusdom'] = 0;
		header('Location: '.mkurl(array('params'=>$steps[$page_step+1]['mod'])));
		exit;
	}

	if (!$p_stepval) {
		$p_stepval = array();
	} else {
		foreach($p_stepval as $k => $v) {
			if ($v) { $points -= 1; }
		}
	}
	if (!empty($doms_maxed)) {
		$domains = $db->req('SELECT %domain_id, %domain_name FROM %%domains WHERE %domain_id NOT IN ('.implode(',',$doms_maxed).') ORDER BY %domain_name ASC ');
	} else {
		tr("Une erreur est survenue : aucun de vos domaines n'a de score égal à 5. Veuillez retourner aux étapes précédentes..<br />");
		echo mkurl(array('params'=>$page_step-1, 'type' => 'TAG', 'anchor' => 'Aller à l\'étape précédente', 'attr' => 'class="btn"'));
		return;
	}

	if ($p_stepval != array(0=>0)) {
	?>
	<div class="notif noicon">
		<p><?php tr("Vous disposez de $basePoints points supplémentaires à répartir dans $basePoints domaines"); ?></p>
		<p><?php tr("Sachez que vous pouvez <strong>conserver 2 points</strong> pour <strong>obtenir des disciplines</strong>, à l'étape suivante."); ?></p>
	</div>
	<div class="mt15">
		<input type="hidden" id="basePoints" value="<?php echo $basePoints; ?>" />
		<span id="pointsAffix" class="well well-small"><?php tr("Points supplémentaires"); ?> : <span id="points" class="well well-small"><?php echo $points; ?></span></span>
	</div>
	<div class="row-fluid">
	<?php
	$i = 1;
	foreach($domains as $domain) {##Affichage des domaines
		$total = (isset($p_stepval[$domain['domain_id']]) ? $p_stepval[$domain['domain_id']] : 0) + (isset($total_doms[$domain['domain_id']]) ? $total_doms[$domain['domain_id']] : 0)
		?>
		<div class="span3 domain"
			data-stepid="<?php echo $domain['domain_id']; ?>"
			data-jobval="<?php echo isset($total_doms[$domain['domain_id']]) ? $total_doms[$domain['domain_id']] : '0'; ?>"
			data-curval="<?php echo isset($p_stepval[$domain['domain_id']]) ? $p_stepval[$domain['domain_id']] : '0'; ?>">
			<h5><?php tr($domain['domain_name']); ?></h5>
			<div class="progress bonusdomcalc" data-domid="<?php echo $domain['domain_id']; ?>">
				<div class="bar bar-gray" style="width: <?php echo isset($total_doms[$domain['domain_id']]) ? $total_doms[$domain['domain_id']]*20 : 0; ?>%;"></div>
				<div class="bar bar-white" style="width: <?php echo isset($p_stepval[$domain['domain_id']]) ? $p_stepval[$domain['domain_id']]*20 : 0 ; ?>%;"></div>
				<span class="icon-minus"></span>
				<span class="icon-plus"></span>
				<span data-domid="<?php echo $domain['domain_id']; ?>" class="domain_value"><?php echo $total; ?></span>
			</div>
		</div>
		<?php
		if ($i != 1 && $i % 4 == 0 && $i < count($domains)) {
			?>
	</div><!--/.row-fluid-->

	<div class="row-fluid">
			<?php
		}
		$i++;
	} // end foreach domains as domain
	?>
	</div><!--/.row-fluid-->
	<script type="text/javascript">var p_stepval = <?php echo $p_stepval ? 'true' : 'false'?>;</script>
	<?php
	} else {?>

	<div class="notif">
		<p>
			<?php tr("Vous n'avez aucun point supplémentaire à répartir."); ?>
		</p>
	</div>
	<?php
	}

	buffWrite('css', '
	.domain_value {
		position: absolute;
		left: 50%;
		font-family: "Carolingia","Times new roman";
		margin-left:-2px;
	}
	.bonusdomcalc { position: relative; }
	.rowfirst { margin-top: 10px; }
	.bonusdomcalc span[class^=icon-] {
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
	.bonusdomcalc span.icon-minus {
		position: absolute;
	}
	.bonusdomcalc span.icon-plus {
		float: right;
	}
	.domain .primsecchoix button.btn span[class^=icon-] {
		-webkit-transform: scale(0.8);
		-moz-transform: scale(0.8);
		-o-transform: scale(0.8);
		transform: scale(0.8);
		margin: 0 -2.4px;
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
	#points {
		font-size: 1.2em;
		font-weight: bold;
	}
	#pointsAffix {
		top: 45px;
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
		 * bonusdomval
		 * Vérifie la validité de l'pointsérience des domaines (de 0 à 5)
		 */
		function bonusdomval(id, incr) {
			var _this = $('.domain[data-stepid='+id+']'),
				stepval = _this.attr('data-jobval'),
				curval = _this.attr('data-curval'),
				values = { },
				points = $('#points').text(),
				xhr;
			curval = parseInt(curval, 10) + incr;
			if (curval < 0) { curval = 0; }
			if (curval > 1) { curval = 1; }
			if (points - incr < 0) {
				curval = 0;
			}
			_this.attr('data-curval', curval);
			ajsend();
		}
		function ajsend() {
			var values = {},
				xhr,
				act;
			points = $('#basePoints').val();
			values.etape = {$page_step};
			values['{$page_mod}'] = [0];
			values['bonusdom'] = 0;
			$('div.domain').each(function(index){
				var domid = $(this).attr('data-stepid'),
					jobval = parseInt($(this).attr('data-jobval'), 10),
					curval = parseInt($(this).attr('data-curval'), 10);
				$(this).find('.bar-white').width(curval*20+'%');
				points -= curval;
				if (!isNaN(points)) { $('#points').text(points); }
				if (curval > 0) {
					values['{$page_mod}'][domid] = curval;
				}
				$('.domain_value[data-domid="'+domid+'"]').text(isNaN(parseInt(jobval+curval, 10)) ? '0' : parseInt(jobval+curval, 10));
			});//fin .each()
			values['bonusdom'] = parseInt($('#points').text(), 10);
			if ($('#points').text() >= 0) {
				sendMaj(values, '{$p_action}');
			} else {
				values['{$page_mod}'] = '';
				$('#gen_send').attr('href', '#').css('visibility', 'hidden');
				xhr = $.ajax({
					url : 'ajax/aj_genmaj.php',
					type : 'post',
					data : values
				});
			}
		}
		\$(document).ready(function(){

			\$('div.bonusdomcalc').click(function(e) { if (this == e.target) { bonusdomval($(this).attr('data-domid'), 1); } });
			\$('div.bonusdomcalc div.bar, div.bonusdomcalc span.icon-minus').click(function(e){ if (this == e.target) { bonusdomval($(this).parent().attr('data-domid'), -1); }});
			\$('div.bonusdomcalc span.icon-plus').click(function(e){ if (this == e.target) { bonusdomval($(this).parent().attr('data-domid'), 1); }});
			if (p_stepval === false) { ajsend(); }
		});
JSFILE
, $page_mod);
