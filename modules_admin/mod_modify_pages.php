<?php

if (Users::$acl === 0) {
	$orderby = isset($_PAGE['request']['orderby']) ? $_PAGE['request']['orderby'] : '';
	switch($orderby) {
		case 'id': case 'getmod': case 'step': case 'anchor':case 'acl':
			$orderby = '%page_'.$orderby;
			break;
		default:
			$orderby = '%page_id';
			break;
	}
	$order = isset($_PAGE['request']['sort']) ? $_PAGE['request']['sort'] : '';
	switch($order) {
		case 'asc': case 'desc':
			break;
		default:
			$order = 'asc';
			break;
	}

	$idmodif = isset($_PAGE['request']['mod']) ? $_PAGE['request']['mod'] : (isset($_POST['idmodif']) ? $_POST['idmodif'] : 0);
	$vmodif = $db -> row('SELECT * FROM %%pages WHERE %page_id = ? LIMIT 0,1', array($idmodif));
	unset($idmodif);
	$vname = isset($vmodif['page_getmod']) ? $vmodif['page_getmod'] : '';
	$vanchor = isset($vmodif['page_anchor']) ? $vmodif['page_anchor'] : '';
	$vcheck = isset($vmodif['page_show_in_menu']) ? $vmodif['page_show_in_menu'] : '';
	$vacl = isset($vmodif['page_acl']) ? $vmodif['page_acl'] : '';
	$vadmin = isset($vmodif['page_show_in_debug']) ? $vmodif['page_show_in_debug'] : '';
	$vlogin = isset($vmodif['page_require_login']) ? $vmodif['page_require_login'] : '';
	if (isset($_POST['suppr'])) {
		$db -> noRes('DELETE FROM %%pages WHERE %page_id = ?', array($_POST['idmodif']));
		@unlink(ROOT.DS.'modules'.DS.'mod_'.$_POST['name'].'.php');
		@unlink(ROOT.DS.'webroot'.DS.'js'.DS.'pages'.DS.'pg'.$_POST['name'].'.js');
		@unlink(ROOT.DS.'css'.DS.'pages'.DS.'pg'.$_POST['name'].'.css');
		$vname = $vanchor = $vstep = $vcheck = $vadmin = $vacl = '';
		redirect(array(), 'Suppression effectuée !', 'success');
	}
	if (isset($_POST['send'])) {

		$sql = ' %%pages
			SET %page_getmod = :name,
			%page_anchor = :anchor,
			%page_show_in_menu = :show_in_menu,
			%page_acl = :acl,
			%page_show_in_debug = :debug,
			%page_require_login = :login
		';
		$datas = array(
			':name' => $_POST['name'],
			':anchor' => $_POST['anchor'],
			':show_in_menu' => isset($_POST['show_in_menu']) ? '1' : '0',
			':acl' => $_POST['acl'],
			':debug' => isset($_POST['show_in_debug']) ? '1' : '0',
			':login' => isset($_POST['require_login']) ? '1' : '0',
		);
		if (isset($_POST['idmodif'])) {

			$sql = 'UPDATE '.$sql.' WHERE %page_id = :idmodif ';
			$datas[':idmodif'] = $_POST['idmodif'];

			$db->noRes($sql, $datas);
			//Rename du fichier css
			if (file_exists(ROOT.DS.'css'.DS.'pages'.DS.'pg_'.$vmodif['page_getmod'].'.css')) {
				rename(		ROOT.DS.'webroot'.DS.'css'.DS.'pages'.DS.'pg_'.$vmodif['page_getmod'].'.css',
							ROOT.DS.'webroot'.DS.'css'.DS.'pages'.DS.'pg_'.$_POST['name'].'.css');
			}
			//Rename du fichier js
			if (file_exists(ROOT.DS.'js'.DS.'pages'.DS.'pg_'.$vmodif['page_getmod'].'.js')) {
				rename(		ROOT.DS.'webroot'.DS.'js'.DS.'pages'.DS.'pg_'.$vmodif['page_getmod'].'.js',
							ROOT.DS.'webroot'.DS.'js'.DS.'pages'.DS.'pg_'.$_POST['name'].'.js');
			}
			if (is_dir(	ROOT.DS.'modules_'.$vmodif['page_getmod'])) {
				rename(	ROOT.DS.'modules_'.$vmodif['page_getmod'],
						ROOT.DS.'modules_'.$_POST['name']);
			}
			//Rename du fichier module php
			rename(	ROOT.DS.'modules'.DS.'mod_'.$vmodif['page_getmod'].'.php',
					ROOT.DS.'modules'.DS.'mod_'.$_POST['name'].'.php');
			$vname = $vanchor = $vstep = $vcheck = $vadmin = $vacl = '';
			redirect(array(), 'Modification effectuée !', 'success');
		} else {
			$sql = 'INSERT INTO '.$sql;
			$db->noRes($sql, $datas);
			file_put_contents(ROOT.DS.'pages'.DS.'mod_'.$_POST['name'].'.php', P_TPL_BASEMOD);
			file_put_contents(ROOT.DS.'webroot'.DS.'js'.DS.'pages'.DS.'pg_'.$_POST['name'].'.js', '');
			file_put_contents(ROOT.DS.'webroot'.DS.'css'.DS.'pages'.DS.'pg_'.$_POST['name'].'.css', '');
			redirect(array(), 'Insertion effectuée !', 'success');
		}
	}
	unset($sql, $vmodif);

	$pglist = $db -> req('SELECT * FROM %%pages ORDER BY '.$orderby.' '.$order.($orderby != '%page_id' ? ' , %page_id ASC ' : ''));
	unset($orderby);
	?>
	<section class="debugger">
		<h2>Modifier ou supprimer des pages</h2>
		<form id="createlink" action="<?php echo mkurl(array('params'=>array('modify_pages'))); ?>" method="post">
			<fieldset>
				<?php if (isset($_PAGE['request']['mod'])) { ?><input type="hidden" name="idmodif" id="idmodif" value="<?php echo $_PAGE['request']['mod']; ?>" /><?php } ?>

				<div class="row-fluid">
					<div class="span4">
						<label for="name">Nom dans l'url</label>
						<input type="text" name="name" id="name" placeholder="Nom de la page" value="<?php echo $vname; ?>" />
					</div>

					<div class="span4">
						<label for="anchor">Ancre/Titre de la page</label>
						<input type="text" name="anchor" id="anchor" placeholder="Ancre de la page" value="<?php echo $vanchor; ?>"/>
					</div>

					<div class="span4">
						<label for="acl">Gestion des droits</label>
						<input type="text" name="acl" id="acl" placeholder="Gestion des droits" value="<?php echo $vacl; ?>" />
					</div>
				</div>

				<div class="row-fluid">
					<div class="span4">
						<label for="show_in_menu">Voir dans le menu</label>
						<input type="checkbox" name="show_in_menu" id="show_in_menu" <?php echo $vcheck ? 'checked="checked"' : ''; ?> />
					</div>
					<div class="span4">
						<label for="show_in_debug">Voir dans l'administration</label>
						<input type="checkbox" name="show_in_debug" id="show_in_debug" <?php echo $vadmin ? 'checked="checked"' : ''; ?> />
					</div>
					<div class="span4">
						<label for="require_login">Connexion requise</label>
						<input type="checkbox" name="require_login" id="require_login" <?php echo $vlogin ? 'checked="checked"' : ''; ?> />
					</div>
				</div>

				<div class="row-fluid mt10">
					<div class="span6">
						<input type="submit" name="send" value="Envoyer" class="btn ib" />
					</div>
					<div class="span6">
						<input type="submit" name="suppr" value="Supprimer" class="btn ib" />
					</div>
				</div>
			</fieldset>
		</form>
		<ul class="unstyled inline"><?php
		unset($vname,$vcheck,$vadmin,$vacl,$vanchor,$vstep,$vlogin);

			$order = $order == 'asc' ? 'desc' : 'asc';
			$output = '
				<li class="bl"><span class="btn btn-small btn-block btn-link listlinks">'
					.mkurl(array('type' => 'TAG', 'anchor' => 'Id', 'attr' => 'class="ib pageid"', 'params' => array('sort' => $order,'orderby'=>'id')))
					.mkurl(array('type' => 'TAG', 'anchor' => 'ACL', 'attr' => 'class="ib pageacl"', 'params' => 	array('sort' => $order,'orderby'=>'acl')))
					.'<a href="#" class="ib pageshow">Dans le menu</a>'
					.'<a href="#" class="ib pageadmin">Dans l\'admin</a>'
					.mkurl(array('type' => 'TAG', 'anchor' => 'Login requis', 'attr' => 'class="ib pagelogin"', 'params' => 	array('sort' => $order,'orderby'=>'login')))
					.mkurl(array('type' => 'TAG', 'anchor' => "Nom dans l'url (getmod)", 'attr' => 'class="ib pagegetmod"', 'params' => array('sort' => $order,'orderby'=>'getmod')))
					.mkurl(array('type' => 'TAG', 'anchor' => 'Ancre et titre', 'attr' => 'class="ib pageanchor"', 'params' => array('sort' => $order,'orderby'=>'anchor')));
			foreach ($pglist as $key => $val) {
				$anchor =
					'<span class="ib pageid">'.$val['page_id'].'</span>'
					.'<span class="ib pageacl">'.$val['page_acl'].'</span>'
					.'<span class="ib pageshow">'.($val['page_show_in_menu'] ? '<span class="icon-green icon-ok"></span>' : '<span class="icon-red icon-remove"></span>').'</span>'
					.'<span class="ib pageadmin">'.($val['page_show_in_debug'] ? '<span class="icon-green icon-ok"></span>' : '<span class="icon-red icon-remove"></span>').'</span>'
					.'<span class="ib pagelogin">'.($val['page_require_login'] ? '<span class="icon-green icon-ok"></span>' : '<span class="icon-red icon-remove"></span>').'</span>'
					.'<span class="ib pagegetmod">'.$val['page_getmod'].'</span>'
					.'<span class="ib pageanchor">'.$val['page_anchor'].'</span>';
				$output .= '
				<li class="bl">'.mkurl(array('type' => 'TAG', 'anchor' => $anchor, 'attr' => 'class="btn btn-mini btn-block centerlinks btn-link"', 'params' => 	array('mod'=>$val['page_id']))).'</li>';
			}
			echo $output;
			unset($pglist, $key, $val, $anchor, $output, $order);
			?>

		</ul>
	</section>
	<?php

	$char_etat = array('Session actuelle : ' => $_SESSION);
	echo '<section id="err" class="debugger">', p_dump($char_etat), '</section>';
	unset($char_etat);
}//endif Users acl == 0
