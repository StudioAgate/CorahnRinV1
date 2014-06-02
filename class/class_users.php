<?php

class Users {

	public static $id = 0;
	public static $name = '';
	public static $acl = 50;
	public static $email = '';
	public static $confirm = '';
	public static $status = 0;

	public static function init($db_datas = null) {
		if (is_array($db_datas) && !empty($db_datas)) {
			if (isset($db_datas['user_status']) && (int) $db_datas['user_status'] === 0) {
				self::logout();
				Session::write('send_mail', $db_datas['user_confirm']);
				Session::setFlash('Un mail de confirmation a été envoyé à '.$db_datas['user_email'].'. Cliquez sur le lien dans ce mail pour accéder à votre compte', 'warning');
				return false;
			}
			foreach ($db_datas as $field => $val) {
				## Initialisation
				$field = strpos($field, 'user_') !== false ? str_replace('user_', '', $field) : $field;
				$user_fields = array(
					'id','name','acl','email','confirm','status',
				);
				if (preg_match('#'.implode('|', $user_fields).'#isUu', $field)) {
					self::$$field = $val;
				}
			}
		} elseif (is_numeric($db_datas) && (int) $db_datas) {
			global $db;
			$res = $db->row('SELECT * FROM %%users WHERE %user_id = ?', array($db_datas));
			if (!$res) {
				Session::setFlash('Utilisateur incorrect... #001', 'error');
				self::logout();
				return false;
			}
			return self::init($res);
		} elseif ($db_datas) {
			self::logout();
			return false;
		}
		Session::write('user', self::$id);
		return true;
	}

	public static function logout() {
		self::$id = 0;
		self::$name = '';
		self::$acl = 50;
		self::$email = '';
		Session::write('user', 0);
	}

	public static function create($datas = array()) {
		global $db;
		if (
			isset($datas['name']) &
			isset($datas['email']) &&
			isset($datas['password'])
		) {
			unset($datas['associate'], $datas['user']);
			if (strlen($datas['password']) < 5 || !preg_match('#[a-zA-Z]#isUu', $datas['password']) || !preg_match('#[0-9]#isUu', $datas['password'])) {
				Session::setFlash('Le mot de passe doit comporter au moins 5 caractères, ainsi qu\'au moins une lettre et un chiffre.', 'error');
				return false;
			}
			$datas['password'] = Users::pwd($datas['password']);
			$users = $db->req('SELECT COUNT(*) as %nb_users FROM %%users WHERE %user_name = ? OR %user_email = ?', array($datas['name'], $datas['email']));
			if ($users && isset($users[0]['nb_users']) && $users[0]['nb_users'] > 0) {
				Session::setFlash('Le nom d\'utilisateur ou l\'adresse mail est déjà utilisé', 'error');
				return false;
			}
			if (!is_correct_email($datas['email'])) {
				Session::setFlash('Entrez une adresse email correcte', 'error');
				return false;
			}
			if (!$datas['name']) {
				Session::setFlash('Entrez un nom d\'utilisateur', 'error');
				return false;
			}
			$datas = array(
				'user_name' => $datas['name'],
				'user_email' => $datas['email'],
				'user_password' => $datas['password'],
				'user_status' => 0,
				'user_confirm' => md5($datas['name'].rand(1,10000)),
			);
			$db->noRes('INSERT INTO %%users SET %%%fields ', $datas);
			$user = $db->row('SELECT %user_id,%user_name,%user_email,%user_confirm FROM %%users WHERE %user_name = ? AND %user_email = ?', array($datas['user_name'], $datas['user_email']));
			if ($user && !empty($user)) {
				//Session::write('user', $id);
				//self::init($user);
				$dest = array('name' => $user['user_name'], 'mail' => $user['user_email']);
				$mail_msg = $db->row('SELECT %mail_id, %mail_contents, %mail_subject FROM %%mails WHERE %mail_code = ?', 'register');
				if (isset($mail_msg['mail_contents']) && isset($mail_msg['mail_subject'])) {
					$subj = tr($mail_msg['mail_subject'], true, null, 'mails');
					$txt = tr($mail_msg['mail_contents'], true, array(
                        '{name}' => htmlspecialchars($user['user_name']),
                        '{link}' => mkurl(array('val'=>64,'type'=>'tag','anchor'=>tr('Confirmer l\'adresse mail', true),'params'=>array('confirm_register', $user['user_confirm']))),
                    ), 'mails');
					if (send_mail($dest, $subj, $txt, $mail_msg['mail_id'])) {
						Session::setFlash('Inscription effectuée ! Vous allez recevoir un mail de confirmation pour valider votre inscription', 'success');
						return true;
					}
				}
			} else {
				Session::setFlash('Une erreur est survenue lors de la création de l\'utilisateur', 'error');
				return false;
			}
		}
		return false;
	}

	/**
	 * Génère le mot de passe utilisateur à crypter à partir d'une chaîne de caractères
	 *
	 * @param string $str Le mot de passe à crypter
	 * @return string Le mot de passe crypté
	 */
	public static function pwd($str) {
		$nb_boucles = 5;
		$salts = array(
			'/JvH*vPdH,a~>]-U%!-|1^~<d0|ML{naAn5+%--H<fB +|_!3rIZsdn`H`810VFa',
			'd>8P(onEFL3^I]LjME&0,MX6Xsp:*x:qq8&NHjP[EUIU7-aR^yuyM)r?F|cPk|>T',
			'i,AH~6kWjs99GlC$S:B0l`1f|W2YTKMSl%#ko_Z-]!Ki+K}47|5-[n{|5m1&JT8_',
			'1ARA,-H^68(i&[Ys:Hk1`-TkSVC/&$s~giatj=X)|}^I-sB^Tc-NMO3_xY10hv.I',
			'WFAA+]w>6XcmL63%P0/IO::L>_L(y3xH$Q&30#ZsA&`FvF9~k-zYv(8Kj50^<JnC',
			'.XL7N+Zk0X $xAr~okBzBVOLkEdF3jA`,kOs<Q+2CrODXIQtQTmM|}$|bLfcgx4h'
		);

		for ($i = 0; $i <= $nb_boucles; $i++) {
			if (isset($salts[$i])) {
				$str .= $salts[$i];
			}
			if ($i % 2 === 0) {
				$str = md5($str);
			} else {
				$str = sha1($str);
			}
		}

		return $str;
	}
}