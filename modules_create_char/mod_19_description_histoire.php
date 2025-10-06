<?php
    global $_PAGE;

    /** @var string $page_mod */
    /** @var string $p_action */

	$sex = $p_stepval['sex'] ?? '';
	$name = isset($p_stepval['name']) ? ' value="'.$p_stepval['name'].'"' : '';
	$player = isset($p_stepval['player']) ? ' value="'.$p_stepval['player'].'"' : '';
	$histoire = $p_stepval['histoire'] ?? '';
	$faits = $p_stepval['faits'] ?? '';
	$description = isset($p_stepval['description']) ? ' value="'.$p_stepval['description'].'"' : '';
?>
	<p>
		<label for="name"><span class="text-red">*</span><?php tr("Nom de votre personnage"); ?></label>
		<input class="span4" type="text" id="name" required placeholder="<?php tr("Entrez ici le nom de votre personnage"); ?>"<?php echo $name; ?> />
	</p>
	<p class="btn-group" data-toggle="buttons-radio">
		<button type="button" data-sex="Homme" class="btn<?php echo $sex === 'Homme' ? ' active' : ''; ?>"><?php tr("Homme"); ?></button>
		<button type="button" data-sex="Femme" class="btn<?php echo $sex === 'Femme' ? ' active' : ''; ?>"><?php tr("Femme"); ?></button>
	</p>
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
	', $page_mod);

	buffWrite('js', /** @lang JavaScript */ <<<JSFILE
    function isValid() {
        const hasChosenGender = $('button[data-sex].active')[0];
        const hasTypedName = $('#name').val().trim().length > 0;
        console.info({hasChosenGender, hasTypedName});
        return hasChosenGender && hasTypedName;
    }

	function send_datas() {
        if (!isValid()) {
            console.info('Invalid form data.');
            $('#gen_send').attr('href', '#').css('visibility', 'hidden');
            return;
        }
        console.info('sending data');
		var values = {
            '{$page_mod}': {
                sex: $('button[data-sex].active').attr('data-sex'),
                name: $('#name').val(),
                player: $('#player').val(),
                histoire: $('#histoire').val(),
                description: $('#description').val(),
                faits: $('#faits').val(),
            }
		};
        sendMaj(values, '{$p_action}');
	}

	$(document).ready(function(){
        let keydownTimeout;
        $('[data-sex]')
            .click(() => {
                setTimeout(() => send_datas(), 100);
            })
        ;
		$('textarea,#player,#name')
			.keydown(function (){
                console.info('keydown');
                if (keydownTimeout) { clearTimeout(keydownTimeout); }
                keydownTimeout = setTimeout(() => send_datas(), 200);
            })
			.blur(() => send_datas())
        ;
	});
JSFILE
, $page_mod);
