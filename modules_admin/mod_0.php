<?php
$users = $db->req('SELECT %user_id, %user_name FROM %%users');

if (!empty($_POST)) {
	$_POST = get_post_datas();
	$id = array_keys($_POST);
	$name = array_values($_POST);
	$name = isset($name[0]) ? $name[0] : null;
	if ($name === null) {
// 		echo 'Erreur';
		return;
	}
	$id = $id[0];
	$id = (int) str_replace('user.', '', $id);
	if ($id) {
		Session::write('userchanged', Users::$id);
	}
	if ($id && Users::init($id)) {
		redirect(array('val'=>1), 'Utilisateur changé pour : '.$name, 'success');
	}
	$_POST = array();
}
?>
<form method="post" name="change_user">
<fieldset>
	<h3><?php tr('Changer d\'utilisateur'); ?></h3>
	<?php
	foreach ($users as $v) { ?>
		<input type="submit" class="btn" name="user.<?php echo $v['user_id']; ?>" value="<?php echo $v['user_name'];?>" />
	<?php }
	?>
</fieldset>
</form>