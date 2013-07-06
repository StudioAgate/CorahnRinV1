<?php
$armures_user = $char->get('inventaire.armures');

$t = $db->req('SELECT %armure_id, %armure_name FROM %%armures');//On récupère les armures
$armures = array();
foreach($t as $v) { $armures[$v['armure_id']] = $v; }//On formate le tableau
?>

<div class="row-fluid">

	<?php
	$i = 0;
	foreach($armures as $id => $v) {
		$active = isset($armures_user[$id]) ? ' btn-inverse' : '';
		?>
	<span class="change_value span2 btn-small btn mb10<?php echo $active; ?>" data-armureid="<?php echo $id; ?>"><?php tr($v['armure_name']); ?></span>
			<input type="hidden" name="armure[<?php echo $id; ?>]" value="<?php echo $active ? '1' : '0'; ?>" />

		<?php $i++;
		if ($i % 6 === 0) {
			?>
</div><div class="row-fluid">

		<?php
		}
	}
	?>

</div>