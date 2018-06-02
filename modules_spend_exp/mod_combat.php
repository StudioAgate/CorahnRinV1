<?php
$def = $char->get('defense');
$rap = $char->get('rapidite');

?>
<p class="info noicon">
<?php tr('Le coût en XP pour augmenter la rapidité ou la défense est le suivant :');?><br />
<?php tr('Pour augmenter 1 point, le coût est équivalent au niveau suivant multiplié par 5, puis ajouté de 5.'); ?>
</p>
<div class="row-fluid">
	<div class="span4" id="rapidite">
		<p><?php tr('Rapidité'); ?></p>
		<p><?php tr('Score de base de rapidité'); ?> : <?php echo $rap['base']; ?></p>
		<div class="progress" data-stat="rapidite.amelioration">
			<span data-stat="rapidite.amelioration" class="progress_text"><?php echo $rap['amelioration']; ?></span>
			<div class="bar bar-gray" style="width: <?php echo $rap['amelioration']*20; ?>%;"></div>
			<div class="bar bar-white" style="width: 0%;"></div>
			<input type="hidden" id="rapidite.amelioration" name="rapidite.amelioration" value="<?php echo $rap['amelioration']; ?>" />
			<span class="icon-minus"></span>
			<span class="icon-plus"></span>
		</div>
	</div>
	<div class="span4" id="defense">
		<p><?php tr('Défense'); ?></p>
		<p><?php tr('Score de base de Défense'); ?> : <?php echo $def['base']; ?></p>
		<div class="progress" data-stat="defense.amelioration">
			<span data-stat="defense.amelioration" class="progress_text"><?php echo $def['amelioration']; ?></span>
			<div class="bar bar-gray" style="width: <?php echo $def['amelioration']*10; ?>%;"></div>
			<div class="bar bar-white" style="width: 0%;"></div>
			<input type="hidden" id="defense.amelioration" name="defense.amelioration" value="<?php echo $def['amelioration']; ?>" />
			<span class="icon-minus"></span>
			<span class="icon-plus"></span>
		</div>
	</div>
</div>

<?php
$_PAGE['more_js'][] = BASE_URL.'/js/pages/pg_'.$module_name.'.js';
buffWrite('js', <<<JSFILE

JSFILE
, $module_name);
