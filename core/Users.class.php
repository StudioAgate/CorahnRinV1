<?php
/**
 * Classe statique de gestion des utilisateurs actifs.
 *
 * @author Pierstoval 01/08/2013
 * @version 1.0
 */
class Users extends Object {

	/**
	 * Une instance du modèle UserModel
	 * @var object UserModel
	 * @see UserModel
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	private static $model;

	/**
	 * Un tableau contenant les données de l'utilisateur, issues de la base de données
	 * @var array
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	private static $db_user;

	/**
	 * Un objet contenant les données de l'utilisateur, issues de la base de données
	 * @var object
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	private static $db_user_obj;

	/**
	 * L'identifiant de l'utilisateur
	 * @var int
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	private static $id = 0;

	/**
	 * Le nom d'utilisateur
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	private static $name = '';

	/**
	 * Le degré de vue de l'utilisateur, "Access Control List"
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	private static $acl = 50;

	/**
	 * L'adresse email de l'utilisateur
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	private static $email = '';

	/**
	 * Le code de confirmation d'inscription de l'utilisateur
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	private static $confirm = '';

	/**
	 * Le statut de l'utilisateur
	 * 0 = inactif au moment de l'inscription
	 * 1 = actif
	 * 2 = inactif, mais désactivé par un administrateur
	 * 3 = banni
	 * @var string
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	private static $status = 0;

	public static function __callstatic($method, $args) {
		if (isset(self::$$method)) {
			return self::$$method;
		} else {
			throw new PException('La méthode statique "'.$method.'" n\'existe pas dans la classe "Users"');
		}
	}

	/**
	 * Récupère l'utilisateur courant depuis la base de données
	 * @param string $db_datas
	 * @return boolean
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static function init($db_datas = null) {
		self::$model = new UserModel();
		$model = self::$model;
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
			self::$db_user = $db_datas;
		} elseif (is_numeric($db_datas) && (int) $db_datas) {

// 			$res = $db->row('SELECT * FROM %%users WHERE %user_id = ?', array($db_datas));
			$res = $model->find(array(
				'conditions' => array('user_id'=>$db_datas),
				'type' => 'row',
			));
			if (!$res) {
				Session::setFlash('Utilisateur incorrect... #001', 'error');
				self::logout();
				return false;
			}
			return self::init($res);
		} else {
			self::logout();
			return false;
		}
		Session::write('user', self::$id);
		return true;
	}

	/**
	 * Déconnecte l'utilisateur courant
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static function logout() {
		self::$id = 0;
		self::$name = '';
		self::$acl = 50;
		self::$email = '';
		Session::write('user', 0);
	}

	/**
	 * Vérifie les informations entrées en paramètre et crée un utilisateur si possible
	 * @param array $datas Un tableau de données issu d'un formulaire
	 * @return boolean
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static function create($datas = array()) {
		self::$model = new UserModel();
		$model = self::$model;
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
			$users = $model->req('SELECT COUNT(*) as %nb_users FROM %%users WHERE %user_name = ? OR %user_email = ?', array($datas['name'], $datas['email']));
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
			$model->noRes('INSERT INTO %%users SET %%%fields ', $datas);
			$user = $model->row('SELECT %user_id,%user_name,%user_email,%user_confirm FROM %%users WHERE %user_name = ? AND %user_email = ?', array($datas['user_name'], $datas['user_email']));
			if ($user && !empty($user)) {
				//Session::write('user', $id);
				//self::init($user);
				$dest = array('name' => $user['user_name'], 'mail' => $user['user_email']);
				$mail_msg = $model->row('SELECT %mail_id, %mail_contents, %mail_subject FROM %%mails WHERE %mail_code = ?', 'register');
				if (isset($mail_msg['mail_contents']) && isset($mail_msg['mail_subject'])) {
					$subj = $mail_msg['mail_subject'];
					$txt = $mail_msg['mail_contents'];
					$txt = str_replace('{name}', htmlspecialchars($user['user_name']), $txt);
					$txt = str_replace('{link}', Router::link(array('route_name'=>'core_confirm_register', 'force_route'=>true,'type'=>'tag','anchor'=>'Confirmer l\'adresse mail','get'=>array('a'=>$user['user_confirm']))), $txt);
					$txt = str_replace('{link}', mkurl(array('val'=>64,'type'=>'tag','anchor'=>'Confirmer l\'adresse mail','params'=>array('confirm_register', $user['user_confirm']))), $txt);
					if (send_mail($dest, $subj, $txt, $mail_msg['mail_id'])) {
						Router::redirect('home', 200, 'Inscription effectuée ! Vous allez recevoir un mail de confirmation pour valider votre inscription', 'success');
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
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
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