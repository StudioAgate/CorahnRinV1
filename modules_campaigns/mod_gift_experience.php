<?php
$exp_reste = $char->get('experience.reste');
$exp_total = $char->get('experience.total');
?>

	<p><?php tr("Expérience du personnage"); ?> : <span class="underline"><?php echo $exp_reste.' / '.$exp_total; ?></span></p>

	<div class="m10">
		<div class="control-group">
			<label for="exp" class="control-label"><?php tr('Expérience à donner'); ?></label>
			<div class="controls">
				<input id="exp" name="exp" type="text" class="input-mini" value="0" />
			</div>
		</div>
		<div id="exp_slider" class="data-slider ml10"  data-slider-input="#exp" data-slider-min="0" data-slider-max="100"></div>
	</div>