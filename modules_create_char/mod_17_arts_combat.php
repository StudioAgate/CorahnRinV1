<?php

	$dom_amelio = isset($_SESSION[$steps[14]['mod']]) ? $_SESSION[$steps[14]['mod']] : false;
	$dom_age = isset($_SESSION[$steps[15]['mod']]) ? $_SESSION[$steps[15]['mod']] : false;
	$avtgs = isset($_SESSION[$steps[11]['mod']]) ? $_SESSION[$steps[11]['mod']] : false;
	$disc = isset($_SESSION[$steps[16]['mod']]) ? $_SESSION[$steps[16]['mod']] : false;

	$show = true;

	$combat = isset($dom_amelio[2]) ? $dom_amelio[2]['curval'] + $dom_amelio[2]['primsec'] + (isset($dom_age[2]) ? $dom_age[2] : 0) : 0;
	$tir = isset($dom_amelio[14]) ? $dom_amelio[14]['curval'] + $dom_amelio[14]['primsec'] + (isset($dom_age[14]) ? $dom_age[14] : 0) : 0;
	if ($combat != 5 && $tir != 5) {
		$_SESSION[$page_mod] = array(0=>0);
		if ((!$p_stepval || empty($p_stepval)) && P_DEBUG === false) {
			$_SESSION['etape']++;
			header('Location: '.mkurl(array('params'=>$steps[$page_step+1]['mod'])));
			exit;
		}
		$show = false;
	}

	if ($dom_amelio === false) {
		tr("Les améliorations des domaines par dépense d\'XP n\'ont pas été définis, merci de vous rendre à l\'étape correspondante.<br />");
		echo mkurl(array('params' => 14, 'type'=> 'tag', 'anchor' => "Aller à la page correspondante", 'attr' =>  'class="btn"'));
		return;
	}
	if ($dom_age === false) {
		tr("Les bonus supplémentaires aux domaines n\'ont pas été définis, merci de vous rendre à l\'étape correspondante");
		echo mkurl(array('params' => 15, 'type'=> 'tag', 'anchor' => "Aller à la page correspondante", 'attr' =>  'class="btn"'));
		return;
	}
	if ($avtgs === false) {
		tr("Les avantages n\'ont pas été définis, merci de vous rendre à l\'étape correspondante.<br />");
		echo mkurl(array('params' => 11, 'type'=> 'tag', 'anchor' => "Aller à la page correspondante", 'attr' =>  'class="btn"'));
		return;
	}
	if ($disc === false) {
		tr("Les disciplines n\'ont pas été définie, merci de vous rendre à l\'étape correspondante.");
		echo mkurl(array('params' => 16, 'type'=> 'tag', 'anchor' => "Aller à la page correspondante", 'attr' =>  'class="btn"'));
		return;
	}

	$baseExp = getXPFromAvtg($_SESSION[$steps[11]['mod']], 100);
	$baseExp = getXPFromDoms($dom_amelio, $baseExp);
	$baseExp = getXPFromDiscs($disc, $baseExp);

	$exp = $baseExp;
	$t = $db->req('SELECT %avdesv_id, %avdesv_type, %avdesv_name, %avdesv_xp, %avdesv_desc, %avdesv_double FROM %%avdesv WHERE %avdesv_name REGEXP "^Arts de combat"');
	$arts = array();
	foreach($t as $v) {
		$arts[$v['avdesv_id']] = $v;
	}

	if ($p_stepval) {
		foreach($p_stepval as $k => $v) {
			if ($v > 0) {
				$exp -= 20;
			} else {
				unset($p_stepval[$k]);
			}
		}
	}
?>
	<div>
		<p><?php tr("Vous avez la possibilité de choisir des arts de combats, chacun coûtant 20XP"); ?>.</p>
	</div>
	<div>
		<input type="hidden" id="baseExp" value="<?php echo $baseExp; ?>" />
		<p><?php tr("Expérience"); ?> : <span id="exp" class="well well-small"><?php echo $exp; ?></span></p>
	</div>

	<div class="content">

	<?php
	if ($show === true) {
		foreach($arts as $art_id => $art) {
			$active = isset($p_stepval[$art_id]) ? ' btn-inverse' : '';
			if (
				($tir == 5 && preg_match('#Archerie#isU', $art['avdesv_name']))
				|| ($combat == 5 && !preg_match('#Arch.rie#isU', $art['avdesv_name']))
			) {
				echo '<a class="btn artbtn'.$active.'" data-stepid="'.$art_id.'">'.tr(preg_replace('#Arts? de combat \(([^)]+)\)#isU', '$1', $art['avdesv_name']), true).'</a> ';
			}
		}
	}
	?></div>

	<?php
		buffWrite('css', '', $page_mod);
		buffWrite('js', /** @lang JavaScript */ <<<JSFILE
		var baseExp;
		function ajsend() {
			var values = {};
			values.etape = {$page_step};
			values['{$page_mod}'] = [0];
			\$('.artbtn.btn-inverse').each(function(){
				values['{$page_mod}'][\$(this).attr('data-stepid')] = 6;
			});
			sendMaj(values, '{$p_action}');
		}
		\$(document).ready(function(){
			baseExp = \$('#baseExp').val();

			\$('.artbtn').click(function(){
				var exp = baseExp;

				if (\$(this).is('.btn-inverse')) {
					\$(this).removeClass('btn-inverse');
				} else {
					\$(this).addClass('btn-inverse');
				}

				\$('.artbtn.btn-inverse:gt(1)').removeClass('btn-inverse');

				\$('.artbtn.btn-inverse').each(function(){
					exp -= 20;
					if (exp < 0) {
						\$(this).removeClass('btn-inverse');
						exp += cost;
					}
				});
				\$('#exp').text(exp);

				if (exp >= 0) { ajsend(); }
			});
			ajsend();
		});
JSFILE
, $page_mod);
