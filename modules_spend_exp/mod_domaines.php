<?php
$doms = $char->get('domaines');

?>
<div class="row-fluid">

	<?php
		$i = 1;
		$count = count($doms);
		foreach ($doms as $id => $v) { ?>
			<div class="span3">
				<p><?php tr($v['name']); ?></p>
				<div class="progress" data-stat="domaines.<?php echo $id; ?>">
					<span data-stat="domaines.<?php echo $id; ?>" class="progress_text"><?php echo $v['val']; ?></span>
					<div class="bar bar-gray" style="width: <?php echo $v['val']*20; ?>%;"></div>
					<div class="bar bar-white" style="width: 0%;"></div>
					<input type="hidden" id="domaines.<?php echo $id; ?>" name="domaines.<?php echo $id; ?>" value="<?php echo $v['val']; ?>" />
					<span class="icon-minus"></span>
					<span class="icon-plus"></span>
				</div>
			</div>
			<?php

			if ($i % 4 === 0 && $i !== $count) {
				?></div><div class="row-fluid"><?php
			}
			$i++;
		}
	?>
</div>

<?php
$_PAGE['more_js'][] = BASE_URL.'/js/pages/pg_'.$module_name.'.js';
buffWrite('js', '', $module_name);
