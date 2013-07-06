<?php

## Déconnexion
if (isset($_PAGE['request'][0]) && $_PAGE['request'][0] == 'logout') {
	Users::logout();
	header('Location: '.mkurl(array('val'=>1)));
	exit;
}

if (P_LOGGED === true) {
	header('Location:'.mkurl(array('val'=>34)));
	exit;
}

## Connexion
if (isset($_POST['nickname']) && isset($_POST['password'])) {
	$user = $db->row(
		'SELECT %user_id FROM %%users WHERE %user_name = :name AND %user_password = :pwd',
		array('name'=>$_POST['nickname'],'pwd'=>Users::pwd($_POST['password']))
	);
	if ($user) {
		$_SESSION['user'] = $user['user_id'];
		if (isset($_GET['redirect']) && $_GET['redirect'] && url_exists($_GET['redirect'])) {
			redirect($_GET['redirect']);
		} else {
			redirect(array('val'=>34));
		}
	} else {
		$_SESSION['user'] = 0;
		if ($_POST['nickname'] && !$_POST['password']) {
			Session::setFlash('Veuillez entrer le mot de passe.', 'error');
		} elseif ($_POST['nickname'] && $_POST['password']) {
			Session::setFlash('Le nom d\'utilisateur ou le mot de passe est incorrect.', 'error');
		} elseif (!$_POST['nickname']) {
			Session::setFlash('Veuillez entrer un nom d\'utilisateur.', 'error');
		}
	}
	unset($user);
}


if (P_LOGGED === false) { ?>
	<div class="container">
		<form id="debugmode" action="<?php echo mkurl(array('val'=>$_PAGE['id'],'get'=>$_GET)); ?>" method="post">
			<fieldset>
				<h3><?php tr('Connexion'); ?></h3>
				<div class="ib w220">
					<label for="nickname"><?php tr('Nom d\'utilisateur'); ?></label>
					<input type="text" id="nickname" name="nickname" <?php echo isset($_POST['nickname']) ? 'value="'.$_POST['nickname'].'"' : ''?> />
				</div>
				<div class="ib w220">
					<label for="password"><?php tr('Mot de passe'); ?></label>
					<input type="password" id="password" name="password" />
				</div>
				<div>
					<input type="submit" class="btn debsend" value="Envoyer" />
				</div>
			</fieldset>
		</form>
		<div class="center"><p><?php tr("Vous n'êtes pas inscrit(e) ?"); ?></p><p><?php echo mkurl(array('val'=>56, 'type' => 'tag', 'anchor' => 'Créez un compte !', 'attr' => 'class="btn btn-link"')); ?></div>
	</div>
	<?php
}

	buffWrite('css', <<<CSSFILE
	#debugmode {
		text-align: center;
	}
CSSFILE
);
	buffWrite('js', <<<JSFILE

JSFILE
);