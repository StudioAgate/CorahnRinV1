<?php

	if (isset($_SESSION[$steps[8]['mod']])) {
		$voies = $_SESSION[$steps[8]['mod']];
		$com = $voies[1];
		$cre = $voies[2];
		$rai = $voies[4];
		$ide = $voies[5];
		$conscience = $rai + $ide;
		$instinct = $com + $cre;
		if ($conscience > $instinct) {
			//$_SESSION[$page_mod] = 'Rationnelle';
			$attr_rati = 'btn-inverse';
			$attr_inst = 'disabled';
			$_SESSION[$page_mod] = 'Rationnelle';
		} elseif ($instinct > $conscience) {
			$attr_rati = 'disabled';
			$attr_inst = 'btn-inverse';
			$_SESSION[$page_mod] = 'Instinctive';
		} elseif ($instinct == $conscience) {
			$attr_rati = $p_stepval == 'Rationnelle' ? 'btn-inverse' : ($p_stepval == '' ? '' : 'disabled');
			$attr_inst = $p_stepval == 'Instinctive' ? 'btn-inverse' : ($p_stepval == '' ? '' : 'disabled');
		}
	}
	if (!$p_stepval && isset($_SESSION[$page_mod]) && P_DEBUG === false) {
		$_SESSION['etape']++;
		header('Location: '.mkurl(array('params'=>$steps[$page_step+1]['mod'])));
		exit;
	}
?>
		<div class="row">
			<div class="span">
				<p><?php
					if (@$voies) {
						?>
							<span class="btn <?php echo $attr_rati; ?>" data-stepid="Rationnelle"><?php tr("Orientation rationnelle"); ?></span>
							<span class="btn <?php echo $attr_inst; ?>" data-stepid="Instinctive"><?php tr("Orientation instinctive"); ?></span>
						<?php
					} else {
						echo tr('Les voies n\'ont pas été définies, merci de vous rendre à l\'étape correspondante', true).
							'<br />'.mkurl(array('params'=>8, 'type' => 'tag', 'anchor' => 'Aller à la page des Voies', 'attr' => 'class="btn"'));
					}
				?></p>
			</div>
		</div><!--/.row-->

		<?php
		buffWrite('css', '', $page_mod);
		buffWrite('js', "
			$(document).ready(function() {
				var values = { }, xhr;
				$('span.btn:not(.disabled)').mouseup(function() {
					$('span.btn').removeClass('btn-inverse');
					$(this).addClass('btn-inverse');
					values.etape = ".$page_step.";
					values['".$page_mod."'] = $(this).attr('data-stepid');
					sendMaj(values, '".$p_action."');
				});
			});
		", $page_mod);