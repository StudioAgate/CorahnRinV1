<?php

use App\Session;
use App\Users;

/** @var \App\bdd $db */

if (P_DEBUG === true && isset($_PAGE['request'][0]) && $_PAGE['request'][0] === 'impersonate') {
	$target = $db->row('SELECT %user_id, %user_status FROM %%users WHERE %user_id = ?', array((int) ($_PAGE['request'][1] ?? 0)));
	if (!$target || $target['user_id'] === Users::$id) {
		redirect(array('val' => 57), 'Utilisateur incorrect.', 'error');
	}
	if ($target['user_status'] === 0) {
		redirect(array('val' => 57), 'Cet utilisateur n\'a pas encore confirmé son adresse e-mail.', 'error');
	}
	if (!Session::check('IMPERSONATING_USER_ORIGIN_ID')) {
		Session::write('IMPERSONATING_USER_ORIGIN_ID', Users::$id);
	}
	Session::write('user', $target['user_id']);
	redirect(array('val' => 34));
}

$all_chars = $db->req('SELECT %%characters.%user_id, %%characters.%char_name, %%characters.%char_id, %%users.%user_email, %%users.%user_id, %%users.%user_name FROM %%characters INNER JOIN %%users ON %%users.%user_id = %%characters.%user_id');

$all_users = $db->req('SELECT %%users.%user_id, %%users.%user_email, %%users.%user_name FROM %%users');

$users = [];

if ($all_chars) {
	foreach($all_chars as $v) {
		$users[$v['user_id']][] = $v;
	}
}
unset($all_chars);

if ($all_users) {
	foreach ($all_users as $v) {
		if (!isset($users[$v['user_id']])) { $users[$v['user_id']] = $v; }
	}
}
unset($all_users);

?>

<div class="container">

<?php if ($users) { ?>
<ul>
	<?php foreach($users as $k => $user) { ?>
		<li>
			<?php
				if (P_DEBUG === true && $k !== Users::$id) {
					echo mkurl(array('val'=>57, 'type' => 'tag', 'anchor' => 'Impersonate', 'attr' => array('class' => 'btn btn-mini'), 'params' => array('impersonate', $k))), ' ';
				}
				if (isset($user[0]['char_id'])) {
					echo '<span class="username">'.$user[0]['user_name'].'</span>';
					echo '<span class="usermail">'.$user[0]['user_email'].'</span>';
					foreach ($user as $char) {
						echo '&nbsp; &ndash; &nbsp;';
						echo mkurl(array('val'=>47, 'type' => 'tag', 'anchor' => $char['char_name'], 'params' => array($char['char_id'])));
					}
				} else {
					echo '<span class="username">'.$user['user_name'].'</span>';
					echo $user['user_email'];
				}?>
		</li>
	<?php } ?>
</ul>
<?php }
unset($users,$k,$user,$char);
?>
</div><!-- /container -->

<?php
	buffWrite('css', /** @lang CSS */ <<<CSSFILE
	.username { display: inline-block; min-width: 140px; }
	.usermail { display: inline-block; min-width: 200px; }
CSSFILE
);
	buffWrite('js', '');