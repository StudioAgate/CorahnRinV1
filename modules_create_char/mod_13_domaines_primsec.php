<?php
	$avtgs = isset($_SESSION[$steps[11]['mod']]) ? $_SESSION[$steps[11]['mod']] : false;
	if ($avtgs === false) {
		tr("Les avantages n'ont pas été définis, merci de vous rendre à l'étape correspondante.<br />");
		echo mkurl(array('params'=>11, 'type' => 'TAG', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn"'));
		return;
	}
	//Si l'avantage 2 est présent, alors l'avantage "Mentor" a été sélectionné
	if (isset($avtgs['avantages'][2])) { $mentor = true; } else { $mentor = false; }

	//Si l'avantage 23 est présent, alors l'avantage "Lettré" a été sélectionné
	if (isset($avtgs['avantages'][23])) { $lettre = true; } else { $lettre = false; }

	$job = isset($_SESSION[$steps[2]['mod']]) ? $_SESSION[$steps[2]['mod']] : false;
	if ($job === false) {
		tr("Le métier n'a pas été défini, merci de vous rendre à l'étape correspondante.<br />");
		echo mkurl(array('params'=>2, 'type' => 'TAG', 'anchor' => 'Aller à la page correspondante', 'attr' => 'class="btn"'));
		return;
	}

	if (is_numeric($job)) {
		$jobdoms = $db->req('
		SELECT
			%%domains.%domain_name as %domname,
			%%domains.%domain_id as %domid,
			%%jobdomains.%jobdomain_primsec as %primsec
		FROM %%domains
		LEFT JOIN %%jobdomains
			ON %%jobdomains.%domain_id = %%domains.%domain_id
		WHERE %%jobdomains.%job_id = :jobid', array('jobid' => (int) $job));
		$test = array();
		foreach($jobdoms as $key => $val) {
			$test[$val['domid']] = $val;
			if ($val['primsec'] == '1') { $predil = $val['domid']; }
		}
		$jobdoms = $test;
		unset($test);
	} else {
		$jobdoms = array();
		$predil = 0;
	}
	?>
	<p class="notif"><?php tr("Voici vos domaines de compétences, ils déterminent les aptitudes générales de votre personnage"); ?></p>
	<?php

	$persojob = false;
	if (!$p_stepval) {
		$p_stepval = array();
	}
	if (is_numeric($job)) {
		$persojob = false;
		if ($job = $db->row('SELECT %job_name, %job_desc FROM %%jobs WHERE %job_id = ? LIMIT 0,1', array($job))) {
		?>
			<div id="jobdesc" class="info noicon">
				<h4><?php tr("Métier prédéfini"); ?> : <?php tr($job['job_name']); ?></h4>
				<?php tr("Rappel de la description"); ?> : <p><?php tr(nl2br($job['job_desc'])); ?></p>
			</div>
		<?php
		} else {
			?><?php tr("Le métier n'est pas défini, merci de retourner sur la page concernée pour le définir"); ?><br /><?php
			echo mkurl(array('params'=>2, 'type' => 'TAG', 'anchor' => "Retourner à la sélection du métier", 'attr' => 'class="btn"'));
		}
	} elseif (is_string($job)) {
		$persojob = true;
	?>
		<div class="notif noicon"><h4><?php tr("Métier personnalisé"); ?> : <?php tr($job); ?></h4></div>
	<?php
		$job = array($job);
	} else {
		?><p class="error"><?php tr("Une erreur est survenue, veuillez réinitialiser le personnage et recommencer"); ?>... #001</p><?php
		return;
	}

	if (is_array($job) && !empty($job)) {
		$primsec_ok = array(0,1,2,3,5);
		$domains = $db->req('SELECT %domain_id, %domain_name FROM %%domains ORDER BY %domain_name ASC ');
		$i = 1;
	?>
	<div class="notif noicon">
		<p><?php tr("Choisissez des domaines primaires et secondaires pour votre personnage"); ?>.</p>
		<p><?php tr("Pour <strong>déselectionner</strong> un élément, cliquez sur ce bouton"); ?> : <span class="btn btn-mini"><span class="icon-star-empty"></span></span></p>
		<p><?php tr('Vous devez sélectionner <span class="underline"><strong>deux fois <span class="progress1">&#9733;</span> et <span class="progress2">&#9733;&#9733;</span></strong></span>, et <span class="underline"><strong>une seule fois <span class="progress3">&#9733;&#9733;&#9733;</span> et <span class="progress5">&#9733;&#9733;&#9733;&#9733;&#9733;</span></strong></span>.'); ?></p>
		<?php if ($persojob === true) {
			?><p><?php tr('Vous avez choisi un métier prédéfini, c\'est pourquoi certains domaines ne peuvent pas bénéficier de <span class="progress3">&#9733;&#9733;&#9733;</span> ou <span class="progress5">&#9733;&#9733;&#9733;&#9733;&#9733;</span>.'); ?></p><?php
		}
		if ($lettre === true) {
			$lettrid = isset($p_stepval['lettre']) ? $p_stepval['lettre'] : 0;
			?><p><?php tr("Vous avez choisi l'avantage \"<strong>Lettré</strong>\". Vous pouvez sélectionner choisir un bonus de +1 parmi 4 domaines différents en cliquant sur le bouton"); ?>
			<span class="btn btn-mini"><span class="icon-book"></span></span></p><?php
		} ?>
		<p><?php
			$ost = array();
			if (isset($p_stepval['ost']) && $p_stepval['ost'] != '2') {
				$ost[1] = '';
				$ost[0] = ' active';
			} else {
				$ost[1] = ' active';
				$ost[0] = '';
			}
		?>
			<?php tr("Avez-vous effectué votre service d'Ost (service militaire) ?"); ?>
			<span class="btn-group groupOst" data-toggle="buttons-radio">
				<button class="btn btn-small<?php echo $ost[1]; ?>" data-ost="1"><?php tr("Oui"); ?></button>
				<button class="btn btn-small<?php echo $ost[0]; ?>" data-ost="0"><?php tr("Non"); ?></button>
			</span>
		</p>
		<p class="toggleOstInfos"<?php echo $ost[0] ? ' style="display:block;"' : ''; ?>><?php tr("Si non, choisissez le domaine de compétence dans lequel vous avez été formé en cliquant sur le bouton<span class=\"btn btn-mini\"><span class=\"icon-certificate icon-red\"></span></span> à côté du domaine concerné."); ?></p>
	</div>
	<div id="domainscontainer">
		<div class="row-fluid rowfirst">
	<?php
		foreach($domains as $domain) {
			$primsec = isset($p_stepval[$domain['domain_id']]) ? $p_stepval[$domain['domain_id']] : 0;
			if ($domain['domain_id'] == $predil) { $primsec = 5; }
			if (!in_array($primsec, $primsec_ok)) { $primsec = 0; }
			##Affichage des domaines
			$dis = $primsec == 5 && $persojob === false ? ' disabled' : '';
			if ($persojob === true) {
				$passion = '';
			} else {
				$passion = (isset($jobdoms[$domain['domain_id']]) && $jobdoms[$domain['domain_id']]['primsec'] == 0)
							|| (count($jobdoms) == 1 && $primsec != 5)
				? '' : ' disabled';
				if (isset($jobdoms[$domain['domain_id']]) && $jobdoms[$domain['domain_id']]['primsec'] == 0 && count($jobdoms) === 2) {
					$primsec = 3;
				}
			}
			$predil_txt = ($primsec == 5 || $predil == $domain['domain_id'] ? ' active' : '')
						 .(($persojob === false || $dis) && $primsec != 5 ? ' disabled' : '');
			$ostactive = isset($p_stepval['ost']) && $p_stepval['ost'] != 2 ? ' style="display: block;"' : '';
			$ostid = isset($p_stepval['ost']) && $p_stepval['ost'] != 2 ? $p_stepval['ost'] : 0;
			?>
			<div class="span3 domain" data-stepid="<?php echo $domain['domain_id']; ?>" data-jobval="<?php echo $primsec; ?>">
				<h5>
					<?php
						if ($domain['domain_id'] != 2) {
							?><button class="btn btn-mini ostbtn<?php echo $ostid == $domain['domain_id'] ? ' btn-inverse' : ''; ?>" <?php echo $ostactive; ?>><span class="icon-certificate icon-red"></span></button><?php
						}
					if ($lettre === true && in_array($domain['domain_id'], array(4,7,13,16))) {
						?><button class="btn btn-mini lettrbtn<?php echo $lettrid == $domain['domain_id'] ? ' btn-inverse' : ''; ?>"><span class="icon-book"></span></button><?php
					}
					?>
					<?php tr($domain['domain_name']); ?>
				</h5>
				<div class="btn-group bl mid primsecchoix" data-toggle="buttons-radio">
					<button type="button" data-change="0" class="btn btn-mini<?php echo ($primsec == 0 ? ' active' : ''),$dis; ?>">
						<span>&#9734;</span>
					</button>
					<button type="button" data-change="1" class="btn btn-mini<?php echo ($primsec == 1 ? ' active' : ''),$dis; ?>">
						<span>&#9733;</span>
					</button>
					<button type="button" data-change="2" class="btn btn-mini<?php echo ($primsec == 2 ? ' active' : ''),$dis; ?>">
						<span>&#9733;&#9733;</span>
					</button>
					<button type="button" data-change="3" class="btn btn-mini<?php echo ($primsec == 3 ? ' active' : ''),$passion; ?>">
						<span>&#9733;&#9733;&#9733;</span>
					</button>
					<button type="button" data-change="5" class="btn btn-mini<?php echo $predil_txt; ?>">
						<span>&#9733;&#9733;&#9733;&#9733;&#9733;</span>
					</button>
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
	</div>
		<?php
	} else {
		?><p><?php tr("Une erreur est survenue, veuillez réinitialiser le personnage et recommencer"); ?>... #002</p><?php
		return;
	}

		buffWrite('css', '
		.ostbtn { display: none; float: left; }
		.lettrbtn { float: left; }
		span.icon-certificate.icon-red { margin-top: -1px; }
		.toggleOstInfos { display: none; }
		#domainscontainer {
			border-radius: 15px;
			-webkit-box-shadow: 0 0 15px #ddd;
			-moz-box-shadow: 0 0 15px #ddd;
			box-shadow: 0 0 15px #ddd;
			padding: 5px 15px 25px 15px;
			margin-top: 15px;
		}

		.primsecchoix button[data-change="1"],.progress1 { color: darkblue; }
		.primsecchoix button[data-change="2"],.progress2 { color: blue; }
		.primsecchoix button[data-change="3"],.progress3 { color: darkgreen; }
		.primsecchoix button[data-change="5"],.progress5 { color: darkred; }

		button.btn.disabled {
			outline: none;
			opacity: 0.2;
		}
		button.btn[data-change] {
			-webkit-transition-property: opacity;
			   -moz-transition-property: opacity;
				 -o-transition-property: opacity;
				    transition-property: opacity;
			-webkit-transition-duration: 800ms;
			   -moz-transition-duration: 800ms;
				 -o-transition-duration: 800ms;
					transition-duration: 800ms;
		}
		button.btn.notactive { opacity: 0.2; }
		button.btn[data-change]:not(.disabled):hover { opacity: 1; }
		button.btn.disabled span {
			visibility: hidden;
		}
		h5 { text-align: center; }
		#jobdesc { padding: 10px; }
		#jobdesc p { margin-top: 10px; line-height: 1.2em; font-size: 0.9em; }
		div.row div[class*="span"]:hover { cursor: default; }
		div[class*="span"]:hover h3,
		div[class*="span"]:hover h4,
		div[class*="span"]:hover h5 {
			text-shadow: none;
		}
		.domain {
			border-radius: 20px;
			-webkit-box-shadow: 0 0 10px #ccc;
			-moz-box-shadow: 0 0 10px #ccc;
			box-shadow: 0 0 10px #ccc;
			margin-bottom: 6px;
			margin-top: 6px;
			padding: 0 10px 10px;
		}
		.domain .primsecchoix button.btn span {
			font-size: 1.2em;
		}
	', $page_mod);


		buffWrite('js', <<<JSFILE
		function show_and_hide() {
			act = $('[data-jobval][data-jobval!=0]');
			if (act.filter('[data-jobval=5]').length == 1) {
				$('[data-change=5]:not(".active"):not(".disabled")').addClass('notactive');
				$('[data-change=5].active').removeClass('notactive');
			} else { $('[data-change=5]:not(".disabled")').removeClass('notactive'); }
			if (act.filter('[data-jobval=3]').length == 1) {
				$('[data-change=3]:not(".active"):not(".disabled")').addClass('notactive');
				$('[data-change=3].active').removeClass('notactive');
			} else { $('[data-change=3]:not(".disabled")').removeClass('notactive'); }
			if (act.filter('[data-jobval=2]').length == 2) {
				$('[data-change=2]:not(".active"):not(".disabled")').addClass('notactive');
				$('[data-change=2].active').removeClass('notactive');
			} else { $('[data-change=2]:not(".disabled")').removeClass('notactive'); }
			if (act.filter('[data-jobval=1]').length == 2) {
				$('[data-change=1]:not(".active"):not(".disabled")').addClass('notactive');
				$('[data-change=1].active').removeClass('notactive');
			} else { $('[data-change=1]:not(".disabled")').removeClass('notactive'); }
		}
		$(document).ready(function(){
			var datachange,
				basedomain,
				values = {},
				act;
			values.etape = {$page_step};
			values['{$page_mod}'] = [];
			values.ost = 0;
			$('button').click(function(){
				if ($(this).attr('data-change')) {
					if ($(this).attr('class').match(/disabled|active/gi)) { return false; }
					datachange = $(this).attr('data-change');
					basedomain = $(this).parents('div.domain');
					if (0 <= datachange && datachange <= 5 && datachange != 4) {
						$('[data-jobval='+datachange+']'+(datachange == 1 || datachange == 2 ? ':gt(0)' : ''))
							.attr('data-jobval', 0)
							.find('button')
							.removeClass('active')
							.filter('[data-change=0]')
							.addClass('active');
					}
					$(this)
						.parents('[data-jobval]')
						.attr('data-jobval', datachange)
						.find('button')
						.removeClass('active')
						.filter('[data-change='+datachange+']')
						.addClass('active');
				} else if ($(this).is('.ostbtn')) {
					if ($(this).is('.btn-inverse')) {
						$('.ostbtn').removeClass('btn-inverse');
					} else {
						$('.ostbtn').removeClass('btn-inverse');
						$(this).addClass('btn-inverse');
					}
				} else if ($(this).is('.lettrbtn')) {
					$('.lettrbtn').removeClass('btn-inverse').filter(this).addClass('btn-inverse');
				}
				act = $('[data-jobval][data-jobval!=0]');
				show_and_hide();
				if (
					act.filter('[data-jobval=5]').length == 1 &&
					act.filter('[data-jobval=3]').length == 1 &&
					act.filter('[data-jobval=2]').length == 2 &&
					act.filter('[data-jobval=1]').length == 2 &&
					(
						($('.lettrbtn')[0] && $('.lettrbtn.btn-inverse')[0])
					 || !$('.lettrbtn')[0]
					) &&
					(
						$('[data-ost="1"].active')[0]
					|| ($('[data-ost="0"].active')[0] && $('.ostbtn.btn-inverse')[0])
					)
				) {
					values['{$page_mod}'] = [];
					$('[data-jobval][data-jobval!=0]').each(function(){
						var index = \$(this).attr('data-stepid');
						values['{$page_mod}'][index] = \$(this).attr('data-jobval');
					});
					if ($('.lettrbtn')[0]) { values.lettre = $('.lettrbtn.btn-inverse').parents('.domain').attr('data-stepid'); }
					values.ost = $('.ostbtn.btn-inverse').parents('.domain').attr('data-stepid');
					if (values['{$page_mod}']) { sendMaj(values, '{$p_action}'); }
				} else {
					values['{$page_mod}'] = '';
					values.ost = 2;
					if ($('.lettrbtn')[0]) { values.lettre = ''; }
					$('#gen_send').attr('href', '#').css('visibility', 'hidden');
					xhr = $.ajax({
						url : with_lang+'/ajax/aj_genmaj.php',
						type : 'post',
						data : values
					});
				}
			});
			show_and_hide();

			$('.groupOst').click(function(){
				if($('.groupOst').find('.active').is('[data-ost="1"]')) {
					$('.toggleOstInfos').show();
					$('.ostbtn').show();
				} else {
					$('.toggleOstInfos').hide();
					$('.ostbtn').removeClass('btn-inverse').hide();
				}
			});
		});
JSFILE
, $page_mod);
