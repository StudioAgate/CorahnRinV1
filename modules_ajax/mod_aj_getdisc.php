<?php
if (isset($_POST['domid'])) {
	$id = (int) $_POST['domid'];
	$res = $db->req('
	SELECT %%disciplines.%disc_id, %%disciplines.%disc_name
		FROM %%disciplines
		LEFT JOIN %%discdoms
			ON %%disciplines.%disc_id = %%discdoms.%disc_id
		LEFT JOIN %%domains
			ON %%domains.%domain_id = %%discdoms.%domain_id
		WHERE %%discdoms.%domain_id = :id AND %%disciplines.%disc_rang = :pro', array(':id' => $id, ':pro'=>'Professionnel'));

	if (!isset($_POST['json'])) {
		echo '<option value="">--Choisir une discipline--</option>';

		foreach($res as $key => $val) {
			echo '<option value="'.$val['disc_id'].'">'.$val['disc_name'].'</option>';
		}
	} else {
		echo json_encode($res);
	}
}