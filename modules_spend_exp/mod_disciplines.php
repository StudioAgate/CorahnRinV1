<?php

$tab = $char->get();

$doms = (array) Hash::extract($tab, 'domaines.{n}');

unset($tab);

$dom_ids = array();
foreach ($doms as $k => $v) {
	$dom_ids[] = $v['id'];
}

$discs_list = $db->req('SELECT %%disciplines.%disc_name, %%discdoms.%disc_id, %%discdoms.%domain_id
		FROM %%discdoms
		INNER JOIN %%disciplines ON %%disciplines.%disc_id = %%discdoms.%disc_id
		WHERE %%disciplines.%disc_rang = "Professionnel"');

?>
<div class="info"><?php tr('Les disciplines peuvent être choisies lorsque le domaine associé a un score de 5. Elles commencent avec un score de 6.'); ?></div>
<div class="warning"><?php tr('Les disciplines liées à la magience et aux artefacts ne peuvent être prises que si le score de magience est à 1 au moins.'); ?></div>
<?php

foreach ($doms as $k => $v) {
	$discs = Hash::extract($discs_list, '{n}[domain_id='.$v['id'].']');
	?>
	<div class="content domain_parent" data-domain="<?php echo $v['id']; ?>">
		<h4><?php tr($v['name']); ?></h4>
		<div class="row-fluid"><?php
		$i = 0;
		$count = count($discs);
		foreach ($discs as $kk => $disc) {
			$stat = 'domaines.{n}[id='.$v['id'].'].disciplines.{n}[id='.$disc['disc_id'].']';
			//pr($disc);
			$val = Hash::extract($doms, '{n}[id='.$v['id'].'].disciplines.{n}[id='.$disc['disc_id'].']');
			if (isset($val[0])) { $val = $val[0]; } else { $val = array('id'=>0,'val'=>0); }
			//$val['val'] = $val['val'] ? $val['val'] - 5 : 0;
			?>
			<div class="span3">
				<p><?php tr($disc['disc_name']); ?></p>
				<div class="progress" data-domain="<?php echo $disc['domain_id']; ?>" data-stat="<?php echo $stat; ?>">
					<span data-stat="<?php echo $stat; ?>" class="progress_text"><?php echo $val['val']; ?></span>
					<div class="bar bar-gray" style="width: <?php echo $val['val']*(100/15); ?>%;"></div>
					<div class="bar bar-white" style="width: 0%;"></div>
					<input type="hidden" id="<?php echo $stat; ?>" name="<?php echo $stat; ?>" value="<?php echo $val['val']; ?>" />
					<span class="icon-minus"></span>
					<span class="icon-plus"></span>
				</div>
			</div>
			<?php
			$i++;
			if ($i % 4 === 0 && $i !== $count) {
				?></div><div class="row-fluid"><?php
			}
		}
		?>
		</div>
	</div>
	<?php
	unset($discs);
}