<?php
class ConnectController extends Controller {

	function index_action() {
		Router::redirect(array('route_name'=>'core_login', 'force_route'=>true), 200);
	}

	function confirm_register_action() {
		$get = $this->get('a');

		if (!$get) {
			Router::redirect('home', 403);
		}
		$usermodel = $this->load_model('User');

		$user = $usermodel->row('SELECT %user_status, %user_id FROM %%users WHERE %user_confirm = ?', array($get));

		if (empty($user) || !$user) {
			Router::redirect('home', 401, 'Aucun utilisateur trouvé.', 'error');
			redirect(array('val'=>1), 'Aucun utilisateur trouvé', 'error');
		} elseif ($user['user_status'] !== 0) {
			Router::redirect('home', 200, 'L\'utilisateur est déjà enregistré.', 'notif');
		} elseif ($usermodel->noRes('UPDATE %%users SET %user_status = :status WHERE %user_id = :id', array('status'=>1,'id'=>$user['user_id']))) {
			Router::redirect('home', 200, 'Votre inscription a été prise en compte ! Vous pouvez désormais vous connecter !.', 'success');
		} else {
			Router::redirect('404', 404, 'Aucun utilisateur trouvé.', 'error');
		}

		$this->rendered(true);
		unset($model);
	}//end register action

	function login_action() {

		$post = $this->request()->post();

		if (P_LOGGED === true) {
			Router::redirect(array('route_name'=>'core_home', 'force_route'=>true, 'type'=>'redirect'), 200);
			exit;
		}

		## Connexion
		if (isset($post['nickname']) && isset($post['password'])) {
			$this->load_model('User', false);
			$userModel = $this->UserModel;
			$user = $userModel->find(array(
					'conditions'=>array('user_name'=>$post['nickname'], 'user_password'=>Users::pwd($post['password'])),
					'limit'=>'0,1',
					'type'=>'row'
			)
			);
			$this->unload_model('User');

			if ($user) {
				$_SESSION['user'] = $user['user_id'];
				Router::redirect(array('route_name'=>'core_admin', 'force_route'=>true), 200, 'Connexion réussie !', 'success');
			} else {
				$_SESSION['user'] = 0;
				if ($post['nickname'] && !$post['password']) {
					Session::setFlash('Veuillez entrer le mot de passe.', 'error');
				} elseif ($post['nickname'] && $post['password']) {
					Session::setFlash('Le nom d\'utilisateur ou le mot de passe est incorrect.', 'error');
				} elseif (!$post['nickname']) {
					Session::setFlash('Veuillez entrer un nom d\'utilisateur.', 'error');
				}
			}
			unset($user);
		}
		$this->set('post', $post);

	}//end login action

	function logout_action() {
		Users::logout();
		Router::redirect(array('route_name'=>'core_home', 'force_route'=>true, 'type'=>'redirect'), 200, 'Vous êtes désormais déconnecté(e) !', 'success');
	}

	function register_action() {

		$post = $this->request()->post();

		if (!empty($post) && isset($post['name']) && isset($post['email']) && isset($post['password'])) {

			$err = '';
			if (!$post['name']) {
				$err .= '<p class="error">'.tr('Le nom d\'utilisateur doit être renseigné', true).'</p>';
			}
			if (!$post['password']) {
				$err .= '<p class="error">'.tr('Entrez un mot de passe', true).'</p>';
			}
			if (!$post['email'] || !preg_match(P_MAIL_REGEX, $post['email'])) {
				$err .= '<p class="error">'.tr('Entrez une adresse email correcte', true).'</p>';
			}

			if ($err !== '') {
				Session::setFlash($err, '');
// 				header('Location:'.mkurl(array('val'=>$_PAGE['id'])));
				return;
			}

			$datas = array(
					'name' => $post['name'],
					'password' => $post['password'],
					'email' => $post['email'],
					'status' => 0,
					'confirm' => md5($post['name'].uniqid(preg_replace('#[^a-z_]+#isUu', '', $post['name']), true)),
			);

			$create = Users::create($datas);
			if ($create === true) {
				if (isset($_GET['redirect']) && $_GET['redirect'] && url_exists($_GET['redirect'])) {
// 					redirect($_GET['redirect']);
				} else {
// 					redirect(array('val'=>34));
				}
			}
		}

		if (P_LOGGED === true) {
			Router::redirect(array('route_name'=>'core_admin', 'force_route'=>true));
		}
	}
}