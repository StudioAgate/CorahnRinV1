<?php
	$p_stepval = $p_stepval ? $p_stepval : array(0,0);
	$voies = isset($_SESSION[$steps[8]['mod']]) ? $_SESSION[$steps[8]['mod']] : false;
	if ($voies === false) {
		echo tr('Les voies n\'ont pas été définies, merci de vous rendre à l\'étape correspondante.', true).'<br />',
		mkurl(array('params'=>8, 'type' => 'tag', 'anchor' => 'Aller à la page des Voies', 'attr' => 'class="btn"'));
		return;
	}

	$com = $voies[1];
	$cre = $voies[2];
	$emp = $voies[3];
	$rai = $voies[4];
	$ide = $voies[5];

	$q = array();
	$d = array();

	$traits = array();
	if ($result = $db->req('SELECT  %trait_id, %trait_name, %trait_voie, %trait_qd, %trait_mm  FROM  %%traitscaractere ORDER BY %trait_name ASC')) {
		foreach ($result as $data) {
			$traits[] = $data;
			if ($cre >= 4 && $data['trait_voie'] == 'cre' && $data['trait_mm'] == 'maj') {
				if ($data['trait_qd'] == 'q') { $q[] = $data; } else { $d[] = $data; }
			} elseif ($cre <= 2 && $data['trait_voie'] == 'cre' && $data['trait_mm'] == 'min') {
				if ($data['trait_qd'] == 'q') { $q[] = $data; } else { $d[] = $data; }
			}
			if ($com >= 4 && $data['trait_voie'] == 'com' && $data['trait_mm'] == 'maj') {
				if ($data['trait_qd'] == 'q') { $q[] = $data; } else { $d[] = $data; }
			} elseif ($com <= 2 && $data['trait_voie'] == 'com' && $data['trait_mm'] == 'min') {
				if ($data['trait_qd'] == 'q') { $q[] = $data; } else { $d[] = $data; }
			}
			if ($rai >= 4 && $data['trait_voie'] == 'rai' && $data['trait_mm'] == 'maj') {
				if ($data['trait_qd'] == 'q') { $q[] = $data; } else { $d[] = $data; }
			} elseif ($rai <= 2 && $data['trait_voie'] == 'rai' && $data['trait_mm'] == 'min') {
				if ($data['trait_qd'] == 'q') { $q[] = $data; } else { $d[] = $data; }
			}
			if ($ide >= 4 && $data['trait_voie'] == 'ide' && $data['trait_mm'] == 'maj') {
				if ($data['trait_qd'] == 'q') { $q[] = $data; } else { $d[] = $data; }
			} elseif ($ide <= 2 && $data['trait_voie'] == 'ide' && $data['trait_mm'] == 'min') {
				if ($data['trait_qd'] == 'q') { $q[] = $data; } else { $d[] = $data; }
			}
			if ($emp >= 4 && $data['trait_voie'] == 'emp' && $data['trait_mm'] == 'maj') {
				if ($data['trait_qd'] == 'q') { $q[] = $data; } else { $d[] = $data; }
			} elseif ($emp <= 2 && $data['trait_voie'] == 'emp' && $data['trait_mm'] == 'min') {
				if ($data['trait_qd'] == 'q') { $q[] = $data; } else { $d[] = $data; }
			}
		}
		unset($data,$result);
	}
	$output = '';

?>
<p class="notif"><?php tr("Choisissez une qualité et un défaut"); ?></p>
<div class="row-fluid">
	<div class="span6" id="qualite">
		<h3><?php tr("Qualités"); ?></h3>
		<p><?php
		foreach ($q as $key => $qualite) {
			$output .= '<a class="btn btn-small qualite';
			if ($p_stepval[0] == $qualite['trait_id']) { $output .= ' btn-inverse'; }
			$output .= '" data-stepid="'.$qualite['trait_id'].'">'.tr($qualite['trait_name'], true).'</a>';
		}
		echo $output;
		$output = ''; ?></p>
	</div>
	<div class="span6" id="defaut">
		<h3><?php tr("Défauts"); ?></h3>
		<p><?php
		foreach ($d as $key => $defaut) {
			$output .= '<a class="btn btn-small defaut';
			if ($p_stepval[1] == $defaut['trait_id']) { $output .= ' btn-inverse'; }
			$output .= '" data-stepid="'.$defaut['trait_id'].'">'.tr($defaut['trait_name'], true).'</a>';
		}
		echo $output;
		?></p>
	</div>
</div>
<?php
	buffWrite('css', /** @lang CSS */ '
	#formgen div[class*="span"]:hover { cursor: default; }
	div[class^="span"] a.btn {
		margin: 2px;
		min-width: 88px;
		font-size: 0.8em;
	}
	h3 { text-align: center; }
	', $page_mod);
	buffWrite('js', /** @lang JavaScript */ "
		$(document).ready(function() {
			var values = { }, allval1 = '', allval2 = '', xhr;
			$('div[class*=span] p a.btn').click(function() {
				var act = '', tclass='';
				if ($(this).attr('class').match('defaut')) {
					tclass = 'defaut';
				} else {
					tclass = 'qualite';
				}
				$('div[class*=span] p a.btn.'+tclass).removeClass('btn-inverse');
				$(this).addClass('btn-inverse');
				act = $('a.btn.qualite.btn-inverse').attr('data-stepid')
					+ ','
					+ $('a.btn.defaut.btn-inverse').attr('data-stepid');
				if (act.match(/^[0-9]+,[0-9]+$/gi)) {
					values.etape = ".$page_step.";
					values['".$page_mod."'] = act;
					sendMaj(values, '".$p_action."');
				}
			});
		});", $page_mod);