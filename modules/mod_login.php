<?php

use App\bdd;
use App\Session;
use App\Users;

/** @var bdd $db */
/** @var array $_PAGE */

$resetPassword = $_GET['reset_token'] ?? null;

## Déconnexion
if (isset($_PAGE['request'][0]) && $_PAGE['request'][0] === 'logout') {
	Users::logout();
    session_destroy();
	header('Location: '.mkurl(array('val'=>1)));
	exit;
}

if (P_LOGGED === true) {
	header('Location:'.mkurl(array('val'=>34)));
	exit;
}

## Mdp oublié
if (isset($_POST['recover_email'])) {
    $email = $_POST['recover_email'];

    $user = $db->row('SELECT %user_id, %user_name, %user_email, %user_confirm FROM %%users WHERE %user_email = :mail', array('mail' => $email));
    if (isset($user['user_id'])) {
        $confirm = bin2hex(random_bytes(32));
        $db->noRes('UPDATE %%users SET %user_confirm = :confirm WHERE %user_id = :id', array('id' => $user['user_id'], 'confirm' => $confirm));

        $mailTxt = '<p style="text-align: center;">Bonjour {user_name}, le maître de jeu <strong>{cp_mj}</strong> vous a invité dans sa campagne !</p>
<p style="text-align: center;">Il propose d\'inviter votre personnage <strong>{char_name}</strong> à sa campagne <strong>{cp_name}</strong></p>
<p style="text-align: center;">Si vous souhaitez confirmer cette invitation, et rejoindre une partie des <strong>Ombres d\'Esteren</strong>, veuillez cliquer sur ce lien : {link}</p>
<p style="text-align: center;">À bientôt sur Corahn-Rin !</p>';

        send_mail(array('name' => $user['user_name'], 'mail' => $user['user_email']), 'Réinitialiser le mot de passe', '
        Pour réinitialiser votre mot de passe, cliquez sur ce lien:<br>
        <a href="'.mkurl(array('val'=>$_PAGE['id'], 'get'=>array('reset_token' => $confirm))).'">Réinitialiser mon mot de passe</a>
        ');
    }
    Session::setFlash('Si cette adresse e-mail est associée à un compte, celle-ci va recevoir un lien pour réinitialiser le mot de passe.', 'success');
    return;
}

## Reset mdp
if ($resetPassword) {
    $user = $db->row('SELECT %user_id, %user_name, %user_email, %user_confirm FROM %%users WHERE %user_confirm = :token', array('token' => $resetPassword));
    if (!$user) {
        ?><p class="alert"><?php tr('Cet utilisateur n\'existe pas.'); ?></p><?php
        return;
    }
    if (isset($_POST['password'])) {
        $password = Users::pwd($_POST['password']);
        if ($db->noRes('UPDATE %%users SET %user_password = :user_password, %user_confirm = "" WHERE %user_id = :id', array('id' => $user['user_id'], 'user_password' => $password))) {
            Session::setFlash('Mot de passe modifié !', 'success');
        } else {
            Session::setFlash('Une erreur inconnue est survenue lors de la modification du mot de passe...', 'error');
        }
        redirect(array('val' => 1));
    }
}

## Connexion
if (isset($_POST['username'], $_POST['password'])) {

    if (Session::read('_token') !== Session::read('tokenToCheck')) {
        Session::setFlash('Une erreur est survenue dans l\'envoi du formulaire, veuillez réessayer', 'error');
    } else {

        $user = $db->row(
            'SELECT %user_id FROM %%users WHERE %user_name = :name AND %user_password = :pwd',
            array('name'=>$_POST['username'],'pwd'=>Users::pwd($_POST['password']))
        );
        if ($user) {
            $_SESSION['user'] = $user['user_id'];
            Session::delete('tokenToCheck');
            if (isset($_GET['redirect']) && $_GET['redirect'] && url_exists($_GET['redirect'])) {
                redirect($_GET['redirect']);
            } else {
                redirect(array('val'=>34));
            }
        } else {
            $_SESSION['user'] = 0;
            if ($_POST['username'] && !$_POST['password']) {
                Session::setFlash('Veuillez entrer le mot de passe.', 'error');
            } elseif ($_POST['username'] && $_POST['password']) {
                Session::setFlash('Le nom d\'utilisateur ou le mot de passe est incorrect.', 'error');
            } elseif (!$_POST['username']) {
                Session::setFlash('Veuillez entrer un nom d\'utilisateur.', 'error');
            }
        }
        unset($user);
    }
}

Session::write('tokenToCheck', Session::read('_token'));

if ($resetPassword) {
    ?>
    <div class="container">
        <form id="debugmode" action="<?php echo mkurl(array('val'=>$_PAGE['id'],'get'=>$_GET)); ?>" method="post">
            <fieldset>
                <h3><?php tr('Réinitialiser mon mot de passe'); ?></h3>
                <div class="ib w220">
                    <label for="m"><?php tr('Adresse email'); ?></label>
                    <input type="text" id="m" value="<?php echo $user['user_email'] ?? ''; ?>" disabled="disabled" />
                </div>
                <div class="ib w220">
                    <label for="password"><?php tr('Mot de passe'); ?></label>
                    <input type="password" id="password" name="password" />
                </div>
                <div>
                    <input type="submit" class="btn debsend" value="Réinitialiser" />
                </div>
            </fieldset>
        </form>
    </div>
<?php
} elseif (P_LOGGED === false) { ?>
	<div class="container">
		<form id="debugmode" action="<?php echo mkurl(array('val'=>$_PAGE['id'],'get'=>$_GET)); ?>" method="post">
			<fieldset>
				<h3><?php tr('Connexion'); ?></h3>
				<div class="ib w220">
					<label for="username"><?php tr('Nom d\'utilisateur'); ?></label>
					<input type="text" id="username" name="username" placeholder="<?php echo htmlspecialchars(tr('Nom d\'utilisateur', true)); ?>" <?php echo isset($_POST['username']) ? 'value="'.$_POST['username'].'"' : ''?> />
				</div>
				<div class="ib w220">
					<label for="password"><?php tr('Mot de passe'); ?></label>
					<input type="password" id="password" name="password" placeholder="*********" />
				</div>
				<div>
					<input type="submit" class="btn debsend" value="Envoyer" />
				</div>
			</fieldset>
		</form>
		<div class="center">
			<p><?php tr("Vous n'êtes pas inscrit(e) ?"); ?></p>
			<p><?php echo mkurl(array('val'=>56, 'type' => 'tag', 'anchor' => 'Créez un compte !', 'trans' => true, 'attr' => 'class="btn btn-link"')); ?></p>
		</div>
        <p class="center">
            <a id="lostpassword" class="btn btn-link ib"><?php tr("Mot de passe oublié ?"); ?></a>
        </p>
        <form id="recoverpassword" action="<?php echo mkurl(array('val'=>$_PAGE['id'],'get'=>$_GET)); ?>" method="post" style="display: none;">
            <fieldset>
                <h3><?php tr('Réinitialiser mon mot de passe'); ?></h3>
                <div class="ib w220">
                    <label for="email"><?php tr('Adresse email'); ?></label>
                    <input type="text" id="email" name="recover_email" />
                </div>
                <div>
                    <input type="submit" class="btn debsend" value="Envoyer" />
                </div>
            </fieldset>
        </form>
	</div>
	<?php
}

buffWrite('css', /** @lang CSS */ <<<CSSFILE
#debugmode, #recoverpassword {
    text-align: center;
}
CSSFILE
);
buffWrite('js', /** @lang JavaScript */ <<<JSFILE

$('#lostpassword').on('click', function(){
    $('#recoverpassword').slideDown(400);
    $(this).slideUp(400);
});

JSFILE
);
