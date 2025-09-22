<?php
    global $_PAGE, $p_action, $page_mod;

	$sex = isset($p_stepval['sex']) ? $p_stepval['sex'] : '';
	$name = isset($p_stepval['name']) ? ' value="'.$p_stepval['name'].'"' : '';
	$player = isset($p_stepval['player']) ? ' value="'.$p_stepval['player'].'"' : '';
	$histoire = isset($p_stepval['histoire']) ? $p_stepval['histoire'] : '';
	$faits = isset($p_stepval['faits']) ? $p_stepval['faits'] : '';
	$description = isset($p_stepval['description']) ? ' value="'.$p_stepval['description'].'"' : '';
?>
	<!--<p>
	<a class="btn btn-inverse" id="validate"><?php tr("Valider les modifications"); ?></a>
	</p>-->
	<p>
		<label for="name"><span class="text-red">*</span><?php tr("Nom de votre personnage"); ?></label>
		<input class="span4" type="text" id="name" required placeholder="<?php tr("Entrez ici le nom de votre personnage"); ?>"<?php echo $name; ?> />
	</p>
	<div class="btn-group" data-toggle="buttons-radio">
		<button type="button" data-sex="Homme" class="btn<?php echo $sex == 'Homme' ? ' active' : ''; ?>"><?php tr("Homme"); ?></button>
		<button type="button" data-sex="Femme" class="btn<?php echo $sex == 'Femme' ? ' active' : ''; ?>"><?php tr("Femme"); ?></button>
	</div>
	<p>
		<label for="player"><?php tr("Nom du joueur"); ?></label>
		<input class="span4" type="text" id="player" placeholder="<?php tr("Entrez ici le nom du joueur"); ?>"<?php echo $player; ?> />
	</p>
	<p>
		<label for="description"><?php tr("Description"); ?></label>
		<input class="span4" type="text" id="description" placeholder="<?php tr("Insérez ici une description de votre personnage"); ?>"<?php echo $description; ?> />
	</p>
	<p>
		<label for="histoire"><?php tr("Histoire"); ?></label>
		<textarea id="histoire" rows="5" placeholder="<?php tr("Écrivez ici l'histoire de votre personnage"); ?>"><?php echo $histoire; ?></textarea>
	</p>
	<p>
		<label for="faits"><?php tr("Faits marquants"); ?></label>
		<textarea id="faits" rows="5" placeholder="<?php tr("Écrivez ici les faits marquants dans l'histoire de votre personnage"); ?>"><?php echo $faits; ?></textarea>
	</p>

	<script type="text/javascript">
		var trad_title = "<?php tr("Champs manquants"); ?>",
			trad_cnt = "<?php tr("Le nom et le sexe du personnage doivent être mentionnés"); ?>";
	</script>
	<?php
	buffWrite('css', /** @lang CSS */ '
		textarea {
			min-width: 200px;
			width: 60%;
			max-width: 80%;
			max-height: 300px;
		}
		.popover {
			width: 400px;
		}
	', $page_mod);
	buffWrite('js', /** @lang JavaScript */ <<<JSFILE
	var timeout = [];
	function send_datas() {
		var values = {};
		if ($('.popover')[0]) { $('#validate').popover('hide'); }//On désactive le popover s'il est actif
		if ($('button[data-sex].active')[0] && $('#name').val()){
			values['{$page_mod}'] = {};
			values['{$page_mod}'].sex = $('button[data-sex].active').attr('data-sex');
			values['{$page_mod}'].name = $('#name').val();
			values['{$page_mod}'].player = $('#player').val();
			values['{$page_mod}'].histoire = $('#histoire').val();
			values['{$page_mod}'].description = $('#description').val();
			values['{$page_mod}'].faits = $('#faits').val();
			sendMaj(values, '{$p_action}');
		} else {
			clearTimeout(timeout[0]);
		 	timeout[0] = setTimeout(function(){ $('#validate').popover('show'); },200);
			clearTimeout(timeout[1]);
		 	timeout[1] = setTimeout(function(){ $('#validate').popover('hide'); },3200);
		}
	}
	$(document).ready(function(){
		$('#validate:not(.disabled)').popover({
			'title' : trad_title,
			'content': trad_cnt,
			'trigger' : 'manual'
		});
		$('#validate,[data-sex]').click(function(){ send_datas(); });
		$('textarea,#player,#name')
			.blur(function(){ send_datas(); })
			.keydown(function (e){ if(e.ctrlKey && e.keyCode === 13){ send_datas(); } });
	});
JSFILE
, $page_mod);
