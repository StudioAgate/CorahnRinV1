<?php
class DbconvertController extends Controller {

	function index_action(){


		$new = new Database('127.0.0.1', 'root', '', 'corahnrin', '');
		$old = new Database('127.0.0.1', 'root', '', 'esteren', 'est_');

		$tables = $old->req('SHOW TABLES');
		$tables_done = array();

		foreach ($tables as $v) {
			$t = array_values($v);
			$t = $t[0];
			$t = str_replace('est_', '', $t);
			$$t = $old->req('SELECT * FROM %%'.$t);
		}

		unset($t,$q,$v);
		$glob = get_defined_vars();
		foreach ($glob as $k => $v) { if (strpos($k, '_') === 0) { unset($glob[$k]); } }
		// pr($glob);
		pr(implode(',', array_keys($glob)));

		$table = 'weapons';echo '<br />';
		if (!$new->req('SHOW COLUMNS FROM %'.$table.' LIKE "date_created"')) { $new->noRes('ALTER TABLE %'.$table.' ADD `date_created` INT UNSIGNED NOT NULL'); echo 'Added `'.$table.'` date_created column - '; }
		if (!$new->req('SHOW COLUMNS FROM %'.$table.' LIKE "date_updated"')) { $new->noRes('ALTER TABLE %'.$table.' ADD `date_updated` INT UNSIGNED NOT NULL'); echo 'Added `'.$table.'` date_updated column - '; }
		foreach ( $armes as $v) {
			$datas = array(
					'id' => $v['arme_id'],
					'name' => $v['arme_name'],
					'dmg' => $v['arme_dmg'],
					'price' => $v['arme_prix'],
					'availability' => $v['arme_dispo'],
					'range' => $v['arme_range'],
					'contact' => (strpos($v['arme_domain'], '2') !== false ? 1 : 0),
			);$new->noRes('REPLACE INTO %'.$table.' SET %%%fields', $datas);
		}$tables_done[]=$table;

		$table = 'armors';echo '<br />';
		if (!$new->req('SHOW COLUMNS FROM %'.$table.' LIKE "date_created"')) { $new->noRes('ALTER TABLE %'.$table.' ADD `date_created` INT UNSIGNED NOT NULL'); echo 'Added `'.$table.'` date_created column - '; }
		if (!$new->req('SHOW COLUMNS FROM %'.$table.' LIKE "date_updated"')) { $new->noRes('ALTER TABLE %'.$table.' ADD `date_updated` INT UNSIGNED NOT NULL'); echo 'Added `'.$table.'` date_updated column - '; }
		foreach ( $armures as $v) {
			$datas = array(
					'id' => $v['armure_id'],
					'name' => $v['armure_name'],
					'description' => $v['armure_desc'],
					'protection' => $v['armure_prot'],
					'price' => $v['armure_prix'],
					'availability' => $v['armure_dispo'],
			);$new->noRes('REPLACE INTO %'.$table.' SET %%%fields', $datas);
		}$tables_done[]=$table;

// 		if ($new->req('SHOW COLUMNS FROM `ways` LIKE "desc"')) { $new->noRes('ALTER TABLE `ways` CHANGE `desc` `description` TEXT NOT NULL'); echo 'Changed `'.$table.'` desc column - '; }
		$table = 'ways';echo '<br />';
		if (!$new->req('SHOW COLUMNS FROM %'.$table.' LIKE "date_created"')) { $new->noRes('ALTER TABLE %'.$table.' ADD `date_created` INT UNSIGNED NOT NULL'); echo 'Added `'.$table.'` date_created column - '; }
		if (!$new->req('SHOW COLUMNS FROM %'.$table.' LIKE "date_updated"')) { $new->noRes('ALTER TABLE %'.$table.' ADD `date_updated` INT UNSIGNED NOT NULL'); echo 'Added `'.$table.'` date_updated column - '; }
		$voies_id = array();
		$voies_short = array();
		foreach ( $voies as $v) {
			$datas = array(
					'id' => $v['voie_id'],
					'name' => $v['voie_name'],
					'short_name' => $v['voie_shortname'],
					'description' => $v['voie_desc'],
					'fault' => $v['voie_travers'],
			);$new->noRes('REPLACE INTO %'.$table.' SET %%%fields', $datas);
			$voies_id[$v['voie_id']] = $v;
			$voies_short[$v['voie_shortname']] = $v;
			// 	echo '<br />REPLACE INTO `'.$table.'` (`'.implode('`,`', array_keys($datas)).'`) VALUES (\''.implode('\',\'', $datas).'\')';
		}$tables_done[]=$table;

		// $new->noRes('RENAME TABLE IF EXISTS %desordes TO %disorders');
		// $table = 'disorders';
		// foreach ( $armes as $v) {
		// 	$datas = array(
		// 			'id' => $v['desordre_id'],
		// 			'name' => $v['desordre_name'],
		// 	);$new->noRes('REPLACE INTO %'.$table.' SET %%%fields', $datas);
		// }echo 'OK for table `'.$table.'`'."\n<br />" ;

		$table = 'user_group';echo '<br />';
		if ($new->req('SHOW COLUMNS FROM %'.$table.' LIKE "id_user_group"')) { $new->noRes('ALTER TABLE %'.$table.'  CHANGE `id_user_group` `id_groups` INT( 11 ) NOT NULL'); echo 'Renamed `'.$table.'` id_user_group column - '; }

		//TODO => Faire manuellement la modif
// 		$new->noRes('RENAME TABLE %user_groups TO %groups ');
		$table = 'groups';echo '<br />';
		if (!$new->req('SHOW COLUMNS FROM %'.$table.' LIKE "acl"')) { $new->noRes('ALTER TABLE %'.$table.' ADD `acl` INT UNSIGNED NOT NULL'); echo 'Added `'.$table.'` acl column - '; }
		if (!$new->req('SHOW COLUMNS FROM %'.$table.' LIKE "description"')) { $new->noRes('ALTER TABLE %'.$table.' ADD `description` INT UNSIGNED NOT NULL'); echo 'Added `'.$table.'` description column - '; }
		$datas = array('id'=>1,'acl'=>0,'name'=>'Super administrateur','description'=>'');$new->noRes('REPLACE INTO %'.$table.' SET %%%fields', $datas);echo '<br />REPLACE INTO `'.$table.'` (`'.implode('`,`', array_keys($datas)).'`) VALUES (\''.implode('\',\'', $datas).'\')';
		$datas = array('id'=>2,'acl'=>50,'name'=>'Utilisateurs','description'=>'');$new->noRes('REPLACE INTO %'.$table.' SET %%%fields', $datas);echo '<br />REPLACE INTO `'.$table.'` (`'.implode('`,`', array_keys($datas)).'`) VALUES (\''.implode('\',\'', $datas).'\')';
		$datas = array('id'=>3,'acl'=>40,'name'=>'Utilisateurs avancés','description'=>'');$new->noRes('REPLACE INTO %'.$table.' SET %%%fields', $datas);echo '<br />REPLACE INTO `'.$table.'` (`'.implode('`,`', array_keys($datas)).'`) VALUES (\''.implode('\',\'', $datas).'\')';
		$tables_done[]=$table;

		$table = 'users';echo '<br />';
		if (!$new->req('SHOW COLUMNS FROM %'.$table.' LIKE "date_created"')) { $new->noRes('ALTER TABLE %'.$table.' ADD `date_created` INT UNSIGNED NOT NULL'); echo 'Added `'.$table.'` date_created column - '; }
		if (!$new->req('SHOW COLUMNS FROM %'.$table.' LIKE "date_updated"')) { $new->noRes('ALTER TABLE %'.$table.' ADD `date_updated` INT UNSIGNED NOT NULL'); echo 'Added `'.$table.'` date_updated column - '; }
		foreach ( $users as $v) {
			$datas = array(
					'id' => $v['user_id'],
					'name' => $v['user_name'],
					'password' => $v['user_password'],
					'email' => $v['user_email'],
					'status' => $v['user_status'],
					'confirm' => $v['user_confirm'],
			);
// 			$new->noRes('REPLACE INTO %'.$table.' SET %%%fields', $datas);
			// 	echo '<br />REPLACE INTO `'.$table.'` (`'.implode('`,`', array_keys($datas)).'`) VALUES (\''.implode('\',\'', $datas).'\')';
			if ($v['user_acl'] == 40) {
// 				$new->noRes('REPLACE INTO %user_group SET %%%fields', array('id_users'=>$v['user_id'], 'id_groups' => 3));
			} elseif ($v['user_acl'] == 50) {
// 				$new->noRes('REPLACE INTO %user_group SET %%%fields', array('id_users'=>$v['user_id'], 'id_groups' => 2));
			} elseif ($v['user_acl'] == 0) {
// 				$new->noRes('REPLACE INTO %user_group SET %%%fields', array('id_users'=>$v['user_id'], 'id_groups' => 1));
			}
		}$tables_done[]=$table;


		$table = 'traits';echo '<br />';
		$new->noRes('DROP TABLE IF EXISTS %traits_ways');
		if (!$new->req('SHOW COLUMNS FROM %'.$table.' LIKE "date_created"')) { $new->noRes('ALTER TABLE %'.$table.' ADD `date_created` INT UNSIGNED NOT NULL'); echo 'Added `'.$table.'` date_created column - '; }
		if (!$new->req('SHOW COLUMNS FROM %'.$table.' LIKE "date_updated"')) { $new->noRes('ALTER TABLE %'.$table.' ADD `date_updated` INT UNSIGNED NOT NULL'); echo 'Added `'.$table.'` date_updated column - '; }
		if (!$new->req('SHOW COLUMNS FROM %'.$table.' LIKE "ways_id"')) { $new->noRes('ALTER TABLE %'.$table.' ADD `ways_id` INT NOT NULL, CONSTRAINT `FK_traits_ways_ways_id` FOREIGN KEY (`ways_id`) REFERENCES `ways`(`id`)'); echo 'Added `'.$table.'` ways_id foreign key column - '; }
		foreach ( $traitscaractere as $v) {
			$datas = array(
					'id' => $v['trait_id'],
					'name' => $v['trait_name'],
					'name_female' => $v['trait_name_female'],
					'is_quality' => ($v['trait_qd'] === 'q' ? 1 : 0),
					'is_major' => ($v['trait_mm'] === 'maj' ? 1 : 0),
					'ways_id' => $voies_short[$v['trait_voie']]['voie_id'],
			);
// 			$new->noRes('REPLACE INTO %'.$table.' SET %%%fields', $datas);
			// 	if ($v['trait_voie'] == 'com') {
			// 		$new->noRes('REPLACE INTO %traits_ways SET %%%fields', array('id_users'=>$v['user_id'], 'id_groups' => 3));
			// 		echo '<div style="display:inline-block;width: 19%;padding:0 5px;">user <span style="color:'.P_DUMP_BOOLTRUECOLOR.'">'.$v['user_name'].'</span> to group 3, acl 40</div>';
			// 	}
		}$tables_done[]=$table;











		echo '<br /><br /><br />';
		foreach ($tables as $t) {
			if (!in_array($t, $tables_done)) {
				echo '<span style="display:inline-block;width: 15%;padding:0 5px;color:'.P_DUMP_STRINGCOLOR.'">`'.$t['Tables_in_esteren'].'`</span>';
			}
		}


		$this->rendered(true);
	}
}