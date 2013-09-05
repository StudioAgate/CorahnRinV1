<?php
/**
 * Classe de modèle générique
 * Hérite de la classe de gestion à la base de données pour faciliter les requêtes et augmenter les possibilités des modèles
 *
 * @see bdd
 * @author Pierstoval 01/08/2013
 * @version 1.0
 */
class Model extends Database {

	/**
	 * La table dans la base de données qui correspond à ce modèle
	 * @var string
	 */
	public $table = '';

	/**
	 * La liste des modèles qui ont été instanciés au cours de cette session
	 * @var array
	 */
	private static $instanciated = array();

	function __construct($table = null) {
		if ($table) { $this->table = $table; }
		parent::__construct(P_DB_HOST, P_DB_USER, P_DB_PWD, P_DB_DBNAME, P_DB_PREFIX, P_DB_TYPE);
		if (isset($this->table)) {
			$struct = array();
			$res = $this->req('DESCRIBE [TABLE]');
			if ($res && is_array($res)) {
				foreach ($res as $v) {
					$v['Type'] = preg_replace('#\([^)]+\)#isUu', '', $v['Type']);
					$v['Type'] = preg_replace('#^(long|medium|big|tiny|small)#isUu', '', $v['Type']);
					$v['Null'] = $v['Null'] === 'NO' ? false : true;
					$v['Key'] = $v['Key'] ?: null;
					if ($v['Key']) {
						$v['Key'] = str_replace('PRI', 'Primary', $v['Key']);
						$v['Key'] = str_replace('UNI', 'Unique', $v['Key']);
					}
					$v['Extra'] = $v['Extra'] ?: null;
					$struct[$v['Field']] = $v;
				}
				$this->structure = $struct;
			}
		} else {
			throw new PException('La table doit être définie dans le modèle "'.get_class($this).'"');
			exit;
		}
		self::$instanciated[get_class($this)] = $this;
	}

	/**
	 * Récupère la liste des modèles qui ont été instanciés
	 * @param string $name Si $name est défini, alors retourne la référence de l'instance demandée
	 * @return boolean | object | array
	 * @author Pierstoval 01/08/2013
	 * @version 1.0
	 */
	public static function get_instances($name = null) {
		if ($name === null) {
			return self::$instanciated;
		} elseif (isset(self::$instanciated[$name])) {
			return self::$instanciated[$name];
		} else {
			return false;
		}
	}

	/**
	 * Effectue une requête SELECT dans la base de données en fonction des champs dans le tableau $req
	 *
	 * @param array $req
	 * @param int $type
	 * @return string
	 * @author Pierstoval 01/08/2013 (importé et adapté du CMS)
	 * @version 1.0
	 * @link http://koezion-cms.com/
	 */
	public function find($req = array(), $type = PDO::FETCH_ASSOC) {

		$structure = $this->structure;

		$sql = 'SELECT '; //Requete sql

		if(!isset($req['fields'])) {
			$fields = array_keys($structure);
			$fields = array_map(function ($v) {return '%'.$v;}, $fields);
			$req['fields'] = implode(', ', $fields);
			unset($fields);
		}

		if(is_array($req['fields'])) {
			foreach ($req['fields'] as $k => $v) {
				if (!preg_match('~^%~', $v)) {
					if (!isset($structure[$k])) {
						return 'ERROR';
					}
					$req['fields'][$k] = '%'.$this->table.'.%'.$v;
				}
			}
			unset($v, $k);
			$sql .= implode(', ', $req['fields']);//Si il s'agit d'un tableau
		} else {
			$sql .= ' '.$req['fields'].' ';//Si il s'agit d'une chaine de caractères
		}

// 		$sql .= ' FROM %'.$this->table.' AS %'.get_class($this).' ';
		$sql .= ' FROM %'.$this->table.' ';

		///////////////////////////
		//   CHAMPS INNER JOIN   //
		if(isset($req['innerJoin']) && !empty($req['innerJoin'])) {
			if (!is_array($req['innerJoin'])) {
				$sql .= $req['innerJoin'].' ';//On ajoute à la requête s'il s'agit d'une chaîne
			} else {
				if (isset($req['innerJoin'][0])) {//Si l'on a un tableau à index numérique, on peut avoir plusieurs "join" à la suite et sur plusieurs tables
					foreach ($req['innerJoin'] as $k => $v) {
						$sql .= ' INNER JOIN %%'.$v['table'].' ON '.$v['pivot'].' ';//On ajoute à la requête
					}
					unset($v, $k);
				} else {//Sinon, on n'a qu'un seul "join"
					$sql .= ' INNER JOIN %%'.$req['innerJoin']['table'].' ON '.$req['innerJoin']['pivot'].' ';//On ajoute à la requête
				}
			}
		}

		///////////////////////////
		//   CHAMPS CONDITIONS   //
		$arr_cond = array();
		if(isset($req['conditions'])) { //Si on a des conditions
			$cond_num = 0;
			$conditions = 'WHERE ';	//Mise en variable des conditions
			if(!is_array($req['conditions'])) {
				$conditions .= $req['conditions']; //On les ajoute à la requete
			} else {
				$cond = array();
				foreach($req['conditions'] as $k => $v) {
					$cond[] = '%'.$this->table.'.%'.$k.' = :'.$k.'_'.$cond_num;
					$arr_cond[$k.'_'.$cond_num] = $v;
					$cond_num++;
				}
				unset($v, $k);
				$conditions .= implode(' AND ', $cond);
			}
			unset($cond_num, $cond);

			$sql .= $conditions; //On rajoute les conditions à la requête

		}

		if(isset($req['groupBy'])) { $sql .= ' GROUP BY %'.$req['groupBy']; }

		if(isset($req['orderby'])) {
			if (strpos($req['order'], $this->table) === false) {
				$req['order'] = '%'.$this->table.'.'.$req['order'];
			}
			$sql .= ' ORDER BY '.$req['order'].' ';
		}
		if (isset($req['orderBy']) && isset($req['orderType'])) {
			$sql .= ' '.$req['orderType'].' ';
		} elseif (isset($req['orderBy']) && isset($req['orderType'])) {
			$sql .= ' ASC ';
		}

		if(isset($req['limit'])) { $sql .= ' LIMIT '.$req['limit'].' '; }

		if (isset($req['datas'])) {
			$datas = array_merge($arr_cond, $req['datas']);
		} else {
			$datas = $arr_cond;
		}

		$req_type = isset($req['type']) ? $req['type'] : 'req';
		if ($req_type !== 'req' && $req_type !== 'row' && $req_type !== 'noRes') { $req_type = 'req'; }
		return $this->$req_type($sql, $datas);
	}

}