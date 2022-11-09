
	<div class="row">
		<div class="span4<?php echo $p_stepval == 'Rural' ? ' checked' : ''; ?>" id="Rural">
			<h3><?php tr("Rural"); ?></h3>
			<p><?php tr("Votre personnage est issu d'une campagne ou d'un lieu relativement isolé."); ?></p>
		</div><div class="span4<?php echo $p_stepval == 'Urbain' ? ' checked' : ''; ?>" id="Urbain">
			<h3><?php tr("Urbain"); ?></h3>
			<p><?php tr("Votre personnage a vécu longtemps dans une ville, suffisamment pour qu'il ait adopté les codes de la ville dans son mode de vie."); ?></p>
		</div>
	</div>
	<?php
	buffWrite('css', /** @lang CSS */ '
		#formgen div[class*="span"] h3 {
			padding: 5px;
			text-align: center;
		}
		div[class*="span"]:hover { cursor: pointer; }
		[class*="span"] p {
			padding: 10px;
		}', $page_mod);
	buffWrite('js', /** @lang JavaScript */ "
		$(document).ready(function() {
			var values = { }, xhr;
			$('div[class*=span]').click(function() {
				$('div[class*=span]').removeClass('checked');
				$(this).addClass('checked');
				values.etape = ".$page_step.";
				values['".$page_mod."'] = $(this).attr('id');
				sendMaj(values, '".$p_action."');
			});
		});", $page_mod);
