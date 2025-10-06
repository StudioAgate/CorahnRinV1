<?php
$armes_user = $char->get('inventaire.armes');

$t = $db->req('SELECT %arme_id, %arme_name FROM %%armes');//On récupère les armes
$armes = [];
foreach($t as $v) { $armes[$v['arme_id']] = $v; }//On formate le tableau
?>

<div class="row-fluid">

	<?php
	$i = 0;
	foreach($armes as $id => $v) {
		$active = isset($armes_user[$id]) ? ' btn-inverse' : '';
		?>
	<span class="change_value span2 btn-small btn mb10<?php echo $active; ?>" data-armeid="<?php echo $id; ?>"><?php tr($v['arme_name']); ?></span>
			<input type="hidden" name="arme[<?php echo $id; ?>]" value="<?php echo $active ? '1' : '0'; ?>" />

		<?php $i++;
		if ($i % 6 === 0) {
			?>
</div><div class="row-fluid">

		<?php
		}
	}
	?>

</div>