<?php

	$voies = isset($_SESSION[$steps[8]['mod']]) ? $_SESSION[$steps[8]['mod']] : false;
	if ($voies === false) {
		echo tr('Les voies n\'ont pas été définies, merci de vous rendre à l\'étape correspondante.', true).'<br />',
		mkurl(array('params'=>8, 'type' => 'tag', 'anchor' => 'Aller à la page des Voies', 'attr' => 'class="btn"'));
		return;
	}

	$maj = $min = array();
	foreach($voies as $id => $v) {
		if ($v < 3) { $min[$id] = 1; }
		elseif ($v > 3) { $maj[$id] = 1; }
	}
	unset($voies);

	$t = $db->req('SELECT %desordre_voies_min,%desordre_voies_maj,%desordre_name,%desordre_id FROM %%desordres');
	$desordres = array();
	foreach($t as $v) {
		$defined = false;
		$v['desordre_voies_maj'] = explode(',', $v['desordre_voies_maj']);
		$v['desordre_voies_min'] = explode(',', $v['desordre_voies_min']);
		foreach($v['desordre_voies_maj'] as $voie) {
			$voie = (int) $voie;
			if (isset($maj[$voie])) {
				$defined = true;
			}
		}
		unset($voie);
		foreach($v['desordre_voies_min'] as $voie) {
			if (isset($min[$voie])) {
				$defined = true;
			}
		}
		if ($defined === true) {
			$desordres[$v['desordre_id']] = $v;
		}
	}
	unset($t);
?>

	<div class="content">
		<p class="notif"><?php tr("Vous pouvez choisir un désordre mental, celui-ci déterminera la tendance de votre personnage lorsqu'il est choqué, effrayé ou pris par surprise."); ?></p>
		<div class="content mt10"><?php
			foreach($desordres as $id => $v) {
				$active = $p_stepval == $id ? ' btn-inverse' : '';
				echo '<button href="#" class="btn',$active,'" data-desordreid="',$id,'">',tr($v['desordre_name'], true),'</button>';
			}
		?></div>
	</div>

	<?php
	buffWrite('css', '
	button {
		margin: 0 5px;
	}
	', $page_mod);
	buffWrite('js', "
		$(document).ready(function() {
			var values = { }, xhr;
			$('button').click(function() {
				$('button').removeClass('btn-inverse');
				$(this).addClass('btn-inverse');
				values.etape = ".$page_step.";
				values['".$page_mod."'] = $(this).attr('data-desordreid');
				sendMaj(values, '".$p_action."');
			});
		});
	", $page_mod);
