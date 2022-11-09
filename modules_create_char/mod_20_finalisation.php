<?php

/** @var array $_PAGE */

use App\EsterenChar;
use App\Session;
use App\Users;

$_PAGE['dont_log'] = true;

$char = new Esterenchar($_SESSION, 'session');

$char->id(session_id());

$user_id = 0;
$form = false;

##Création du compte
if (isset($_POST['assoc'])) {
	if ($_POST['assoc'] == 'yes') {
		$user_id = Users::$id;
	}
	$form = true;
} elseif (!empty($_POST)) {
	Session::setFlash('Veuillez indiquer si vous souhaitez associer ce personnage à votre compte.', 'error');
}

if($form === true) {
$saved = $char->export_to_db($user_id);
	if ($saved === true) {
		EsterenChar::session_clear();
		if (!$user_id) {
			Session::setFlash('Le personnage a été correctement enregistré ! Il ne sera associé à aucun utilisateur.', 'success');
		} else {
			Session::setFlash('Le personnage a été correctement enregistré ! Il sera désormais associé à votre compte.', 'success');
		}
        $char_id = $db->last_id();
		header('Location:'.mkurl(array('val'=>47,'params'=>array($char->id()))));
		exit;
	}
}

$sheets = $char->export_to_img();
foreach ($sheets as $k => $sheet) {
    $sheets[$k] = mkurl_to_client_url($sheet, false);
}

?>
	<div class="row-fluid">
		<div class="span4">
			<h4><?php tr("Voir la feuille de personnage"); ?></h4>
			<a href="<?php echo $sheets[0]; ?>" class="btn-block btn pageview"><?php tr("Voir la page"); ?> 1</a>
			<a href="<?php echo $sheets[1]; ?>" class="btn-block btn pageview"><?php tr("Voir la page"); ?> 2</a>
			<a href="<?php echo $sheets[2]; ?>" class="btn-block btn pageview"><?php tr("Voir la page"); ?> 3</a>
		</div>
		<div class="span8">
			<form action="<?php echo mkurl(array('params' => $page_mod)); ?>" id="form_create" method="post">
				<fieldset>
					<input type="submit" class="btn btn-success btn-large" id="createchar" value="<?php tr('Créer le personnage'); ?>" />
					<div>
					<?php if (P_LOGGED === true) { ?>
						<p><?php tr('Bonjour');echo ', ',Users::$name.', '; tr('voulez-vous associer ce personnage à votre compte ?'); ?></p>
						<div><label class="ib" for="assoc"><?php tr('Oui'); ?> </label> <input class="ib" type="radio" name="assoc" id="assoc" value="yes" /></div>
						<div><label class="ib" for="no_assoc"><?php tr('Non'); ?> </label> <input class="ib" type="radio" name="assoc" id="no_assoc" value="no" /></div>
					<?php } else {
						?>
						<input type="hidden" name="assoc" value="no" />
						<p class="info"><?php tr("Vous n'êtes <strong>pas connecté(e)</strong>. Si vous créez le personnage, il sera supprimé au bout d'un certain temps."); ?></p>
						<p class="well well-small">
						<?php echo mkurl(array('val'=>56,'type'=>'tag','anchor'=>'Inscrivez-vous','get'=>array('redirect'=>mkurl(array('params'=>$_PAGE['request']))), 'attr'=>array('class'=>'btn'))); ?>
						<?php tr('ou'); ?>
						<?php echo mkurl(array('val'=>48,'type'=>'tag','anchor'=>'Connectez-vous','get'=>array('redirect'=>mkurl(array('params'=>$_PAGE['request']))), 'attr'=>array('class'=>'btn'))); ?>
						<?php tr('pour conserver votre personnage.'); ?></p>
					<?php } ?>
					</div>
					<!--
					<div class="formcontainers">
						<?php if (P_LOGGED === true) { ?><div class="info"><?php tr('Vous êtes <strong>déjà connecté(e)</strong>. Si vous souhaitez associer ce personnage à votre compte, passez simplement à l\'étape suivante et laisez les champs vides.<br />En revanche, si vous souhaitez <strong>créer un nouveau compte</strong>, remplissez tous les champs avec de nouvelles valeurs.'); ?></div><?php } ?>
						<input type="hidden" id="userid" name="userid" value="<?php echo P_LOGGED === true ? Users::$id : '0'; ?>" />
						<label for="create_name"><?php tr("Nom d'utilisateur"); ?></label>
						<input class="span" type="text" id="create_name" name="create_name" />

						<label for="create_password"><?php tr('Mot de passe'); ?></label>
						<input class="span" type="password" id="create_password" name="create_password" />

						<label for="create_email"><?php tr('Adresse email'); ?></label>
						<input class="span" type="text" id="create_email" name="create_email" />
					</div>
					-->
				</fieldset>
			</form>
		</div>
	</div>
<?php
buffWrite('css', /** @lang CSS */ <<<CSSFILE
	div[class*=span]:hover { cursor: default; }
	#indicate_all { display: none; }
CSSFILE
, $page_mod);
buffWrite('js', /** @lang JavaScript */ <<<JSFILE
	$(document).ready(function(){
		$('#form_create').click(function(){ $('#formgen').submit(); });
		$(".pageview").click(function() { return !window.open(this.href); });
	});
JSFILE
, $page_mod);
