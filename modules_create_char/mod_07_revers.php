<?php

	$output = '';
	$t = $db->req('SELECT %rev_id,%rev_name,%rev_desc FROM %%revers ORDER BY %rev_id ASC');
	$revers = array();
	foreach ($t as $k => $v) {
		$revers[$v['rev_id']] = $v;
	}
	unset($revers[1], $t, $k, $v);

	$age = isset($_SESSION[$steps[6]['mod']]) ? $_SESSION[$steps[6]['mod']] : false;
	if ($age === false) {
		tr('L\'âge n\'a pas été défini, merci de vous rendre à l\'étape correspondante.');
		echo '<br />';
		mkurl(array('params'=>6, 'type' => 'tag', 'anchor' => 'Aller à la page des Voies', 'attr' => 'class="btn"'));
		return;
	}

	if ($age >= 21) {
		if (!$p_stepval) {
			$dice = array();
			if ($age > 20) {

				$dice[0] = rand(1,10);
				if ($dice[0] == 10) {
					$dice[1] = rand(2, 9);
				} elseif ($dice[0] == 1) {
					$output .= tr("<strong>Poisse</strong> ! Un revers supplémentaire !", true).'<br />';
					$dice[0] = rand(2, 9);
					$dice[1] = rand(2, 9);
					do {
						$dice[1] = rand(2,9);
					} while ($dice[1] == $dice[0]);
				}

				if ($age >= 26) {
					do {
						$act = rand(2,9);
					} while (in_array($act, $dice));
					$dice[2] = $act;
				}
				if ($age >= 31) {
					do {
						$act = rand(2,9);
					} while (in_array($act, $dice));
					$dice[3] = $act;
				}
				unset($act);
			}
		} else {
			$dice = $p_stepval;
		}

		if (isset($dice[0]) && $dice[0] == 0) {
			$output = tr('Aucun revers (moins de 21 ans)', true);
		} elseif (isset($dice[0]) && $dice[0] > 0) {
			foreach ($dice as $val) {
				if ($val) {
					$output .= '<strong>'.tr($revers[$val]['rev_name'], true).'</strong>, '.tr($revers[$val]['rev_desc'], true).'<br />';
				}
			}
		}

		if ($dice == array()) { $output = tr('Aucun revers (moins de 21 ans)', true); }
	} else {
		$output = tr('Aucun revers (moins de 21 ans)', true);
		$dice = array(0=>0);
	}
	unset($revers);
	if ($output === tr('Aucun revers (moins de 21 ans)', true) || $age < 21) {
		$_SESSION[$page_mod] = array(0=>'0');
		if (!$p_stepval && P_DEBUG === false) {
			$_SESSION['etape']++;
			header('Location: '.mkurl(array('params'=>$steps[$page_step+1]['mod'])));
			exit;
		}
	}

	if (empty($p_stepval)) {
		Session::write($page_mod, $dice);
		$_SESSION['etape'] = $page_step+1;
		$p_stepval = $dice;
	}
	unset($age);
	?>
	<div class="row">
		<div class="span" id="revers">
		<?php
		if ($p_stepval) {
			echo $output;
		} else {
			foreach($dice as $k => $v) {
				?><input type="hidden" class="hiddenReversInput" value="<?php echo $v;?>" id="InputHidden<?php echo $k; ?>" />
		<?php
			}
			?><p><a class="btn"><?php tr("Cliquez ici pour générer les revers"); ?></a></p><?php
		}
		unset($dice);
		?><p id="generated"><?php
			if (!$p_stepval) { echo $output; }//Ce qui est affiché après avoir appuyé sur le bouton
			unset($output);
		?></p>
		</div>
	</div>

	<?php
// 	unset($output, $k);
echo "<style type=\"text/css\">#gen_send{visibility:visible;}</style>";
buffWrite('css', '
	#formgen p#generated { display: none; }
	#formgen div[class*="span"]:hover { cursor: default; }
', $page_mod);
buffWrite('js', "
	$(document).ready(function(){
		$('#gen_send').delay(1).attr('href', base_url+'".$p_action."').html(nextsteptranslate).css('visibility', 'visible');
	});
", $page_mod);
