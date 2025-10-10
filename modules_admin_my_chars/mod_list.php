<?php

use App\bdd;
use App\Users;

/** @var bdd $db */

$characters = $db->req('SELECT %char_name,%char_id FROM %%characters WHERE %deleted_at IS NULL AND %user_id = ?', array(Users::$id));

if (is_array($characters) && !empty($characters)) { ?>
	<h4><?php tr('Voici la liste de vos personnages'); ?></h4>
	<ul><?php
		foreach($characters as $v) { ?>
			<li>
				<?php echo mkurl(array('type' => 'tag', 'anchor' => 'Supprimer', 'trans' => true, 'attr' => array('class'=>'btn btn-mini btn-danger','style'=>'color: #fff'), 'params' => array($v['char_id'], 'delete'))); ?> &ndash;
				<?php echo mkurl(array('type' => 'tag', 'anchor' => 'Modifier', 'trans' => true, 'attr' => 'class="btn btn-mini"', 'params' => array($v['char_id']))); ?> &ndash;
				<?php echo mkurl(array('val' => 63, 'type' => 'tag', 'trans' => true, 'attr' => 'class="btn btn-mini"', 'params' => array($v['char_id']))); ?> &ndash;
				<?php echo mkurl(array('val' => 47, 'type' => 'tag', 'anchor' => $v['char_name'], 'attr' => '', 'params' => array($v['char_id']))); ?>
			</li>
		<?php } ?>
	</ul>
<?php
} else {
	?>
    <p class="alert alert-info">
        <?php tr('Aucun personnage'); ?>
    </p>
    <?php
}
unset($characters, $v);
