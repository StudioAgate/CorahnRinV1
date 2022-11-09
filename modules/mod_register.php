<?php

use App\Session;
use App\Users;

if (isset($_POST['name'], $_POST['email'], $_POST['password']) && !empty($_POST)) {

    if (($_PAGE['referer']['id'] !== $_PAGE['id']) || (Session::read('_token') !== Session::read('tokenToCheck'))) {
        Session::setFlash('Une erreur est survenue dans l\'envoi du formulaire, veuillez réessayer', 'error');
        httpCode(403);
    } else {

        $err = '';
        if (!$_POST['name']) { $err .= ''.tr('Le nom d\'utilisateur doit être renseigné', true).'<br />'; }
        if (!$_POST['password']) { $err .= ''.tr('Entrez un mot de passe', true).'<br />'; }
        if (!$_POST['email'] || !preg_match(P_MAIL_REGEX, $_POST['email'])) { $err .= ''.tr('Entrez une adresse email correcte', true).''; }

        if ($err !== '') {
            Session::setFlash($err, 'error');
        } else {
            $data = array(
                'name' => $_POST['name'],
                'password' => $_POST['password'],
                'email' => $_POST['email'],
                'status' => 0,
                'confirm' => md5($_POST['name'].uniqid(preg_replace('#[^a-z_]+#iUu', '', $_POST['name']), true)),
            );

            Session::delete('tokenToCheck');
            $create = Users::create($data);
            if ($create === false) {
                Session::setFlash($err, 'error');
            } elseif (!empty($_GET['redirect']) && url_exists($_GET['redirect'])) {
                redirect($_GET['redirect']);
            } else {
                redirect(array('val'=>34));
            }
        }
    }
}

if (P_LOGGED === true) {
	redirect(array('val'=>34));
}

Session::write('tokenToCheck', Session::read('_token'));

?>

<div class="container">
	<h2><?php tr('Inscription'); ?></h2>
	<div class="info"><?php
		echo tr('Inscrivez-vous dès maintenant pour pouvoir avoir accès en permanence à vos personnages !', true),
		'<br />',
		tr('Vous avez déjà un compte ?', true),
		'<br />',
		mkurl(array('val'=>48, 'type'=>'tag', 'attr'=>array('class'=>'btn btn-info','style'=>'color:white;'), 'anchor' => 'Connectez-vous !'));
	?></div>
	<form id="register_form" class="bl mid" action="<?php echo mkurl(array('val'=>$_PAGE['id'])); ?>" method="post">
		<fieldset>

			<div class="first">
				<label class="ib mid" for="name"><?php tr("Nom d'utilisateur"); ?></label>
				<input class="ib mid" type="text" id="name" name="name" />
			</div>

			<div class="form_row">
				<label class="ib mid" for="password"><?php tr('Mot de passe'); ?></label>
				<input class="ib mid" type="password" id="password" name="password" />
			</div>

			<div class="form_row">
				<label class="ib mid" for="email"><?php tr('Adresse email'); ?></label>
				<input class="ib mid" type="text" id="email" name="email" />
			</div>

			<div class="form_row submit">
				<input type="submit" class="btn" id="send" value="<?php tr('Envoyer'); ?>" />
			</div>
		</fieldset>
	</form>
</div>

<?php
	buffWrite('css', /** @lang CSS */ <<<CSSFILE
		#register_form { width: 500px; }
		#register_form label { width: 200px; }
		#register_form label:hover { cursor: pointer; }
		#register_form .form_row.submit { text-align: center; }
		#register_form .first { margin-top: 15px; }
CSSFILE
);

 	buffWrite('js', '');
