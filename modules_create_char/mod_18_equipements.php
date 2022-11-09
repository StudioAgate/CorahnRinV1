<?php
	if (empty($p_stepval)) {
		$p_stepval = array('arme'=>array(),'armure'=>array());
	}
	$t = $db->req('SELECT %arme_id, %arme_name FROM %%armes');
	$armes = array();
	foreach($t as $v) {
		$armes[$v['arme_id']] = $v;
	}

	$t = $db->req('SELECT %armure_id, %armure_name FROM %%armures WHERE %armure_prot > 0');
	$armures = array();
	foreach ($t as $v) {
		$armures[$v['armure_id']] = $v;
	}

?>
	<!--<p>
		<a class="btn btn-inverse" id="validate"><?php tr("Valider les modifications"); ?></a>
	</p>-->
	<div class="row-fluid">
		<div class="span12">
			<p><?php tr("Vous avez la possibilité de choisir quelques objets à ajouter à votre inventaire. Ne soyez pas trop gourmand !"); ?></p>
			<div class="row-fluid">
				<div class="span6"><?php
				foreach($armes as $id => $v) {
					$active = in_array($id, $p_stepval['arme']) ? ' btn-inverse' : '';
					echo '<button class="btn btn-small'.$active.'" data-armeid="'.$id.'">'.tr($v['arme_name'], true).'</button>';
				} ?></div>
				<div class="span6"><?php
				foreach($armures as $id => $v) {
					$active = in_array($id, $p_stepval['armure'])  ? ' btn-inverse' : '';
					echo '<button class="btn btn-small'.$active.'" data-armureid="'.$id.'">'.tr($v['armure_name'], true).'</button>';
				} ?></div>
			</div>
		</div>
	</div><!--/.row-->
	<hr class="hr" />
	<div class="row-fluid">
		<div class="span12">
			<label for="autre_equip"><?php tr("Autres possessions. Indiquez un objet par ligne."); ?></label>
			<textarea id="autre_equip"><?php echo isset($p_stepval['autre_equip']) ? $p_stepval['autre_equip'] : ''?></textarea>
		</div>
	</div>

	<?php
	buffWrite('css', /** @lang CSS */ '
		button { margin: 5px 3px; }
		#autre_equip { width: 100%; height: 150px; }
	', $page_mod);
	buffWrite('js', /** @lang JavaScript */ <<<JSFILE
		function send_datas() {
			var values = {};
			values['{$page_mod}'] = {};
			values['{$page_mod}'].arme = [];
			$('button[data-armeid].btn-inverse').each(function(){
				values['{$page_mod}'].arme.push($(this).attr('data-armeid'));
			});
			values['{$page_mod}'].armure = [];
			$('button[data-armureid].btn-inverse').each(function(){
				values['{$page_mod}'].armure.push($(this).attr('data-armureid'));
			});
			values['{$page_mod}'].autre_equip = $('#autre_equip').val();
			console.info(values);
			sendMaj(values, '{$p_action}');
		}
		$(document).ready(function(){
			$('button').click(function(){
				$(this).toggleClass('btn-inverse');
				if ($(this).attr('data-armeid')) {
					$('[data-armeid].btn-inverse:gt(4)').removeClass('btn-inverse');
				} else if ($(this).attr('data-armureid')) {
					$('[data-armureid].btn-inverse:gt(3)').removeClass('btn-inverse');
				}
			});
			$('#validate,[data-armeid],[data-armureid]').click(function(){ send_datas(); });
			$('textarea')
				.blur(function(){ send_datas(); })
				.keydown(function (e){ if(e.ctrlKey && e.keyCode == 13){ send_datas(); } });
		});
JSFILE
, $page_mod);