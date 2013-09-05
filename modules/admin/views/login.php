<?php
if (P_LOGGED === false) { ?>
	<div class="container">
		<form id="debugmode" action="" method="post">
			<fieldset>
				<h3><?php tr('Connexion'); ?></h3>
				<div class="ib w220">
					<label for="nickname"><?php tr('Nom d\'utilisateur'); ?></label>
					<input type="text" id="nickname" name="nickname" <?php echo isset($post['nickname']) ? 'value="'.$post['nickname'].'"' : ''?> />
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
		<div class="center">
			<p><?php tr("Vous n'êtes pas inscrit(e) ?"); ?></p>
			<p><?php echo Router::link(array('route_name'=>'core_register', 'force_route'=>'true', 'type'=>'tag', 'attr'=>array('class'=>'btn btn-link'), 'anchor' => 'Créez un compte !', 'translate' => true))?></p>
		</div>
	</div>
	<?php
}