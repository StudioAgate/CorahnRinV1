<?php

use App\EsterenChar;
use App\Session;
use App\Users;

global $_PAGE;
/** @var string $page_mod */

$_PAGE['dont_log'] = true;

$char = null;
try {
    $char = new Esterenchar($_SESSION, 'session');
    $char->id(session_id());
} catch (Exception $error) {
    ?>
    <p class="alert alert-danger">
        <?php echo $error->getMessage(); ?>
        <?php tr('Veuillez retourner aux étapes précédentes concernées pour régler le problème.'); ?>
    </p>
    <?php
}

$user_id = 0;
$form = false;

##Création du compte
if (isset($_POST['assoc'])) {
    if ($_POST['assoc'] === 'yes') {
        $user_id = Users::$id;
    }
    $form = true;
} elseif (!empty($_POST)) {
    Session::setFlash('Veuillez indiquer si vous souhaitez associer ce personnage à votre compte.', 'error');
}

if(isset($char) && $form === true) {
    $isValid = true;
    $validationErrors = [];

    if (!\trim($char->name())) {
        $isValid = false;
        $validationErrors[] = 'Le personnage doit avoir un nom. Retournez à l\'étape précédente pour le mettre à jour.';
    }

    if ($isValid) {
        $saved = $char->export_to_db($user_id);
        if ($saved === true) {
            EsterenChar::session_clear();
            if (!$user_id) {
                Session::setFlash('Le personnage a été correctement enregistré ! Il ne sera associé à aucun utilisateur.', 'success');
            } else {
                Session::setFlash('Le personnage a été correctement enregistré ! Il sera désormais associé à votre compte.', 'success');
            }
            /** @var \App\bdd $db */
            global $db;
            $char_id = $db->last_id();
            redirect(mkurl(['val'=>47,'params'=> [$char->id()]]));
            exit;
        }
    } else {
        foreach ($validationErrors as $error) {
            Session::setFlash($error, 'error');
        }
    }
}

if (isset($char)) {
    $sheets = $char->export_to_img();
    foreach ($sheets as $k => $sheet) {
        $sheets[$k] = mkurl_to_client_url($sheet, false);
    }

    ?>
        <div class="row-fluid">
            <div class="span4">
                <h4><?php tr("Voir la feuille de personnage"); ?></h4>
                <a href="<?php echo $sheets[0]; ?>" class="btn-block btn pageview" target="_blank"><?php tr("Voir la page"); ?> 1</a>
                <a href="<?php echo $sheets[1]; ?>" class="btn-block btn pageview" target="_blank"><?php tr("Voir la page"); ?> 2</a>
                <a href="<?php echo $sheets[2]; ?>" class="btn-block btn pageview" target="_blank"><?php tr("Voir la page"); ?> 3</a>
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
                    </fieldset>
                </form>
            </div>
        </div>
    <?php
}

buffWrite('css', /** @lang CSS */ <<<CSSFILE
CSSFILE
, $page_mod);
buffWrite('js', /** @lang JavaScript */ <<<JSFILE
	$(document).ready(function(){
		$('#form_create').click(function(){ $('#formgen').submit(); });
		//$(".pageview").click(function() { return !window.open(this.href); });
	});
JSFILE
, $page_mod);
