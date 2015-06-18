<?php
	$avtgs = isset($_SESSION[$steps[11]['mod']]) ? $_SESSION[$steps[11]['mod']] : false;
	if ($avtgs === false) {
		tr("Les avantages n\'ont pas été définis, merci de vous rendre à l\'étape correspondante.<br />");
		echo mkurl(array('params'=>11, 'type' => 'TAG', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn"'));
		return;
	}
	//Si l'avantage 2 est présent, alors l'avantage "Mentor" a été sélectionné
	if (isset($avtgs['avantages'][2])) { $mentor = true; } else { $mentor = false; }

	$primsec = isset($_SESSION[$steps[13]['mod']]) ? $_SESSION[$steps[13]['mod']] : false;
	if ($primsec === false) {
		tr("Les domaines primaires et secondaires n\'ont pas été définis, merci de vous rendre à l\'étape correspondante.<br />");
		echo mkurl(array('params'=>13, 'type' => 'TAG', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn"'));
		return;
	}
	$amelio = isset($_SESSION[$steps[14]['mod']]) ? $_SESSION[$steps[14]['mod']] : false;
	if ($amelio === false) {
		tr("Les améliorations des domaines par dépense d\'XP n\'ont pas été définis, merci de vous rendre à l\'étape correspondante.<br />");
		echo mkurl(array('params'=>14, 'type' => 'TAG', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn"'));
		return;
	}

	$bonusdom = isset($_SESSION[$steps[15]['mod']]) ? $_SESSION[$steps[15]['mod']] : false;
	$sess_bonus = isset($_SESSION['bonusdom']) ? (int) $_SESSION['bonusdom'] : false;
	if ($bonusdom === false || $sess_bonus === false) {
		tr("Les bonus supplémentaires aux domaines n'ont pas été définis, merci de vous rendre à l'étape correspondante");
		echo mkurl(array('params'=>15, 'type' => 'tag', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn"'));
		return;
	}

	$totaldoms = array();
	$mentor_domain_id = 0;
	foreach($amelio as $id => $v) {
		if (!isset($totaldoms[$id])) {
			$totaldoms[$id] = $v['primsec'];
		}
		$totaldoms[$id] += $v['curval'];
		if ($v['primsec'] == 5) { $mentor_domain_id = $id; }//Récupération de l'id du domaine associé au mentor
	}
	foreach($primsec as $id => $v) {
		if ($v != 5 && $v != 3) {
			unset($totaldoms[$id]);
		}
	}
	foreach($bonusdom as $id => $v) {
		if (!isset($totaldoms[$id])) {
			$totaldoms[$id] = $v;
		} else {
			$totaldoms[$id] += $v;
		}
	}
	foreach($totaldoms as $k => $v) {
		if ($v < 5) { unset($totaldoms[$k]); }
	}

	$dom_ids = array();
	foreach ($primsec as $k => $v) {
		if ($v == 5 || ($v == 3 && isset($totaldoms[$k]) && $totaldoms[$k] === 5)) {
			$dom_ids[] = (int) $k;
		}
	}

	$domains = $db->req('SELECT %domain_id, %domain_name FROM %%domains WHERE %domain_id IN (%%%in) ORDER BY %domain_name ASC ', $dom_ids);
	if (!$domains) { $domains = array(); }
	$t = array();
	foreach($domains as $k => $v) { $t[$v['domain_id']] = $v; }
	$domains = $t; unset($t);
	$disc = $db->req('SELECT %%disciplines.%disc_name, %%discdoms.%disc_id, %%discdoms.%domain_id
		FROM %%discdoms
		INNER JOIN %%disciplines ON %%disciplines.%disc_id = %%discdoms.%disc_id
		WHERE %%disciplines.%disc_rang = "Professionnel"
		AND %%discdoms.%domain_id IN (%%%in)', $dom_ids);
	if (!$disc) { $disc = array(); }
	$mentor_disc_id = array();
	foreach($disc as $k => $v) {
		$domains[$v['domain_id']]['disciplines'][$v['disc_id']] = $v;
		if ($v['domain_id'] == $mentor_domain_id) { $mentor_disc_id[$v['disc_id']] = $v['disc_id']; }//On détermine la liste des disciplines affectées par un potentiel mentor
	}

	$baseExp = getXPFromAvtg($_SESSION[$steps[11]['mod']], 100);
	$baseExp = getXPFromDoms($_SESSION[$steps[14]['mod']], $baseExp);
	$basePoints = $sess_bonus;
	$points = $basePoints;
	$exp = $baseExp;
	if ($p_stepval) {
		foreach($p_stepval as $disc_id => $v) {
			if ($v['bonus']) {
				$points -= 1;
			} elseif ($v['exp']) {
				$cost = 25;
				if ($mentor === true && isset($disc_id) && isset($mentor_disc_id[$disc_id])) { $cost = 20; }
				$exp -= $cost;
			}
		}
	}
	?>
	<div class="notif noicon">
		<p><?php tr("Vous avez la possibilité de choisir des disciplines en fonction des domaines qui disposent d'un score de 5 et font partie de vos <strong>domaines primaires et secondaires</strong>."); ?></p>
		<p><?php tr("Vous avez droit à <strong>2 disciplines maximum</strong>, chacune coûtant 25XP ou 1 point bonus, à vous de choisir."); ?></p>
		<p><?php tr("Si vous avez choisi l'avantage \"Allié Mentor\", alors choisir une discipline dans votre domaine de prédilection ne vous coûtera que 20XP, mais toujours 1 point bonus."); ?></p>
	</div>
	<p class="warning"><?php tr("Attention ! À ce stade, les <strong>points bonus non dépensés</strong> sont <strong>perdus</strong> !"); ?></p>
	<p class="info"><?php tr("L'expérience, elle, est conservée"); ?></p>
	<div id="affix" class="clearfix mt15">
		<div class="pull-left">
			<input type="hidden" id="basePoints" value="<?php echo $basePoints; ?>" />
			<span id="pointsAffix" class="well well-small"><span class="icon-plus-sign"></span> <?php tr("Points bonus"); ?> : <span id="points" class="well well-small"><?php echo $points; ?></span></span>
		</div>
		<div class="pull-left ml10">
			<input type="hidden" id="baseExp" value="<?php echo $baseExp; ?>" />
			<span id="expaffix" class="well well-small"><span class="icon-star-empty"></span> <?php tr("Expérience"); ?> : <span id="exp" class="well well-small"><?php echo $exp; ?></span></span>
		</div>
	</div>
	<?php

	foreach($domains as $domain_id => $domain) { ?>
		<div class="row">
			<div class="span domain" data-mentor="<?php echo $mentor === true && $mentor_domain_id == $domain_id ? '1' : '0'; ?>">
				<h3><?php echo $domain['domain_name'], $mentor === true && $mentor_domain_id == $domain_id ? ' <small>('.tr("L\'avantage \"Mentor\" s\'applique à ce domaine", true).')</small>' : ''; ?></h3>
				<?php
					foreach($domain['disciplines'] as $disc_id => $disc) {
						$active = isset($p_stepval[$disc_id]) ? true : false;
						if ($active === true) {
							$activebonus = $p_stepval[$disc_id]['bonus'] ? ' btn-inverse' : '';
							$activeexp = $p_stepval[$disc_id]['exp'] ? ' btn-inverse' : '';
						} else { $activebonus = $activeexp = ''; }
						?><div class="ib well well-small center m5 disccontainer" data-domid="<?php echo $domain_id; ?>" data-discid="<?php echo $disc_id; ?>">
							<span><?php tr($disc['disc_name']); ?></span>
							<div class="btn-group">
								<button class="btn btn-small ptsbonus discbtn<?php echo $activebonus; ?>"><span class="icon-plus-sign"></span></button>
								<button class="btn btn-small expbonus discbtn<?php echo $activeexp; ?>"><span class="icon-star-empty"></span></button>
							</div>
						</div><?php
					}
				?>
			</div>
		</div><!--/.row-->
		<?php
	}


	buffWrite('css', '
	a.btn {
		margin-bottom: 7px;
	}
	form#formgen div.row div[class*=span].domain:hover h3,
	div[class*="span"]:hover h1, div[class*="span"]:hover h2, div[class*="span"]:hover h3, div[class*="span"]:hover h4, div[class*="span"]:hover h5, div[class*="span"]:hover h6 {
		cursor: default;
		text-shadow: none;
	}
	', $page_mod);
	buffWrite('js', <<<JSFILE
	var baseExp, basePoints;
	function ajsend() {
		var values = {};
		values.etape = {$page_step};
		values['{$page_mod}'] = [0];
		\$('.discbtn.btn-inverse').each(function(){
			values['{$page_mod}'][\$(this).parents('.disccontainer').attr('data-discid')] = {
				val: 6,
				bonus: $(this).is('.ptsbonus') ? 1 : null,
				exp: $(this).is('.expbonus') ? 1 : null,
				domain : parseInt($(this).parents('.disccontainer').attr('data-domid'), 10)
			};
		});
		console.info(values);
		sendMaj(values, '{$p_action}');
	}
	\$(document).ready(function(){
		baseExp = \$('#baseExp').val();
		basePoints = $('#basePoints').val();

		\$('.discbtn').click(function(){
			var exp = baseExp, points = basePoints;

			$(this).toggleClass('btn-inverse');

			if ($(this).is('.btn-inverse.ptsbonus')) {
				$(this).next('.btn').removeClass('btn-inverse');
			} else if ($(this).is('.btn-inverse.expbonus')) {
				$(this).prev('.btn').removeClass('btn-inverse');
			}

			\$('.discbtn.btn-inverse:gt(1)').removeClass('btn-inverse');

			\$('.discbtn.btn-inverse').each(function(){
				var cost = 25;
				if ($(this).is('.expbonus')) {
					if ($(this).parents('.domain').attr('data-mentor') === '1') { cost = 20; }
					exp -= cost;
					if (exp < 0) {
						\$(this).removeClass('btn-inverse');
						exp += cost;
					}
				} else if ($(this).is('.ptsbonus')) {
					points -= 1;
					if (points < 0) {
						\$(this).removeClass('btn-inverse');
						points += 1;
					}
				}
			});
			\$('#exp').text(exp);
			\$('#points').text(points);

			ajsend();
		});
		ajsend();

		//\$('#affix').piersAffix();
	});
JSFILE
, $page_mod);
