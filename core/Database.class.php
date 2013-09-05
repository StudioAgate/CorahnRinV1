<?php

/**
 * Classe de connexion à la base de données
 * Permet la gestion de l'affichage des erreurs
 * Effectue des requêtes préparées
 * Simplifie les noms de tables avec les préfixes %% et % (voir méthode buildReq)
 * @author Pierstoval 01/01/2013
 * @version 1.0
 */
class Database extends Object {

	protected $table;
	public static $prefix;
	private $db;
	private $dbname;
	private $e;
	private $show_err;
	private $err_type;
	private $last_query;
	private $last_values;
	private $last_results;
	private $cache;
	private $cache_saved = false;
	private $cache_filename;

	function __construct($host = '127.0.0.1', $user = 'root', $pwd = '', $database = 'mydb', $tb_prefix = '', $db_type = 'mysql') {
		$this->cache_filename = ROOT.DS.'logs'.DS.'cache_sql'.DS.'cache.php';

		$pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

		## Utilisation de la connexion persistante uniquement dans le cas d'une connexion lente à la BDD, ce qui est rare.
		## Commentez cette ligne si votre connexion est rapide (moins d'1 seconde pour se connecter)
// 		$pdo_options[PDO::ATTR_PERSISTENT] = true;

		self::$prefix = $tb_prefix;
		$dsn = $db_type.':host=' . $host . ';dbname=' . $database . '';
		$this->initErr(true);
		try {
			$this->db = new PDO($dsn, $user, $pwd, $pdo_options);
			$this->dbname = $database;
			$this->_init_cache();
		} catch (PDOException $e) {
			header('Content-type: text/html; charset=utf-8');
			tr('Impossible de se connecter à la base de données... <br />Message : {message}', false, array('message'=>'<br />'.Encoding::toUTF8($e->getMessage())));
			exit;
		}
		//$this->initErr(false);
		$this->noRes('SET NAMES "utf8"');
	}

	function __destruct() {
		$this->_export_cache();
	}

	function __toString() {
		$ret = '';
		$ret .= p_dump(array(
				'database' => $this->dbname,
				'show_err' => $this->show_err,
				'err_type' => $this->err_type,
				'last_query' => $this->last_query,
				'last_values' => $this->last_values,
				'last_results' => $this->last_results
		));
		return $ret;
	}

	/**
	 * Afficher les erreurs permet de débugguer plus rapidement les requêtes en mode développement. A désactiver en mode production
	 *
	 * @param boolean $err Affiche les erreurs ou non
	 * @param string $type Change le type d'erreur. Valeurs possible : 'fatal', 'warning', 'notice'
	 * @author Pierstoval 01/01/2013
	 * @version 1.0
	 */
	public function initErr($err = false, $type = 'fatal') {
		$this->show_err = $err == true ? true : false;
		if ($type === 'warning')	{
			$this->err_type = E_USER_WARNING;
		}
		elseif ($type === 'fatal')		{
			$this->err_type = E_USER_ERROR;
		}
		elseif ($type === 'notice')	{
			$this->err_type = E_USER_NOTICE;
		}
		else {
			$this->err_type = E_USER_WARNING;
		}
	}

	/**
	 * Affiche les erreurs, renvoie le détail selon les paramètres envoyés  la méthode
	 *
	 * @param PDOException $e Utilisé en cas de throw exception sur une requête
	 * @param string $req_qry Utilisé pour afficher la requête en cas d'erreur, notamment sur les méthodes req(), row() et noRes()
	 * @param PDOStatement $req Contient la ressource PDOStatement permettant l'extraction d'erreurs
	 * @param boolean $trigger Si true, on renvoie l'erreur. Sinon, on affiche simplement qu'une erreur est survenue. Cela permet, en cas de paramétrage d'erreurs "fatales", d'arrêter le script en cas de besoin
	 * @author Pierstoval 01/01/2013
	 * @version 1.0
	 */
	public function showErr($e = null, $req_qry = null, $req = null, $trigger = false) {
		global $_PAGE;
		$final = '*|*|*Date=>'.json_encode(date(DATE_RFC822));
		$trace = is_object($e) ? $e->getTrace() : $e;
		$final .= '||Erreur N°=>'.json_encode($e->errorInfo[0]);
// 		$final .= '||Traçage=>'.json_encode($trace);
		$final .= '||Méthode appelée=>'.json_encode($trace[2]['function']);
		$final .= '||Last instance query=>'.json_encode($this->last_query);
		$final .= '||Last real query=>'.json_encode($req_qry);
		$final .= '||Last values sent=>'.json_encode($this->last_values);
		$final .= '||PDO caught exceptions=>'.json_encode(is_object($e) ? $e->getMessage() : $e);

		$final .= '||Ip=>'.json_encode($_SERVER['REMOTE_ADDR'])
		.'||Page.get=>'.json_encode($_PAGE['get'])
		.'||Page.request=>'.json_encode($_PAGE['request'])
		.'||User.id=>'.json_encode(Session::read('user'));

		$error_file = ROOT.DS.'logs'.DS.'sql'.DS.date('Y.m.d').'.log';
		$f = fopen($error_file, 'a');
		fwrite($f, $final);
		fclose($f);
		$msg = 'Une erreur MySQL est survenue...<br />';
		echo '<pre>';
		if (class_exists('Translate')) { tr($msg); echo $e->getMessage(); } else { echo $msg.'<br />'.$e->getMessage(); }
		echo '</pre>';
	}

	/**
	 * Alias statique de buildReq() : Formate la requête
	 *
	 * @param string $req_qry La requête initiale
	 * @param array $values Les paramètres de la requête à préparer
	 * @return string Requête formatée
	 * @author Pierstoval 01/01/2013
	 * @version 1.0
	 */
	public static function sbuildReq($req_qry, $values = array()) {
		$values = (array) $values;
		if (strpos($req_qry, '%%%fields') !== false) {//Transforme %%%Fields en une liste des champs à entrer
			$fields = array();
			foreach ($values as $field => $value) {
				$field = str_replace(':','',$field);
				$fields[] = '%'.$field.' = :'.$field;
			}
			$req_qry = str_replace('%%%fields', implode(', ', $fields), $req_qry);
		}
		if (strpos($req_qry, '%%%in') !== false) {//Transforme %%%Fields en une liste des champs à entrer
			if (empty($values)) {
				$req_qry = str_replace('%%%in', '0', $req_qry);
			} else {
				$str = implode(', ', array_fill(0, count($values), '?'));
				$req_qry = str_replace('%%%in', $str, $req_qry);
			}
		}
		$req_qry = preg_replace('#%%([a-zA-Z0-9_]+)#', ' `'.self::$prefix.'$1` ', $req_qry); // Transforme %%table en `prefix_table`
		$req_qry = preg_replace('#%([a-zA-Z0-9_]+)#', ' `$1` ', $req_qry); // Transforme %champ en `champ`

		$t = array();
		foreach($values as $k => $v) {
			if (!preg_match('#^:#isUu', $k) && !is_numeric($k)) {
				unset($values[$k]);
				$values[':'.$k] = $v;
			}
		}

		$req_qry = str_replace("\n", ' ', $req_qry);
		$req_qry = str_replace("\r", '', $req_qry);
		$req_qry = str_replace("\t", '', $req_qry);
		$req_qry = preg_replace('#\s\s+#Uu', ' ', $req_qry);
		return $req_qry;
	}

	public function last_id() {
		$last_id = 0;
		try {
			$last_id = $this->db->lastInsertId();
			$last_id = (int) $last_id;
		} catch (PException $e) {
			$last_id = $this->showErr($e, '', $last_id, true);
		}
		return $last_id;
	}

	/**
	 * Effectue une requête classique
	 *
	 * @param string $req_qry Une requête formatée préalablement avec buildReq()
	 * @param array $values Un tableau de valeurs à envoyer à PDO pour la requête préparée
	 * @return Un tableau avec une entrée pour chaque élément trouvé dans la BDD, false
	 * @author Pierstoval 01/01/2013
	 * @version 1.0
	 */
	public function req($req_qry, $values = array()) {
		$values = (array) $values;
		$req_qry = $this->buildReq($req_qry, $values);
		$result = $this->runReq($req_qry, $values);
		if (is_object($result) && $result->rowCount() > 0) {
			$contents = $result->fetchAll(PDO::FETCH_ASSOC);
			foreach($contents as $key => $val) {
				foreach($val as $vkey => $vval) {
					if (is_numeric($vval)) {
						$contents[$key][$vkey] = (int) $vval;
					}
					if (is_int($vkey)) {
						unset($contents[$key][$vkey]);
					}
				}
			}
		} elseif (is_array($result)) {
			$contents = $result;
		} else {
			$contents = false;
		}
		$this->last_results = $contents;
		if (is_object($result)) {
			$result->closeCursor();
		}
		if (preg_match('#select#isUu', $req_qry) && $contents) {
			$this->_save_cache($req_qry, $values, $contents);
		}
		return $contents;
	}

	/**
	 * Effectue une requête mais ne récupère que le premier résultat. Utile pour les sélections uniques par Id
	 *
	 * @param string $req_qry Une requête formatée préalablement avec buildReq()
	 * @param array $values Un tableau de valeurs à envoyer à PDO pour la requête préparée
	 * @return tableau à 1 entrée, false sinon
	 * @author Pierstoval 01/01/2013
	 * @version 1.0
	 */
	public function row($req_qry, $values = array()) {
		$values = (array) $values;
		$req_qry = $this->buildReq($req_qry, $values);
		if (!preg_match('#LIMIT +[0-9]+( *, *[0-9]+)?#isU', $req_qry)) {
			$req_qry .= ' LIMIT 0,1';
		}
		$result = $this->runReq($req_qry, $values);
		if (is_object($result) && $result->rowCount() > 0) {
			$contents = $result->fetch(PDO::FETCH_ASSOC);
			foreach($contents as $key => $val) {
				if (is_numeric($val)) {
					$contents[$key] = (int) $val;
				}
				if (is_int($key)) {
					unset($contents[$key]);
				}
			}
		} elseif (is_array($result)) {
			$contents = $result;
		} else {
			$contents = false;
		}
		$this->last_results = $contents;
		if (preg_match('#select#isUu', $req_qry) && $contents) {
			$this->_save_cache($req_qry, $values, $contents);
		}
		if (is_object($result)) {
			$result->closeCursor();
		}
		return $contents;
	}

	/**
	 * Effectue une requête mais ne récupère pas de résultat autre que la réussite ou l'échec. Utile pour update,insert,set,delete...
	 *
	 * @param string $req_qry Une requête formatée préalablement avec buildReq()
	 * @param array $values Un tableau de valeurs à envoyer à PDO pour la requête préparée
	 * @return true si la requête est excéutée, false sinon
	 * @author Pierstoval 01/01/2013
	 * @version 1.0
	 */
	public function noRes($req_qry, $values = array()) {
		$values = (array) $values;
		$req_qry = $this->buildReq($req_qry, $values);
		$result = $this->runReq($req_qry, $values);
		if (is_object($result) && $result->rowCount() > 0) {
			$results = $result->rowCount();
		} elseif (is_array($result)) {
			$results = $result;
		} else {
			$results = false;
		}
		$this->last_results = $results;
		if ($results) {
			$result->closeCursor();
		}
		if (preg_match('#insert|delete#isUu', $req_qry) && $results) {
			$this->_save_cache($req_qry, $values, $results);
		}
		return $results ? true : false;
	}

	/**
	 * Formate la requête
	 *
	 * @param string $req_qry La requête initiale
	 * @param array $values Les paramètres de la requête à préparer
	 * @return string Requête formatée
	 * @author Pierstoval 01/01/2013
	 * @version 1.0
	 */
	private function buildReq($req_qry, $values = array()) {
		if (strpos($req_qry, '[TABLE]') !== false) {
			$req_qry = str_replace('%[TABLE]', '[TABLE]', $req_qry);
			$req_qry = str_replace('[TABLE]', '%'.$this->table, $req_qry);
		}
		$req_qry = self::sbuildReq($req_qry, $values);
		$this->last_query = $req_qry;
		$this->last_values = $values;
		return $req_qry;
	}

	/**
	 * Prépare une requête et l'exécute via PDO
	 * @param string $req_qry Requête SQL (Doit avoir été formatée par la méthode buildReq)
	 * @param array $values Tableau de valeurs à envoyer à PDO pour l'exécution
	 * @return object PDOStatement
	 * @author Pierstoval 01/01/2013
	 * @version 1.0
	 */
	private function runReq($req_qry, $values = array()) {
		$values = (array) $values;
		$check_cache = $this->_check_cache($req_qry, $values);
		if ($check_cache !== false && strpos($req_qry, 'select') !== false) {
			$result = $this->_get_cache($check_cache, $req_qry, $values);
		} else {
			try {
				$result = $this->db->prepare($req_qry);
				$result->execute($values);
			} catch (PException $e) {
				$result = $this->showErr($e, $req_qry, $result, true);
			}
		}
		return $result;
	}

	/**
	 * Vérifie si une requête existe dans le cache
	 * @param string $req Une requête parsée avec sbuildReq() à vérifier
	 * @param array $values Un tableau passé en paramètres à la requête préparée
	 * @return boolean Vrai si la requête existe, false sinon
	 * @author Pierstoval 13/06/2013
	 * @version 1.0
	 */
	private function _check_cache($req, $values = array()) {
		$cache = $this->cache;

// 		if (isset($this->cache[$req])) {
// 			return true;
// 		}
		foreach ($cache as $index => $element) {
			if ($req === $element['req'] && $values === $element['values']) {
				return $index;
			}
		}
		return false;
	}

	/**
	 * Sauvegarde une requête et ses résultats dans le cache
	 * @param string $req Une requête parsée avec sbuildReq() à mettre en cache
	 * @param array $results Les résultats de la requête à mettre en cache
	 * @param array $values Un tableau passé en paramètres à la requête préparée
	 * @return boolean Vrai si l'enregistrement a été réalisé, false sinon
	 * @author Pierstoval 13/06/2013
	 * @version 1.0
	 */
	private function _save_cache($req, $values = array(), $results = '') {
		$cache = (array) $this->cache;

		if ($this->_check_cache($req, $values) !== false) {
			return false;
		}

		$save = array(
				'req' => $req,
				'values' => $values,
				'results' => $results,
		);
		$cache[$req] = $save;

		$this->cache = $cache;
		$this->cache_saved = true;
		return true;
	}

	/**
	 * Récupère le contenu d'une requête dans le cache
	 * @param string $req Une requête parsée avec sbuildReq() à vérifier
	 * @param array $values Un tableau passé en paramètres à la requête préparée
	 * @return mixed Les données récupérées dans le cache (de tout type, tableau, chaîne, booléen...), false sinon.
	 * @author Pierstoval 13/06/2013
	 * @version 1.0
	 */
	private function _get_cache($index, $req, $values = array()) {
		$cache = $this->cache;

		$results = false;
		if (isset($cache[$req]) &&
		$cache[$req]['req'] === $req &&
		$cache[$req]['values'] === $values) {
			$results = $cache[$req]['results'];
		}
		// 		if (isset($cache[$index]) &&
		// 			$cache[$index]['req'] === $req &&
		// 			$cache[$index]['values'] === $values) {
		// 			$results = $cache[$index]['results'];
		// 		}

		return $results;
	}

	/**
	 * Récupère le contenu du cache sql
	 * @return mixed Les données récupérées dans le cache (de tout type, tableau, chaîne, booléen...), false sinon.
	 * @author Pierstoval 13/06/2013
	 * @version 1.0
	 */
	private function _init_cache() {
		$this->cache = array();
		if (FileAndDir::fexists($this->cache_filename)) {
			include $this->cache_filename;
		} else {
			FileAndDir::put($this->cache_filename, '<?php $this->cache = array();');
		}
		return $this->cache;
	}

	/**
	 * Enregistre toutes les données dans le fichier cache
	 * @author Pierstoval 01/01/2013
	 * @version 1.0
	 */
	private function _export_cache() {
		if ($this->cache_saved === true) {
			$cache = $this->cache;

			$export = var_export($cache, true);
			$export = preg_replace('#\s+\'#Uu', "'", $export);
			$export = preg_replace('#\s+\)#Uu', ')', $export);
			$export = preg_replace('#\s+array #Uu', 'array', $export);
			$export = preg_replace('#\s+([0-9]+) =>#Uu', '$1=>', $export);


// 			$replace = array(
// 				"\n" => '',
// 				"\r" => '',
// 				"\t" => '',
// 				' }' => '}',
// 				';}' => '}',
// 				' {' => '{',
// 				'{ ' => '{',
// 				', ' => ',',
// 				': ' => ':',
// 				' :' => ':',
// 				' !=' => '!=',
// 				' =' => '=',
// 				'= ' => '=',
// 				' <' => '<',
// 				' >' => '>',
// 				'< ' => '<',
// 				'> ' => '>',
// 			);
// 			$export = str_replace(array_keys($replace), array_values($replace));

			$cache_save = '<?php $this->cache = '.($export ? $export : "''").';';

			FileAndDir::put($this->cache_filename, $cache_save);
		}
	}
}
