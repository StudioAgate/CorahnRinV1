<?php
$daols = $char->get_daols();
?>

	<div class="m10">
		<div class="control-group">
			<label for="exp" class="control-label"><?php tr('Daols de braise'); ?></label>
			<div class="controls">
				<input id="daols_braise" name="daols_braise" type="text" class="input-mini" value="<?php echo $daols['braise']; ?>" />
			</div>
		</div>
		<div class="control-group">
			<label for="exp" class="control-label"><?php tr('Daols d\'azur'); ?></label>
			<div class="controls">
				<input id="daols_azur" name="daols_azur" type="text" class="input-mini" value="<?php echo $daols['azur']; ?>" />
			</div>
		</div>
		<div class="control-group">
			<label for="exp" class="control-label"><?php tr('Daols de givre'); ?></label>
			<div class="controls">
				<input id="daols_givre" name="daols_givre" type="text" class="input-mini" value="<?php echo $daols['givre']; ?>" />
			</div>
		</div>
	</div>