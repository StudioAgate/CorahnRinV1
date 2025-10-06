<?php

namespace App;

/**
 * Classe statique permettant la gestion des variables de session
 *
 */
class Session {

/**
 * Cette fonction permet l'initialisation de la variable de session
 *
 * @version 0.1 - 20/04/2012
 * @version 0.2 - 31/07/2012 - Suppression de la récupération des données de la variable de session par un fichier
 * @version 0.3 - 09/11/2012 - Rajout du test pour savoir si les classes Inflector et Set sont chargées
 */
	public static function init() {

		if (!isset($_SESSION['nocache'])) {
			header('Pragma: no-cache');
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Thu, 03 Jan 2013 06:23:00 GMT');
			$_SESSION['nocache'] = true;
		}

		$sessionName = Inflector::variable(Inflector::slug('CorahnRin '.$_SERVER['HTTP_HOST'])); //Récupération du nom de la variable de session
		ini_set('session.use_trans_sid', 0);													//Evite de passe l'id de la session dans l'url
		session_name($sessionName); 															//On affecte le nom
		session_start(); 																		//On démarre la session

        $time = self::read('_time') ?: rand(120,240);

        if (!self::check('_token') || self::read('_valid') < time() - $time) {
            self::write('_token', self::createToken());
            self::write('_valid', time());
            self::write('_time', rand(120,240));
        }
	}

    public static function createToken() {
        return sha1(uniqid('corahn_rin_token', true));
    }

/**
 * ACCESSEURS EN LECTURE, ECRITURE, SUPPRESSION ET CONTROLE DE LA VARIABLE DE SESSION
 */

/**
 * Retourne vrai si la variable passée en paramètre figure dans la variable de session
 *
 * @param string $key Variable à tester
 * @return boolean Vrai si la variable est dans la variable de session, faux sinon
 * @version 0.1 - 30/12/2011
 * @see /Koezion/lib/set.php
 */
	public static function check($key) {

		if(empty($key)) { return false; } //Si la clée est vide
		$result = Hash::get($_SESSION, $key); //On procède à l'extraction de la donnée
		return !empty($result); //On retourne le résultat
	}

/**
 * Permet d'écrire une donnée dans la session
 *
 * @param string $key Clée de la donnée
 * @param mixed $value Donnée à écrire
 * @return boolean Vrai si la valeur est insérée, faux sinon
 * @version 0.1 - 30/12/2011
 * @see /Koezion/lib/set.php
 */
	public static function write($key, $value) {

		$session = Hash::insert($_SESSION, $key, $value); //On insère les données et on récupère la nouvelle variable de session
		$_SESSION = $session; //On affecte les données à la variable de session
		return (Hash::extract($_SESSION, $key) === $value); //On retourne le résultat de la fonction
	}

/**
 * Permet de lire une donnée dans la session
 *
 * @param string $key Clée de la donnée (Peut être composée de . pour les niveaux)
 * @return mixed Valeur de la donnée, faux si la donnée n'est pas dans la variable de session
 * @version 0.1 - 30/12/2011
 * @see /Koezion/lib/set.php
 */
	public static function read($key = null) {

		$result = Hash::get($_SESSION, $key);
		if(!is_null($result)) { return $result; }
		else { return false; }
	}

/**
 * Permet de supprimer une donnée dans la session
 *
 * @param string $key Clée de la donnée (Peut être composée de . pour les niveaux)
 * @return boolean Vrai si la valeur est supprimée, faux sinon
 * @version 0.1 - 30/12/2011
 * @see /Koezion/lib/set.php
 */
	public static function delete($key) {

		$_SESSION = Hash::remove($_SESSION, $key);
		return (Session::check($key) == false);
	}

/**
 * Permet de supprimer la variable de session lors de la deconnexion
 *
 * @version 0.1 - 20/04/2012
 * @see http://www.php.net/manual/fr/function.session-destroy.php
 */
	public static function destroy() {

		session_unset(); // Détruit toutes les données dans la variable de session

		// Si vous voulez détruire complètement la session, effacez également le cookie de session.
		// Note : cela détruira la session et pas seulement les données de session
		if (ini_get("session.use_cookies")) {

			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}

		session_destroy(); // Finalement, on détruit la session
	}

/**
 * GESTION DES MESSAGES FLASH
 */

/**
 * Permet d'initialiser un message dans la variable de session
 *
 * @param string $message Message à afficher
 * @param string $type Type du message
 * @version 0.1 - 30/12/2011
 */
	public static function setFlash($message, $type = 'notif') {

		$flashBag = Session::read('flash_bag');

        if (!isset($flashBag[$type])) {
            $flashBag[$type] = [];
        }

        $flashBag[$type][] = $message;

		Session::write('flash_bag', $flashBag);
	}

    /**
     * @return array
     */
    public static function getFlashbag()
    {
        $datas = self::read('flash_bag');
        self::delete('flash_bag');
        return $datas;
    }

/**
 * EN ATTENTE DE COMM
 *
 * @return boolean
 */

	public static function isLogged() {

		$session = Session::read('Backoffice');
		return (isset($session) && !empty($session));
	}

/**
 * Cette fonction permet de récupérer la valeur du role de l'utilisateur connecté
 *
 * @access	static
 * @author	koéZionCMS
 * @version 0.1 - 02/03/2013 by FI
 */
	public static function getRole() {

		//Si session
		if(Session::read('Backoffice.UsersGroup')) {

			$role = Session::read('Backoffice.UsersGroup.role_id'); //Récupération de la valeur
			if($role) { return $role; } //Si la valeur est valide on la retourne
			else { return false; } //On retourne faux sinon
		}
		return false;
	}

	/*static function user($key) {

		//Si session user
		if(Session::read('Backoffice.User')) {

			$userType = Session::read('Backoffice.User.'.$key); //Récupération de la valeur

			//Test de la valeur
			if(isset($userType)) { return $userType; }
			else { return false; }
		}
		return false;
	}*/
}
