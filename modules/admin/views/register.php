
<div class="container">
	<h2><?php tr('Inscription'); ?></h2>
	<div class="info"><?php
		echo tr('Inscrivez-vous dès maintenant pour pouvoir avoir accès en permanence à vos personnages !', true),
		'<br />',
		tr('Vous avez déjà un compte ?', true),
		'<br />',
		Router::link(array('type'=>'tag', 'route_name'=>'core_login', 'force_route'=>true, 'anchor'=>'Connectez-vous !', 'attr'=>array('class'=>'btn btn-info text-white')));;
	?></div>
	<form id="register_form" class="bl mid" action="" method="post">
		<fieldset>

			<div class="first">
				<label class="ib mid" for="name"><?php tr("Nom d'utilisateur"); ?></label>
				<input class="ib mid" type="text" id="name" name="name" value="<?php echo $this->controller->post('name'); ?>" />
			</div>

			<div class="form_row">
				<label class="ib mid" for="password"><?php tr('Mot de passe'); ?></label>
				<input class="ib mid" type="password" id="password" name="password" value="<?php echo $this->controller->post('password'); ?>" />
			</div>

			<div class="form_row">
				<label class="ib mid" for="email"><?php tr('Adresse email'); ?></label>
				<input class="ib mid" type="text" id="email" name="email" value="<?php echo $this->controller->post('email'); ?>" />
			</div>

			<div class="form_row submit">
				<input type="submit" class="btn" id="send" value="<?php tr('Envoyer'); ?>" />
			</div>
		</fieldset>
	</form>
</div>