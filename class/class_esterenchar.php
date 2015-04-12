<?php


class EsterenChar {

	private $base_char = array();//Le personnage récupéré dans la BDD ou dans la session (utilisé pour voir les différences et stocker les modifications)
	private $char = array();//Le personnage en cours d'édition ou de modification
	private $id = 0;//L'id du personnage
	private $user_id = 0;//L'id de l'utilisateur associé au personnage

	/**
	 * L'objet de la classe bdd
	 * @var array
	 */
	private $db;

	/**
	 * Récupère l'id. Si en paramètre on a l'id de la session en cours, c'est qu'on enregistre temporairement un personnage
	 * @param string $param L'id de la session, sinon rien
	 * @return number L'id du personnage
	 */
	public function id($param = null) { if ($param === session_id()) { $this->id = $param; } return $this->id; }

	/**
	 * Récupère le nom du personnage
	 * @return string Le nom du personnage
	 */
	public function name() { return $this->get('details_personnage.name'); }

	/**
	 * Récupère l'utilisateur associé au personnage
	 * @return number L'id de l'utilisateur associé
	 */
	public function user_id() { return $this->user_id; }

	/**
	 * Cette fonction initialise l'instance de la classe pour créer un personnage à partir de ce que l'on passera en paramètre
	 *
	 * @param array|int $char Variable qui contient le personnage généré
	 * @param string $type Détermine si $char provient de la BDD. Si false, $char vient de la session
	 */
	function __construct($char = null, $type = 'db') {
		global $db;
		$this->db = $db;
		$method = '_make_char_from_'.$type;
		$ret = false;
		if (method_exists(__CLASS__, $method)) {
			$ret = $this->$method($char);
		}
		if ($ret === true && $this->id > 0 && !FileAndDir::dexists(CHAR_EXPORT.DS.$this->id)) {
			FileAndDir::createPath(CHAR_EXPORT.DS.$this->id);
		}
	}

	public static function session_clear() {
		global $db;
		$t = $db->req('SELECT %gen_step,%gen_mod,%gen_anchor FROM %%steps ORDER BY %gen_step ASC');//On génère la liste des étapes
		$steps = array();
		foreach ($t as $v) {//On formate la liste des étapes
			$steps[$v['gen_step']] = array(
				'step' => $v['gen_step'],
				'mod' => $v['gen_mod'],
				'title' => $v['gen_anchor'],
			);
		}
		foreach($steps as $v) { unset($_SESSION[$v['mod']]); }

		Session::write('etape', 1);
		unset($_SESSION['amelio_bonus'], $_SESSION['bonusdom']);
	}

	/**
	 * Récupère le nom urlencodé du personnage
	 *
	 * @param string $char Le nom à encoder
	 * @return string Le nom encodé
	 */
	function sget_url_charname($char = null) {
		$hash = '';
		if ($char) {
			$hash = md5(json_encode($this->char));
		}
		return $hash;
	}

	/**
	 * Génère un tableau d'état de santé en fonction de l'état de santé du PJ
	 *
	 * @return array Un tableau contenant les valeurs de chaque état de santé
	 */
	public function get_health_array() {
		$sante = $this->get('sante');
		$health_array = array(
			'Bon'		=> 5,
			'Moyen'		=> 5,
			'Grave'		=> 4,
			'Critique'	=> 4,
			'Agonie'	=> 1,
		);
		if ($sante >= 20) { $health_array['Grave']++; }
		if ($sante >= 21) { $health_array['Critique']++; }

		if ($sante <= 18) { $health_array['Moyen']--; }
		if ($sante <= 17) { $health_array['Critique']--; }
		return $health_array;
	}

	/**
	 * Génère un tableau déterminant la quantité de daols (braise, azur et givre) en fonction de l'argent initial
	 *
	 * @param int $argent La quantité d'argent. Si elle n'est pas mentionnée, l'argent du personnage sera récupéré automatiquement
	 * @return array Un tableau contenant les trois valeurs de daosl
	 */
	public function get_daols($argent = null) {
		if ($argent === null) {
			$argent = $this->get('inventaire.argent');
		} else {
			$argent = (int) $argent;
		}
		$braise = $azur = $givre = 0;

		do {
			if ($argent >= 100) {
				$givre += 1; $argent -= 100;
			} elseif ($argent >= 10) {
				$azur += 1; $argent -= 10;
			} elseif ($argent > 0) {
				$braise += 1; $argent -= 1;
			}
		} while ($argent > 0);
		return array(
			'braise' => $braise,
			'azur' => $azur,
			'givre' => $givre
		);
	}

	/**
	 * Récupère la valeur d'attaque de Tir et Lancer
	 *
	 * @param integer $disc Si une discipline est passée en paramètre, on renvoie le bonus d'attaque avec la discipline
	 * @return int Le score d'attaque
	 */
	public function get_attack_tir($disc = 0) {
		$tir = $this->get('domaines.14.val');	//Score de tir & lancer
		$tir += $this->get('voies.1.val');		//Score de combativité
		$tir += $this->get('domaines.14.bonus');//Bonus au tir & lancer
		$tir -= $this->get('domaines.14.malus');//Malus au tir & lancer
		$disc = (int) $disc;
		if ($disc > 0 && $this->get('domaines.14.disciplines.'.$disc)) {
			$tir += $this->get('domaines.14.disciplines.'.$disc.'.val') - 5;//Si la discipline, on l'ajoute et on enlève les 5 points de tir & lancer
		}
		return $tir;
	}

	/**
	 * Récupère la valeur d'attaque de Combat au Contact
	 *
	 * @param integer $disc Si une discipline est passée en paramètre, on renvoie le bonus d'attaque avec la discipline
	 * @return int Le score d'attaque
	 */
	public function get_attack_cac($disc = 0) {
		$cac = $this->get('domaines.2.val');	//Score de combat au contact
		$cac += $this->get('voies.1.val');		//Score de combativité
		$cac += $this->get('domaines.2.bonus');//Bonus au combat au contact
		$cac -= $this->get('domaines.2.malus');//Malus au combat au contact
		$disc = (int) $disc;
		if ($disc > 0 && $this->get('domaines.2.disciplines.'.$disc)) {
			$cac += $this->get('domaines.2.disciplines.'.$disc.'.val') - 5;//Si la discipline, on l'ajoute et on enlève les 5 points de combat au contact
		}
		return $cac;
	}

	/**
	 * Cette fonction est utilisée pour pouvoir générer les feuilles de personnage à partir d'une instance existante
	 *
	 * @param string $img_type Détermine le type de l'image, cela va lancer la fonction éponyme
	 * @param boolean $printer_friendly Détermine si l'on crée une image printer friendly ou pas
	 * @param array $pages Contient la listes des pages à générer
	 * @return mixed False si la fonction n'existe pas ou qu'une erreur est survenue dans la méthode
	 */
	public function export_to_img($img_type = 'original', $printer_friendly = false, $pages = array(1,2,3)) {

		$method = '_make_sheet_from_'.$img_type;
		if (method_exists(__CLASS__, $method)) {
			return $this->$method($pages, $printer_friendly);
		} else {
			return false;
		}
	}

	/**
	 * Cette fonction est utilisée pour pouvoir générer les feuilles de personnage au format PDF à partir d'une instance existante
	 *
	 * @param string $sheet_style Détermine le type de l'image, cela va lancer la fonction éponyme
	 * @param boolean $printer_friendly Détermine si l'on crée une image printer friendly ou pas
	 * @return boolean True si réussi, False sinon
	 */
	public function export_to_pdf($sheet_style = 'original', $printer_friendly = false) {
		$method = '_make_pdf_from_'.$sheet_style;
		if (method_exists(__CLASS__, $method)) {
			return $this->$method($printer_friendly);
		} else {
			return false;
		}
	}

	/**
	 * Crée le personnage dans la base de données
	 *
	 * @param int $user_id Si l'utilisateur est mentionné, on associe ce personnage au personnage enregistré
	 * @return boolean True si réussi, False sinon
	 */
	public function export_to_db($user_id = 0) {
		if ($user_id) {
			$user = $this->db->row('SELECT %user_id FROM %%users WHERE %user_id = ?', array($user_id));
			if ($user) { $user_id = $user['user_id']; } else { $user_id = 0; }
		}
		$search = $this->db->row('SELECT COUNT(*) as %nb_chars FROM %%characters WHERE %char_name = ? AND %user_id = ?', array($this->get('details_personnage.name'), $user_id));
		if (!empty($search) && isset($search['nb_chars']) && $search['nb_chars'] > 0) {
			Session::setFlash('Un personnage du même nom existe déjà.', 'error');
			if (!$user_id) { Session::setFlash('<br />Si vous souhaitez vraiment créer un personnage avec ce nom, vous pouvez créer un compte, ainsi le personnage sera enregistré pour vous avec ce nom.<br />Vous pouvez toujours changer le nom du personnage à l\'étape précédente', 'error'); }
			return false;
		}
		$datas = array(
			'user_id' => $user_id,
			'char_name' => $this->get('details_personnage.name'),
			'char_job' => $this->get('metier.id') ? $this->get('metier.id') : $this->get('metier.name'),
			'char_origin' => $this->get('region_naissance.id'),
			'char_people' => $this->get('peuple'),
			'char_content' => $this->_encrypt(),
			'char_date_creation' => time(),
		);
        $ret = $this->db->noRes('INSERT INTO %%characters SET %%%fields', $datas);
        $id = $this->db->last_id();
        $this->id = $id;
		return $ret;
	}

	/**
	 * Met à jour le personnage dans la base de données
	 *
	 * @return boolean True si réussi, False sinon
	 */
	public function update_to_db() {
		global $_PAGE;

		$compare_after = p_array_diff_recursive($this->char, $this->base_char, true);
		$compare_before = p_array_diff_recursive($this->base_char, $this->char, true);
// 		if (!empty($compare_after) && !empty($compare_before)) {
			$compare_before = $this->_encrypt($compare_before);
			$compare_after = $this->_encrypt($compare_after);
			$datas_compare = array(
				'charmod_content_before' => $compare_before,
				'charmod_content_after' => $compare_after,
				'charmod_date' => time(),
				'charmod_page_module' => $_PAGE['get'],
				'charmod_page_request' => $this->_encrypt($_PAGE['request']),
				'char_id' => $this->id,
				'user_id' => Users::$id,
			);
			$sql2 = 'INSERT INTO %%charmod SET %%%fields';
// 		}

		$datas = array(
			'char_name' => $this->get('details_personnage.name'),
			'char_job' => $this->get('metier.id') ? $this->get('metier.id') : $this->get('metier.name'),
			'char_origin' => $this->get('region_naissance.id'),
			'char_people' => $this->get('peuple'),
			'char_content' => $this->_encrypt(),
			'char_id' => $this->id,
			'char_date_update' => time(),
		);
		FileAndDir::remove_directory(CHAR_EXPORT.DS.$this->id.DS);
		$sql = 'UPDATE %%characters
			SET %char_content = :char_content,
				%char_name = :char_name,
				%char_job = :char_job,
				%char_origin = :char_origin,
				%char_people = :char_people,
				%char_date_update = :char_date_update
			WHERE %char_id = :char_id ';
		return $this->db->noRes($sql, $datas) &&
			((!empty($sql2) && isset($datas_compare)) ? $this->db->noRes($sql2, $datas_compare) : true);
	}

	/**
	 * Détruit le personnage dans la base de données
	 */
	public function delete_char() {
		if (!$this->id) {
			return false;
		}

		$req = $this->db->row('SELECT %char_id, %char_name FROM %%characters WHERE %char_id = ?', array($this->id));

		if ($req === false) {
			Session::setFlash('Le personnage n\'a pas été trouvé dans la base de données, il a peut-être déjà été supprimé.', 'warning');
			return false;
		} elseif ($req && isset($req['char_id']) && isset($req['char_name'])) {
			$this->char = array();
			if ($this->update_to_db()) {
				$ret = $this->db->noRes('DELETE FROM %%characters WHERE %char_id = ?', array($req['char_id']));
				if (!$ret) {
					Session::setFlash('Une erreur est survenue lors de la suppression du personnage. #001', 'error');
				}
				return $ret;
			} else {
				Session::setFlash('Erreur lors de la mise à jour du personnage', 'error');
				return false;
			}
		} else {
			Session::setFlash('Une erreur inconnue est survenue lors de la suppression du personnage. #002', 'error');
			return false;
		}
	}

	/**
	 * Réinitialise les valeurs du personnage pour mettre à jour les éléments en fonction de la BDD
	 */
	public function clean_char() {
// 		$t = $this->db->req('SELECT %disc_id, %disc_name FROM %%disciplines WHERE %disc_rang = ?', array('Professionnel'));
// 		pr($t);

	}

	/**
	 * Met à jour le tableau $this->char du personnage
	 * Cette fonction gère également les incrémentations et décrémentations avec un préfixe "+=" ou "-="
	 *
	 * @return boolean True si réussi, False sinon
	 */
	public function set($path = null, $value = null) {
		$get_value = Hash::get((array)$this->char, $path);

		if (is_string($value) && strpos($value, '+=') === 0) {
			$value = str_replace('+=', '', $value);
			$get_value += $value;
		} elseif (is_string($value) && strpos($value, '-=') === 0) {
			$value = str_replace('-=', '', $value);
			$get_value += $value;
		} else {
			$get_value = $value;
		}

		$char = Hash::insert((array)$this->char, $path, $get_value); //On insère les données et on récupère la nouvelle variable
		$this->char = $char; //On affecte les données à la variable
		return (Hash::get((array)$this->char, $path) === $get_value); //On retourne le résultat de la fonction
	}

	/**
	 * Récupère une information du personnage avec Hash::get
	 *
	 * @return mixed
	 */
	public function get($path = null) {
		if ($path === '' || $path === null) {
			return $this->char;
		} else {
			$element = Hash::get($this->char, $path);
			//$element = isset($element[0]) ? $element[0] : '';
			return $element; //On récupère un élément du personnage en fonction du chemin donné
		}
	}

	/**
	 * Récupère une information du personnage avec Hash::extract (pour utiliser les chemins plus précis)
	 *
	 * @return boolean True si réussi, False sinon
	 */
	public function extract($path = null) {
		if ($path === '' || $path === null) {
			return $this->char;
		} else {
			$element = Hash::extract($this->char, $path);
			$element = isset($element[0]) ? $element[0] : '';
			return $element; //On récupère un élément du personnage en fonction du chemin donné
		}
	}

	/**
	 * Cette fonction permet de décoder le contenu en paramètre, normalement issu du personnage
	 *
	 * @param string $content Le contenu crypté
	 * @return array
	 */
	public static function sdecode_char($cnt) {
		return json_decode($cnt, true);
	}

	/**
	 * Cette fonction se charge de créer le personnage à partir d'un contenu envoyé, et de le placer dans la variable $char de l'objet
	 *
	 * @param string $content Le contenu crypté
	 * @return boolean
	 */
	private function _decode_char($cnt) {
		if ($cnt) {
			$cnt = self::sdecode_char($cnt);
			if ($cnt) {
				$this->char = $cnt;
				$this->base_char = $cnt;
				return true;
			} else {
				echo '<div class="container error">Le contenu du personnage est incorrect. #001</div>';
				return false;
			}
		} else {
			echo '<div class="container error">Le contenu du personnage a été mal récupéré. #002</div>';
			return false;
		}
	}

	/**
	 * Cette fonction est utilisée pour générer un export de $this->char
	 *
	 * @return boolean True si réussi, False sinon
	 */
	private function _encrypt($data = null) {
		if ($data === null) {
			$data = $this->char;
		}
		$export = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
		return $export;
	}

	/**
	 * Cette fonction crée un personnage à partir des variables de session
	 *
	 * @param array $char Variable qui contient le personnage généré
	 * @return array
	 */
	private function _make_char_from_session($char) {
		$err = array();
		if (!$char || empty($char)) {
			return array();
		}
		$t = array();
		unset($char['bonusdom']);
		foreach($char as $k => $v) {
			$k = preg_replace('#^[0-9]+_#isU', '', $k);
			$t[$k] = $v;
		}
		$char = $t;//On définit la variable $char qui sera à la fin envoyé à $this->char

		/*
		 On définit quelques variables qui pourront être modifiées au fur et à mesure, en fonction des choix du joueur
		Toutes ces variables sont potentiellement modifiables, notamment par des avantages ou désavantages.
		*/
		$argent = 0;		//L'argent sera compté automatiquement en daols de braise, le calcul sera fait a posteriori en un décompte de dg, da et db
		$trauma = 0;		//Par défaut, aucun traumatisme
		$sante = 19;		//19 points de santé au départ comme tout le monde
		$vigueur = 10;		//10 de vigueur par défaut
		$defense = 5;		//5 de défense par défaut, on rajoutera Raison et Empathie plus tard
		$survie = 3;		//3 points de survie de base
		$resist_mentale = 5;//5 de résistance, on y ajoutera l'idéal plus tard
		$rapidite = 0;		//On ajoutera plus tard combativité et empathie
		$rindath = 0;		//Bonus au rindath dépendant des disciplines
		$exaltation = 0;	//Bonus au rindath dépendant des disciplines

		/*------------VOIES------------*/
		if (isset($char['voies']) && !empty($char['voies'])) {
			$t = $this->db->req('SELECT %voie_id, %voie_name FROM %%voies');
			$voies = array();
			foreach ($t as $v) {
				$voies[$v['voie_id']] = array(
					'id' => (int) $v['voie_id'],
					'name' => $v['voie_name'],
					'val' => $char['voies'][$v['voie_id']]
				);
			}
			$this->set('voies', $voies);
		} else {
			$err[] = 'Voies';
		}

		/*------------MÉTIER------------*/
		if (isset($char['metier']) && !empty($char['metier']) && (is_string($char['metier']) || is_numeric($char['metier']))) {
			if (is_string($char['metier'])) {
				$this->set('metier', array(
					'id' => 0,
					'name' => $char['metier'],
					'description' => '(Métier personnalisé)'
				));
			} elseif (is_numeric($char['metier'])) {
				$job = $this->db->row('SELECT %job_name,%job_desc FROM %%jobs WHERE %job_id = ?', array($char['metier']));
				$this->set('metier', array(
					'id' => (int) $char['metier'],
					'name' => $job['job_name'],
					'description' => $job['job_desc']
				));
			}
		} else {
			$err[] = 'Métier';
		}

		/*------------LIEU DE NAISSANCE------------*/
		if (isset($char['naissance']) && !empty($char['naissance']) && (int) $char['naissance']) {
			$region = $this->db->row('SELECT %region_name,%region_kingdom,%region_desc FROM %%regions WHERE %region_id= ?', array($char['naissance']));
			$this->set('region_naissance', array(
				'id' => (int) $char['naissance'],
				'name' => $region['region_name'],
				'royaume' => $region['region_kingdom'],
				'description' => $region['region_desc']
			));
		} else {
			$err[] = 'Lieu de naissance';
		}

		/*------------TRAITS DE CARACTÈRE------------*/
		if (isset($char['traits']) && !empty($char['traits']) && is_array($char['traits'])) {
			$char['traits'] = array_map('intval', $char['traits']);
			$t = $this->db->req('SELECT %trait_name,%trait_name_female,%trait_id,%trait_qd FROM %%traitscaractere WHERE %trait_id IN ('.implode(',', $char['traits']).')');
			$traits = array();
			foreach($t as $v) {
				if ($v['trait_qd'] == 'q') {
					$traits['qualite'] = array(
						'id' => (int) $v['trait_id'],
						'name' => (isset($char['description_histoire']['sex']) && $char['description_histoire']['sex'] === 'Femme') ? $v['trait_name_female'] : $v['trait_name'],
					);
				} else {
					$traits['defaut'] = array(
						'id' => (int) $v['trait_id'],
						'name' => (isset($char['description_histoire']['sex']) && $char['description_histoire']['sex'] === 'Femme') ? $v['trait_name_female'] : $v['trait_name'],
					);
				}
			}
			unset($t);
			$this->set('traits_caractere', $traits);
		} else {
			$err[] = 'Traits de caractère';
		}

		/*------------DOMAINES------------*/
		if (
			isset($char['domaines_primsec']) && !empty($char['domaines_primsec'])
			&& isset($char['domaines_amelio'])
			&& isset($char['bonusdom'])
			&& isset($char['disciplines'])
		) {
			$t = $this->db->req('SELECT %domain_id,%domain_name,%domain_desc FROM %%domains');

			$primsec = $char['domaines_primsec'];
			if (isset($primsec['lettre'])) { $lettre = $primsec['lettre']; }

			$amelio = $char['domaines_amelio'];
			$bonusdom = $char['bonusdom'];
			$domaines = array();
			foreach($t as $v) {
				$v['domain_id'] = (int) $v['domain_id'];
				$id = $v['domain_id'];
				$v['val'] = 0;
				if (isset($amelio[$id]))	{ $v['val'] += (int) $amelio[$id]['primsec'] + (int) $amelio[$id]['curval']; }
				if (isset($bonusdom[$id]))	{ $v['val'] += (int) $bonusdom[$id]; }
				$domaines[$id] = array(
					'id' => (int) $v['domain_id'],
					'name' => $v['domain_name'],
					'description' => $v['domain_desc'],
					'val' => $v['val'],
					'disciplines' => array(),
					'bonus' => 0,
					'malus' => 0
				);
			}
			unset($id,$v,$t);
			if (isset($lettre)) {
				$domaines[$lettre]['bonus'] += 1;
			}
			$this->set('domaines', $domaines);
		} else {
			$err[] = 'Définition des domaines';
		}

		/*------------AVANTAGES------------*/
		if (isset($char['des_avtg']) && !empty($char['des_avtg']) && is_array($char['des_avtg'])) {
			$avtg = array_keys($char['des_avtg']['avantages']);
			$avtg = array_map('intval', $avtg);
			$avtgs = array();
			if (!empty($avtg)) {
				$t = $this->db->req('SELECT %avdesv_id,%avdesv_name,%avdesv_name_female,%avdesv_bonusdisc FROM %%avdesv WHERE %avdesv_id IN ('.implode(',', $avtg).')');
				foreach($t as $v) {
					$val = (int) $char['des_avtg']['avantages'][$v['avdesv_id']];
					$bonuses = $v['avdesv_bonusdisc'];
					$bonuses = explode(',', $bonuses);
					foreach($bonuses as $bonus) {
						if (preg_match('#^[0-9]+[abg]$#isU', $bonus)) {//Bonus lié aux daols
							$daol = preg_replace('#^[0-9]+([abg])$#isU', '$1', $bonus);
							$bonus = (int) preg_replace('#^([0-9]+)[abg]$#isU', '$1', $bonus);
							if ($daol == 'b') {//daols de braise
								$argent += $bonus;
							} elseif ($daol == 'a') {//Daols d'azur
								$argent += $bonus*10;
							} elseif ($daol == 'g') {//Daols de givre
								$argent += $bonus*100;
							}
						} elseif ($bonus == 'resm') {//Bonus à la résistance mentale
							$resist_mentale += $val;
						} elseif ($bonus == 'vig') {//Bonus à la vigueur
							$vigueur += $val;
						} elseif ($bonus == 'rap') {//Bonus à la rapidité
							$rapidite += $val;
						} elseif ($bonus == 'sur') {//Bonus à la survie
							$survie += $val;
						} elseif ($bonus == 'def') {//Bonus à la défense
							$defense += $val;
						} elseif ($bonus == 'bless') {//Bonus à la santé
							$sante += $val;
						} elseif ($bonus == 'trau') {//Malus au traumatisme
							$trauma -= $val;
						} elseif (is_numeric($bonus)) {
							$bonus = (int) $bonus;
							$this->set('domaines.'.$bonus.'.bonus', $this->get('domaines.'.$bonus.'.bonus') + $val);
						}
					}
					$avtgs[$v['avdesv_id']] = array(
						'id' => (int) $v['avdesv_id'],
						'name' => (isset($char['description_histoire']['sex']) && $char['description_histoire']['sex'] === 'Femme') ? $v['avdesv_name_female'] : $v['avdesv_name'],
						'val' => $val
					);
				}
			}
			unset($t);
			$this->set('avantages', $avtgs);
		} else {
			$err[] = 'Avantages';
		}

		/*------------DÉSAVANTAGES------------*/
		if (isset($char['des_avtg']) && !empty($char['des_avtg']) && is_array($char['des_avtg'])) {
			$desv = array_keys($char['des_avtg']['desavantages']);
			if (!$desv) { $desv = array(); }
			$desv = array_map('intval', $desv);
			$desvs = array();
			if (!empty($desv)) {
				$t = $this->db->req('SELECT %avdesv_id,%avdesv_name,%avdesv_name_female,%avdesv_bonusdisc FROM %%avdesv WHERE %avdesv_id IN ('.implode(',', $desv).')');
				foreach($t as $v) {
					$val = (int) $char['des_avtg']['desavantages'][$v['avdesv_id']];
					$bonuses = $v['avdesv_bonusdisc'];
					$bonuses = explode(',', $bonuses);
					foreach($bonuses as $bonus) {
						if (preg_match('#^[0-9]+[abg]$#isU', $bonus)) {//Bonus lié aux daols
							if (strpos($bonus, 'b') !== false) {//daols de braise
								$argent -= $val;
							} elseif (strpos($bonus, 'a') !== false) {//Daols d'azur
								$argent -= $val*10;
							} elseif (strpos($bonus, 'g') !== false) {//Daols de givre
								$argent -= $val*100;
							}
						} elseif ($bonus == 'resm') {//Malus à la résistance mentale
							$resist_mentale -= $val;
						} elseif ($bonus == 'vig') {//Malus à la vigueur
							$vigueur -= $val;
						} elseif ($bonus == 'rap') {//Malus à la rapidité
							$rapidite -= $val;
						} elseif ($bonus == 'sur') {//Malus à la survie
							$survie -= $val;
						} elseif ($bonus == 'def') {//Malus à la défense
							$defense -= $val;
						} elseif ($bonus == 'bless') {//Malus à la santé
							$sante -= $val;
						} elseif ($bonus == 'trau') {//Bonus à la santé
							$trauma += $val;
						} elseif (is_numeric($bonus)) {
							$bonus = (int) $bonus;
							$this->set('domaines.'.$bonus.'.malus', $this->get('domaines.'.$bonus.'.malus') + $val);
						}
					}
					$desvs[$v['avdesv_id']] = array(
						'id' => (int) $v['avdesv_id'],
						'name' => (isset($char['description_histoire']['sex']) && $char['description_histoire']['sex'] === 'Femme') ? $v['avdesv_name_female'] : $v['avdesv_name'],
						'val' => $val
					);
				}
			}
			unset($t);
			$this->set('desavantages', $desvs);
		} else {
			$err[] = 'Avantages';
		}

		/*------------REVERS------------*/
		if (isset($char['revers'])) {
			if (is_array($char['revers']) && !empty($char['revers']) && $char['revers'] != array(0=>'0')) {
				$char['revers'] = array_map('intval', $char['revers']);
				$t = $this->db->req('SELECT %rev_id,%rev_name,%rev_desc,%rev_malus FROM %%revers WHERE %rev_id IN ('.implode(',', $char['revers']).')');
				$revers = array();
				foreach($t as $v) {
					$revers[$v['rev_id']] = array(
						'id' => (int) $v['rev_id'],
						'name' => $v['rev_name'],
						'description' => $v['rev_desc']
					);
					if ($v['rev_malus'] == 'vig') {
						$vigueur -= 1;
					} elseif ($v['rev_malus'] == 'trauma') {
						$trauma += 1;
					} elseif ($v['rev_malus'] == '0g') {
						$argent = 0;
					}
				}
				unset($t);
			} else { $revers = array(); }
			$this->set('revers', $revers);
		} else {
			$err[] = 'Revers';
		}

		/*------------DÉSORDRE MENTAL------------*/
		if (isset($char['sante_mentale']) && !empty($char['sante_mentale']) && (int) $char['sante_mentale']) {
			$desordres = $this->db->row('SELECT %desordre_name FROM %%desordres WHERE %desordre_id = ?', array($char['sante_mentale']));
			$this->set('desordre_mental', array(
				'id' => (int) $char['sante_mentale'],
				'name' => $desordres['desordre_name'],
				//'description' => $desordres['desordre_desc']
			));
		} else {
			$err[] = 'Désordre mental';
		}

		/*------------DESCRIPTION------------*/
		if (isset($char['description_histoire'])
		&& !empty($char['description_histoire'])
		&& isset($char['description_histoire']['sex'])
		&& ($char['description_histoire']['sex'] == 'Homme' || $char['description_histoire']['sex'] == 'Femme')
		&& isset($char['description_histoire']['name'])
		&& isset($char['description_histoire']['player'])
		&& isset($char['description_histoire']['histoire'])
		&& isset($char['description_histoire']['description'])
		) {
			$this->set('details_personnage', array(
				'name' => $char['description_histoire']['name'],
				'sexe' => $char['description_histoire']['sex'],
				'joueur' => $char['description_histoire']['player'],
				'histoire' => $char['description_histoire']['histoire'],
				'faits' => $char['description_histoire']['faits'],
				'description' => $char['description_histoire']['description']
			));
		} else {
			$err[] = 'Détails du personnage, description, histoire, etc.';
		}

		/*------------ÉQUIPEMENTS------------*/
		if (isset($char['equipements'])
		&& !empty($char['equipements'])
		&& isset($char['equipements']['arme'])
		&& isset($char['equipements']['armure'])
		&& isset($char['equipements']['autre_equip'])
		) {
			if ($char['equipements']['arme']) {
				$arme = $this->db->req('SELECT %arme_id,%arme_name,%arme_dmg,%arme_domain FROM %%armes WHERE %arme_id IN ('.implode(',', $char['equipements']['arme']).')');
				$armes = array();
				foreach ($arme as $v) {
					$doms = explode(',', $v['arme_domain']);
					$armes_dom = array();
					foreach($doms as $d) { $armes_dom[$d] = $this->get('domaines.'.$d.'.name'); }
					$armes[$v['arme_id']] = array(
						'id' => $v['arme_id'],
						'name' => $v['arme_name'],
						'degats' => $v['arme_dmg'],
						'domaines' => $armes_dom
					);
				}
			} else {
				$armes = array();
			}
			if ($char['equipements']['armure']) {
				$armure = $this->db->req('SELECT %armure_id,%armure_name,%armure_prot FROM %%armures WHERE %armure_id IN ('.implode(',', $char['equipements']['armure']).')');
				$armures = array();
				foreach ($armure as $v) {
					$armures[$v['armure_id']] = array(
						'id' => $v['armure_id'],
						'name' => $v['armure_name'],
						'protection' => $v['armure_prot'],
					);
				}
			} else {
				$armures = array();
			}
			$poss = $char['equipements']['autre_equip'];
			$poss = preg_split('#\n#', $poss);
			foreach($poss as $k => $v) { if (!$v) { unset($poss[$k]); } }
			$this->set('inventaire', array(
				'armes' => $armes,
				'armures' => $armures,
				'possessions' => $poss
			));
		} else {
			$err[] = 'Détails du personnage, description, histoire, etc.';
		}

		/*------------DISCIPLINES------------*/
		if (isset($char['disciplines'])) {

			$disc = $char['disciplines'];
			if (!empty($disc)) {
				$ids = array_map('intval', array_keys($disc));
				$doms = array();
				foreach($disc as $v) { $doms[$v['domain']] = $v['domain']; }//On récupère les domaines pour ne pas avoir de doublon de disciplines
				$discs = $this->db->req('
					SELECT %%disciplines.%disc_name, %%discdoms.%disc_id, %%discdoms.%domain_id
					FROM %%discdoms
					INNER JOIN %%disciplines ON %%disciplines.%disc_id = %%discdoms.%disc_id
					WHERE %%disciplines.%disc_id IN ('.implode(',',$ids).')
					AND %%discdoms.%domain_id IN ('.implode(',',$doms).')');
				foreach($discs as $d) {
					if ($d['disc_id'] == 95) { $rindath += 5; }
					if ($d['disc_id'] == 74) { $exaltation += 5; }
					$this->set('domaines.'.$d['domain_id'].'.disciplines.'.$d['disc_id'], array(
						'id' => $d['disc_id'],
						'name' => $d['disc_name'],
						'val' => 6
					));
				}
			}
		}

		/*------------ORIENTATION DE LA PERSONNALITÉ------------*/
		if (isset($char['orientation']) && !empty($char['orientation'])) {
			$this->set('orientation', array(
				'name' => (string) $char['orientation'],
				'instinct' => $this->get('voies.1.val') + $this->get('voies.2.val'),
				'conscience' => $this->get('voies.4.val') + $this->get('voies.5.val')
			));
			$trauma += abs($this->get('orientation.instinct') - $this->get('orientation.conscience'));
		} else {
			$err[] = 'Orientation de la personnalité';
		}

		/*------------RÉSIDENCE GÉOGRAPHIQUE------------*/
		if (isset($char['geo']) && !empty($char['geo'])) {
			$this->set('residence_geographique', $char['geo']);
		} else {
			$err[] = 'Résidence géographique';
		}

		/*------------CLASSE SOCIALE------------*/
		if (isset($char['classe']) && !empty($char['classe']) && isset($char['classe']['classe']) && !empty($char['classe']['classe'])) {
			$this->set('classe_sociale', $char['classe']['classe']);
		} else {
			$err[] = 'Classe sociale';
		}

		/*------------ÂGE------------*/
		if (isset($char['age']) && !empty($char['age']) && (int) $char['age']) {
			$this->set('age', $char['age']);
		} else {
			$err[] = 'Âge';
		}

		/*------------POTENTIEL------------*/
		if ($this->get('voies.2.val') == 1) {
			$this->set('potentiel', 1);
		} elseif ($this->get('voies.2.val') >= 2 && $this->get('voies.2.val') <= 4) {
			$this->set('potentiel', 2);
		} elseif ($this->get('voies.2.val') == 5) {
			$this->set('potentiel', 3);
		} else {
			$err[] = 'Potentiel :';
		}

		/*------------PEUPLE------------*/
		if (isset($char['peuple']) && !empty($char['peuple'])) {
			$this->set('peuple', $char['peuple']);
		} else {
			$err[] = 'Peuple';
		}

		/*
		$argent = 0;		//L'argent sera compté automatiquement en daols de braise, le calcul sera fait a posteriori en un décompte de dg, da et db
		$trauma = 0;		//Par défaut, aucun traumatisme
		$sante = 19;		//19 points de santé au départ comme tout le monde
		$vigueur = 10;		//10 de vigueur par défaut
		$defense = 5;		//5 de défense par défaut, on rajoutera Raison et Empathie plus tard
		$survie = 3;		//3 points de survie de base
		$resist_mentale = 5;//5 de résistance, on y ajoutera l'idéal plus tard
		$rapidite = 0;		//On ajoutera plus tard combativité et empathie
		*/
		$this->set('inventaire.argent', $argent);
		$this->set('resistance_mentale', array(
			'val' => $resist_mentale + $this->get('voies.5.val'),
			'exp' => 0
		));
		$this->set('sante', $sante);
		$this->set('vigueur', $vigueur);
		$this->set('defense', array(
			'base' => $defense + $this->get('voies.3.val') + $this->get('voies.4.val'),
			'amelioration' => 0
		));
		$this->set('survie', $survie);
		$this->set('rapidite', array('base' => $rapidite + $this->get('voies.1.val') + $this->get('voies.3.val'), 'amelioration' => 0));
		$this->set('traumatismes', array('permanents' => $trauma, 'curables' => 0));
		$this->set('rindath', array(
			'val' => $this->get('voies.1.val') + $this->get('voies.2.val') + $this->get('voies.3.val') + $rindath,
			'max' => $this->get('voies.1.val') + $this->get('voies.2.val') + $this->get('voies.3.val') + $rindath
		));
		$this->set('exaltation', array(
			'val' => $this->get('voies.5.val') * 3 + $exaltation,
			'max' => $this->get('voies.5.val') * 3 + $exaltation
		));

		$this->set('ogham', array());
		$this->set('miracles', array(
			'majeurs' => array(),
			'mineurs' => array()
		));
		$this->set('artefacts', array());
		$this->set('flux', array(
			'mineral' => 0,
			'vegetal' => 0,
			'organique' => 0,
			'fossile' => 0
		));

		$this->set('arts_combat', array());
		if (isset($char['arts_combat']) && !empty($char['arts_combat']) && $char['arts_combat'] != array(0=>0) && is_array($char['arts_combat'])) {
			$arts_ids = array_keys($char['arts_combat']);
			$arts = $this->db->req('SELECT %avdesv_name,%avdesv_id FROM %%avdesv WHERE %avdesv_id IN ('.implode(',', $arts_ids).')');
			foreach($arts as $v) {
				$id = $v['avdesv_id'];
				$this->set('arts_combat.'.$id, array(
					'id' => $id,
					'name' => preg_replace('#Arts? de combat \(([^)]+)\)#isU', '$1', $v['avdesv_name']),
				));
			}
			unset($id);
		}

		$baseExp = getXPFromAvtg($char['des_avtg'], 100);
		$baseExp = getXPFromDoms($char['domaines_amelio'], $baseExp);
		$baseExp = getXPFromDiscs($char['disciplines'], $baseExp);
		foreach($this->get('arts_combat') as $v) { if (!empty($v)) { $baseExp -= 20; } }

		if ($baseExp > 100) {
			$this->set('experience.total', $baseExp);
			$this->set('experience.reste', $baseExp);
		} else {
			$this->set('experience.total', 100);
			$this->set('experience.reste', $baseExp);
		}

		if (!empty($err)) {//S'il y a une erreur
			//$obj_vars = get_object_vars($this);//On récupère les variables de l'objet
			$this->char = array();
			foreach($err as $k => $v) {
				$err[$k] = '<span class="icon-arrow-right"></span> '.$v;
			}
			echo '<p>Une erreur est survenue lors du calcul des caractéristiques suivantes :<br />'.implode('<br />', $err).'</p>';//On affiche l'erreur en live
			return false;
		}//end if !empty($err)
		return true;

	}

	/**
	 * Cette fonction se charge de créer le personnage à partir de la BDD
	 *
	 * @param int $id Contient l'identifiant du personnage dans la BDD
	 * @return array
	 */
	private function _make_char_from_db($id = 0) {
		if ($id) {
			$char_content = $this->db->row('SELECT %char_content, %user_id FROM %%characters WHERE %char_id = ?', $id);
			if ($char_content && isset($char_content['char_content']) && !empty($char_content['char_content'])) {
				$this->user_id = $char_content['user_id'];
				$this->id = $id;
				return $this->_decode_char($char_content['char_content']);
			} else {
				echo '<div class="container error">Aucun personnage trouvé.</div>';
				if (P_DEBUG === true) { pr('Id recherché : '.$id); }
				return false;
			}
		} else {
			echo '<div class="container error">Une erreur est survenue pendant la récupération du personnage dans la base de données. #001</div>';
			return false;
		}
	}

	/**
	 * Cette fonction se charge de créer le personnage à partir du contenu
	 *
	 * @param int $id Contient l'identifiant du personnage dans la BDD
	 * @return array
	 */
	/*
	private function _make_char_from_content($cnt = '') {
		if ($id) {
			$char_content = $this->db->row('SELECT %char_content, %user_id FROM %%characters WHERE %char_id = ?', $id);
			if ($char_content && isset($char_content['char_content']) && !empty($char_content['char_content'])) {
				$this->user_id = (int) $char_content['user_id'];
				$this->id = (int) $id;
				return $this->_decode_char($char_content['char_content']);
			} else {
				echo '<div class="container error">Aucun personnage trouvé.</div>';
				if (P_DEBUG === true) { pr('Id recherché : '.$id); }
				return false;
			}
		} else {
			echo '<div class="container error">Une erreur est survenue pendant la récupération du personnage dans la base de données. #001</div>';
			return false;
		}
		echo '<div class="container error">Une erreur est survenue pendant la récupération du personnage dans la base de données. #002</div>';
		return false;
	}
	//*/

	/**
	 * Cette fonction se charge de générer les feuilles de personnage au format PDF selon la feuille originale des Ombres d'Esteren
	 *
	 * @return boolean True si réussi, False sinon
	 */
	private function _make_pdf_from_original($printer_friendly = false) {

		$general_width = 893;
		$general_height = 1263;
		$pdf = new tFPDF('P', 'pt');
		$pdf->SetCompression(false);
		$p = array(
			'lettrine' => array(
				'file' => P_FONTS.DS.'LettrinEsteren-Regular.ttf',
				'name' => 'lettrinesteren-regular',
			),
            'unz' => array(
                'file' => P_FONTS.DS.'UnZialish.ttf',
                'name' => 'unzialish',
            ),
			'caro' => array(
				'file' => P_FONTS.DS.'carolingia.ttf',
				'name' => 'carolingia',
			),
			'carbold' => array(
				'file' => P_FONTS.DS.'carolingia_old.ttf',
				'name' => 'carolingia_old',
			),
			'times' => array(
				'file' => P_FONTS.DS.'times.ttf',
				'name' => 'times',
			),
			'arial' => array(
				'file' => P_FONTS.DS.'arial.ttf',
				'name' => 'arial',
			),
		);
		foreach ($p as $key => $v) {
			$pdf->AddFont($v['name'], '', $v['file'], true);
		}
		unset($key, $v, $str);


		/*--------------------------------*/
		/*---------PREMIÈRE FICHE---------*/
		/*--------------------------------*/

		//*-----------------------------------
		$pdf->AddPage('', array($general_width, $general_height));
		$pdf->Image(WEBROOT.DS.'files'.DS.'sheets'.DS.'esteren1'.($printer_friendly === true ? '-pf' : '').'_'.P_LANG.'.jpg', 0, 0, $general_width, $general_height);

		$pdf->textbox($this->get('details_personnage.name'), 213, 280, $p['lettrine'], 25, 370);

		$pdf->textbox($this->get('details_personnage.joueur'), 880, 280, $p['lettrine'], 21, 230);

		$pdf->textline(substr(tr($this->get('details_personnage.sexe'), true), 0, 1), 215, 322, $p['times'], 18);
		$pdf->textline(substr($this->get('age'), 0, 3), 343, 322, $p['caro'], 18);

        $description = tr($this->get('details_personnage.description'), true, null, 'characters.'.$this->id);
        $description = str_replace("\n", '', $description);
		$pdf->textline($description, 295, 365, $p['carbold'], 17);

		$pdf->textline(substr(tr($this->get('peuple'), true, null, 'create_char'), 0, 20), 530, 322, $p['lettrine'], 18);
		$pdf->textline(substr(tr($this->get('metier.name'), true, null, 'create_char'), 0, 25), 895, 322, $p['lettrine'], 18);

		// voies
		$pdf->textline($this->get('voies.1.val'), 325, 545, $p['carbold'], 28);
		$pdf->textline($this->get('voies.3.val'), 325, 608, $p['carbold'], 28);
		$pdf->textline($this->get('voies.2.val'), 325, 667, $p['carbold'], 28);
		$pdf->textline($this->get('voies.4.val'), 325, 727, $p['carbold'], 28);
		$pdf->textline($this->get('voies.5.val'), 325, 784, $p['carbold'], 28);

		// voies des domaines ligne 1
		$pdf->textline($this->get('voies.2.val'), 290, 990, $p['unz'], 22);
		$pdf->textline($this->get('voies.4.val'), 537, 990, $p['unz'], 22);
		$pdf->textline($this->get('voies.4.val'), 800, 990, $p['unz'], 22);
		$pdf->textline($this->get('voies.2.val'), 1069, 990, $p['unz'], 22);
		// voies des domaines ligne 2
		$pdf->textline($this->get('voies.1.val'), 298, 1169, $p['unz'], 22);
		$pdf->textline($this->get('voies.3.val'), 542, 1169, $p['unz'], 22);
		$pdf->textline($this->get('voies.5.val'), 802, 1169, $p['unz'], 22);
		$pdf->textline($this->get('voies.4.val'), 1060, 1169, $p['unz'], 22);
		// voies des domaines ligne 3
		$pdf->textline($this->get('voies.3.val'), 280, 1335, $p['unz'], 22);
		$pdf->textline($this->get('voies.3.val'), 540, 1335, $p['unz'], 22);
		$pdf->textline($this->get('voies.1.val'), 820, 1335, $p['unz'], 22);
		$pdf->textline($this->get('voies.1.val'), 1085, 1335, $p['unz'], 22);
		// voies des domaines ligne 4
		$pdf->textline($this->get('voies.4.val'), 271, 1502, $p['unz'], 22);
		$pdf->textline($this->get('voies.4.val'), 539, 1502, $p['unz'], 22);
		$pdf->textline($this->get('voies.3.val'), 800, 1502, $p['unz'], 22);
		$pdf->textline($this->get('voies.3.val'), 1065, 1502, $p['unz'], 22);

		// Avantages et désavantages
		$av = array(); foreach($this->get('avantages') as $v) { $av[] = tr($v['name'], true, null, 'create_char').($v['val']>1 ? '    x'.$v['val'] : ''); }
		if (isset($av[0])) { $pdf->textline(substr($av[0], 0, 25), 430, 500, $p['caro'], 18); }
		if (isset($av[1])) { $pdf->textline(substr($av[1], 0, 25), 430, 540, $p['caro'], 18); }
		if (isset($av[2])) { $pdf->textline(substr($av[2], 0, 25), 430, 580, $p['caro'], 18); }
		if (isset($av[3])) { $pdf->textline(substr($av[3], 0, 25), 430, 620, $p['caro'], 18); }
		$dv = array(); foreach($this->get('desavantages') as $v) { $dv[] = tr($v['name'], true, null, 'create_char').($v['val']>1 ? '    x'.$v['val'] : ''); }
		if (isset($dv[0])) { $pdf->textline(substr($dv[0], 0, 25), 430, 685, $p['caro'], 18); }
		if (isset($dv[1])) { $pdf->textline(substr($dv[1], 0, 25), 430, 725, $p['caro'], 18); }
		if (isset($dv[2])) { $pdf->textline(substr($dv[2], 0, 25), 430, 765, $p['caro'], 18); }
		if (isset($dv[3])) { $pdf->textline(substr($dv[3], 0, 25), 430, 805, $p['caro'], 18); }

		// Santé
		$health_array = $this->get_health_array();
		$health = array();
		foreach ($health_array as $k => $v) {
			$health[$k] = '';
			for ($i = 1; $i <= $v; $i++) { $health[$k] .= 'O '; }
		}
		$pdf->textline($health['Bon'], 920, 527, $p['times'], 24);
		$pdf->textline($health['Moyen'], 920, 571, $p['times'], 24);
		$pdf->textline($health['Grave'], 920, 615, $p['times'], 24);
		$pdf->textline($health['Critique'], 920, 658, $p['times'], 24);
		$pdf->textline($health['Agonie'], 920, 700, $p['times'], 24);

		$pdf->textline($this->get('vigueur'), 1090, 755, $p['caro'], 22);
		$pdf->textline($this->get('survie'), 1090, 798, $p['caro'], 22);

		// Domaines
		$x_arr = array(0, 91, 91, 91, 350, 350, 350, 350, 614, 614, 614, 614, 874, 874, 874, 874, 91);
		$y_arr = array(0, 988, 1165, 1331, 988, 1165, 1333, 1499, 988, 1165, 1331, 1499, 988, 1165, 1331, 1499, 1499);
		$j = 0;
		if ($printer_friendly === true) {
			$pdf->SetTextColor(0x14, 0x14, 0x14);
		} else {
			$pdf->SetTextColor(0x22, 0x11, 0x4);
		}
		foreach($this->get('domaines') as $val) {
			$score = $val['val'];
			$j++;
			if ($score >= 0) {
				for ($i = 1; $i <= $score; $i++) {
					$pdf->textline('●', $x_arr[$j]+($i-1)*23.75-7, $y_arr[$j]+4, $p['arial'], 29);
				}
			}
			if ($val['bonus']) {
				$pdf->textline('+'.$val['bonus'], $x_arr[$j]+52, $y_arr[$j]+23, $p['unz'], 16);
			}
			if ($val['malus']) {
				$pdf->textline('-'.$val['malus'], $x_arr[$j]+143, $y_arr[$j]+23, $p['unz'], 16);
			}
			$l = 0;
			foreach($val['disciplines'] as $v) {
				$pdf->textline(tr($v['name'], true, null, 'create_char'), $x_arr[$j]+45, $y_arr[$j]+45+$l*22, $p['times'], 13);
				$pdf->textline($v['val'], $x_arr[$j]+222, $y_arr[$j]+45+$l*22, $p['caro'], 17);
				$l++;
			}
		}
		$pdf->SetTextColor(0, 0, 0);

		//-----------------------------------*/

		/*---------------------------------*/
		/*----------DEUXIÈME FICHE---------*/
		/*---------------------------------*/

		//*-----------------------------------
		$pdf->AddPage('', array($general_width, $general_height));
		$pdf->Image(WEBROOT.DS.'files'.DS.'sheets'.DS.'esteren2'.($printer_friendly === true ? '-pf' : '').'_'.P_LANG.'.jpg', 0, 0, $general_width, $general_height);


		$i = 0;
		foreach($this->get('inventaire.armes') as $v) {
			if ($i > 4) { break; }
			$pdf->textline(tr($v['name'], true, null, 'create_char'), 123, 151+$i*43, $p['times'], 14);//Affichage de l'arme
			$pdf->textline($v['degats'], 370, 157+$i*43-2, $p['caro'], 20);
			$i++;
		}
		unset($i, $v);

		$pdf->textline($this->get('potentiel'), 335, 366, $p['caro'], 32);


		//Attitudes de combat
		$tir = $this->get_attack_tir();
		$cac = $this->get_attack_cac();
		$pot = $this->get('potentiel');
		$rap = $this->get('rapidite.base') + $this->get('rapidite.amelioration');
		$def = $this->get('defense.base') + $this->get('defense.amelioration');
		$attitudes = array(
				array('tir' => $tir,		'cac' => $cac,		'def' => $def,		'rap' => $rap		),//Attitudes standards
				array('tir' => $tir+$pot,	'cac' => $cac+$pot,	'def' => $def-$pot,	'rap' => $rap		),//Attitudes offensives
				array('tir' => $tir-$pot,	'cac' => $cac-$pot,	'def' => $def+$pot,	'rap' => $rap		),//Attitudes défensives
				array('tir' => $tir,		'cac' => $cac,		'def' => $def-$pot,	'rap' => $rap+$pot	),//Attitudes rapide
				array('tir' => 0,			'cac' => 0,			'def' => $def+$pot,	'rap' => $rap		),//Attitudes de mouvement
		);
		$pdf->textline('CàC/Tir', 475, 115, $p['times'], 13);
		foreach($attitudes as $k => $v) {
			$pdf->textline($v['cac'].'/'.$v['tir'], 489, 161+$k*54, $p['carbold'], 15);
			$pdf->textline($v['def'], 572, 161+$k*54, $p['carbold'], 20);
			$pdf->textline($v['rap'], 650, 161+$k*54, $p['carbold'], 20);
		}
		unset($i, $v, $k, $tir, $cac, $pot, $rap, $def, $attitudes);

		//Défense améliorée
		if ($printer_friendly === true) {
			$pdf->SetTextColor(0x14, 0x14, 0x14);
		} else {
			$pdf->SetTextColor(0x22, 0x11, 0x4);
		}
		if ($this->get('defense.amelioration')) {
			for ($i = 1; $i <= $this->get('defense.amelioration'); $i++) {
				if ($i > 5) { $off = 12; } else { $off = 0; }
				$pdf->textline('●', 767+($i-1)*27.6+$off, 136, $p['arial'], 30);
			}
		}
		unset($i, $off);

		//Rapidité améliorée
		for ($i = 1; $i <= $this->get('rapidite.amelioration'); $i++) {
			$pdf->textline('●', 767+($i-1)*27.6, 219, $p['arial'], 30);
		}
		unset($i);
		$pdf->SetTextColor(0, 0, 0);

		if ($this->get('inventaire.armures')) {
			$arr = $this->get('inventaire.armures');
			$i = 0;
			foreach ($arr as $k => $v) {
				if ($i > 3) { break; }
				$v = str_replace("\r", '', $v);
				$v = str_replace("\n", '', $v);
				$str = tr($v['name'], true, null, 'create_char').' ('.$v['protection'].')';
				$pdf->textline($str, 750, 277+($i*31), $p['times'], 14);
				$i++;
			}
		}
		unset($i, $arr, $v, $k);

		if ($this->get('arts_combat')) {
			$i = 0;
			foreach ($this->get('arts_combat') as $v) {
				$str = tr($v['name'], true, null, 'create_char');
				$pdf->textline($str, 448, 1026+($i*44), $p['carbold'], 20);
				$i++;
			}
		}
		unset($i, $v);

		$arr = (array) $this->get('inventaire.artefacts');
		foreach ($arr as $k => $v) {
			$v = str_replace("\r", '', $v);
			$v = str_replace("\n", '', $v);
			if ($v) { $arr[$k] = $v; } else { unset($arr[$k]); }
		}
		$str = implode(', ', $arr);
		$pdf->multiple_lines($str, 92, 1028, $p['times'], 14, 300, 3, 43);
		unset($k, $v, $str, $arr);


		$arr = (array) $this->get('inventaire.objets_precieux');
		foreach ($arr as $k => $v) {
			$v = str_replace("\r", '', $v);
			$v = str_replace("\n", '', $v);
			if ($v) { $arr[$k] = $v; } else { unset($arr[$k]); }
		}
		$str = implode(', ', $arr);
		$pdf->multiple_lines($str, 800, 790, $p['times'], 14, 300, 3, 43);
		unset($k, $v, $str, $arr);

		//Possessions et équipements
		if ($this->get('inventaire.possessions')) {
			$arr1 = array_slice($this->get('inventaire.possessions'), 0, 10);
			$arr2 = array_slice($this->get('inventaire.possessions'), 10, 10);
			foreach ($arr1 as $i => $v) { $pdf->textbox($v, 85, 535+$i*42.8, $p['times'], 14, 280); }
			foreach ($arr2 as $i => $v) { $pdf->textbox($v, 445, 535+$i*42.8, $p['times'], 14, 280); }
		}
		unset($i, $v, $arr1, $arr2);

		if ($this->get('ogham')) {
			$arr = $this->get('ogham');
			$i = 0;
			foreach ($arr as $v) {
				if ($i > 5) { break; }
				$pdf->textline($v, 147, 1377+($i*43), $p['carbold'], 20);
				$i++;
			}
		}

		if ($this->get('miracles')) {
			$min = (array) $this->get('miracles.min');
			$maj = (array) $this->get('miracles.maj');
			$min = implode(', ', $min);
			$maj = implode(', ', $maj);
			$pdf->multiple_lines($min, 457, 1341, $p['carbold'], 18, 270, 3, 43);
			$pdf->multiple_lines($maj, 457, 1512, $p['carbold'], 18, 270, 3, 43);
		}

		$pdf->textline($this->get('rindath.val').' / '.$this->get('rindath.max'), 195, 1272, $p['carbold'], 32);
		$pdf->textline($this->get('exaltation.val').' / '.$this->get('exaltation.val'), 540, 1272, $p['carbold'], 32);

		//Argent
		$argent = $this->get_daols($this->get('inventaire.argent'));
		$pdf->textline($argent['braise'], 830, 540, $p['carbold'], 28);
		$pdf->textline($argent['azur'], 830, 609, $p['carbold'], 28);
		$pdf->textline($argent['givre'], 830, 676, $p['carbold'], 28);
		//-----------------------------------*/

		/*---------------------------------*/
		/*---------TROISIÈME FICHE---------*/
		/*---------------------------------*/
		$pdf->AddPage('', array($general_width, $general_height));
		$pdf->Image(WEBROOT.DS.'files'.DS.'sheets'.DS.'esteren3'.($printer_friendly === true ? '-pf' : '').'_'.P_LANG.'.jpg', 0, 0, $general_width, $general_height);

		$resist_mentale = $this->get('resistance_mentale.val') + $this->get('resistance_mentale.exp');
		$pdf->textline($resist_mentale, 323, 528, $p['carbold'], 25);
		$pdf->textline($this->get('voies.1.val'), 1050, 897, $p['carbold'], 28);
		$pdf->textline($this->get('voies.2.val'), 1050, 969, $p['carbold'], 28);
		$pdf->textline($this->get('voies.3.val'), 1050, 1041, $p['carbold'], 28);
		$pdf->textline($this->get('voies.4.val'), 1050, 1110, $p['carbold'], 28);
		$pdf->textline($this->get('voies.5.val'), 1050, 1180, $p['carbold'], 28);

		$story = tr($this->get('details_personnage.histoire'), true, null, 'characters.'.$this->id);
		$story = substr($story, 0, 1200);
		$pdf->multiple_lines($story, 90, 173, $p['times'], 14, 1015, 6, 43);

		$str = tr($this->get('region_naissance.royaume'), true, null, 'create_char').' - '.tr($this->get('region_naissance.name'), true, null, 'create_char').' - '.tr($this->get('residence_geographique'), true);
		$pdf->textline($str, 557, 86, $p['caro'], 14);
		unset($str);

		$pdf->textline(tr($this->get('classe_sociale'), true, null, 'create_char'), 557, 114, $p['caro'], 14);

		if ($this->get('revers')) {
			$rev = array();
			foreach($this->get('revers') as $v) { $rev[] = tr($v['name'], true, null, 'create_char'); }
			$rev = implode(' - ', $rev);
			$pdf->textline($rev, 557, 142, $p['caro'], 14);
		}

		//Points de traumatisme
		$trauma = $this->get('traumatismes.permanents') + $this->get('traumatismes.curables');
		$off = 0;
		for ($i = 1; $i <= $trauma; $i++) {
			if ($i <= $this->get('traumatismes.permanents')) {
				$pdf->SetTextColor(0x22, 0x11, 0x4);
			} else {
				$pdf->SetTextColor(0x88, 0x6F, 0x4B);
			}
			if (($i - 1) % 5 == 0) { $off += 12; }
			$pdf->textline('●', 219+($i-1)*27.75+$off, 595, $p['arial'], 32);
		}
		unset($i, $trauma, $off);
		$pdf->SetTextColor(0, 0, 0);

		//Points d'endurcissement
		if ($this->get('endurcissement')) {
			$endurcissement = (int) $this->get('endurcissement');
			$off = 0;
			if ($printer_friendly === true) {
				$pdf->SetTextColor(0x14, 0x14, 0x14);
			} else {
				$pdf->SetTextColor(0x22, 0x11, 0x4);
			}
			for ($i = 1; $i <= $endurcissement; $i++) {
				if (($i-1) % 5 == 0) { $off += 12; }
				$pdf->textline('●', 219+($i-1)*27.75+$off, 631, $p['arial'], 32);
			}
		}
		unset($i, $endurcissement, $off);


		//Orientation
		$pdf->textline($this->get('orientation.conscience'), 271, 878, $p['carbold'], 21);
		$pdf->textline($this->get('orientation.instinct'), 420, 878, $p['carbold'], 21);
		$pdf->textline(tr($this->get('orientation.name'), true, null, 'create_char'), 645, 877, $p['carbold'], 18);

		//Désordre mental
		$pdf->textline($this->get('desordre_mental.name'), 195, 674, $p['carbold'], 21);

		//Qualité et défaut
		$pdf->textline(tr('Qualité', true, null, 'create_char').' : '.tr($this->get('traits_caractere.qualite.name'), true, null, 'create_char'), 270, 940, $p['carbold'], 21);
		$pdf->textline(tr('Défaut', true, null, 'create_char').' : '.tr($this->get('traits_caractere.defaut.name'), true, null, 'create_char'), 270, 982, $p['carbold'], 21);

		//Expérience
		$pdf->textline(tr('Reste', true).' :  '.$this->get('experience.reste').'         Total :  '.$this->get('experience.total'), 679, 1325, $p['carbold'], 24);


		if ($this->get('details_personnage.faits')) {
			$str = preg_replace('#\n|\r#isU', '', tr($this->get('details_personnage.faits'), true, null, 'characters.'.$this->id));
			$taille_du_texte = 14;
			$police_du_texte = $p['times'];
			$desc = array(0 => '');
			$arr = explode(' ', $str, 200);
			$line = 0;
			foreach ( $arr as $word ){
				$teststring = $desc[$line].' '.$word;
				$testbox = imagettfbbox($taille_du_texte, 0, $police_du_texte['file'], $teststring);
				if ($line == 0) { $larg = 729;
				} elseif ($line == 1) { $larg = 929;
				} elseif ($line == 2) { $larg = 908;
				} elseif ($line == 3) { $larg = 898;
				} elseif ($line == 4) { $larg = 878;
				} elseif ($line > 4) { $larg = 856;
                } else { $larg = INF; }// Théoriquement impossible
				if ($testbox[2] > $larg) {
					if ($desc[$line] == "") { $desc[$line] .= $word;
					} else { $line++; $desc[$line] = $word; }
				} else { $desc[$line] .= ($desc[$line] == "" ? "" : " " ) . $word; }
			}

			foreach($desc as $i => $v) {
				$offset = 0;
				if ($i == 0) { $offset = 197;
				} elseif ($i == 2) { $offset = 18;
				} elseif ($i == 3) { $offset = 32;
				} elseif ($i == 4) { $offset = 52;
				} elseif ($i == 5) { $offset = 74; }
				if ($i < 5) {
					$pdf->textline($v, 176 + $offset, 1388+$i*43, $police_du_texte, $taille_du_texte);
				} elseif ($i == 5) {
					$pdf->textline($v.'(...)', 176 + $offset, 1388+$i*43, $police_du_texte, $taille_du_texte);
				} else {
					break;
				}
			}
		}

		return $pdf;
	}

    /**
     * Cette fonction se charge de générer les feuilles de personnage jpeg selon la feuille originale des Ombres d'Esteren
     *
     * @param array $pages Les pages à créer
     * @param bool $printer_friendly
     * @return boolean True si réussi, False sinon
     */
	private function _make_sheet_from_original($pages = null, $printer_friendly = false) {
		Translate::$domain = 'character_sheet';

		if ($pages === null) { $pages = array(1,2,3); }

		$char_name_dest = clean_word($this->get('details_personnage.name'));

		$ret_names = array(
			CHAR_EXPORT.DS.$this->id.DS.$char_name_dest.'_original1'.($printer_friendly === true ? '-print' : '').'_'.P_LANG.'.jpg',
			CHAR_EXPORT.DS.$this->id.DS.$char_name_dest.'_original2'.($printer_friendly === true ? '-print' : '').'_'.P_LANG.'.jpg',
			CHAR_EXPORT.DS.$this->id.DS.$char_name_dest.'_original3'.($printer_friendly === true ? '-print' : '').'_'.P_LANG.'.jpg',
		);

		$name = $this->id;
		$dest_folder = ROOT.DS.'webroot'.DS.'files'.DS.'characters_export'.DS.$name;

		if (!FileAndDir::dexists($dest_folder)) {
			FileAndDir::createPath($dest_folder);
		}

		$x = 1191;//Largeur
		$y = 1685;//Hauteur

		//Polices de caractère
		$unzialish			= P_FONTS.DS.'UnZialish.ttf';
		$arial				= P_FONTS.DS.'arial.ttf';
		$arial				= P_FONTS.DS.'arial.ttf';
		$carolingia			= P_FONTS.DS.'carolingia.ttf';
		$carolingia_bold	= P_FONTS.DS.'carolingia_old.ttf';
		$lettrine			= P_FONTS.DS.'LettrinEsteren-Regular.ttf';
		$ubuntu				= P_FONTS.DS.'Ubuntu-R_0.ttf';
		$times				= P_FONTS.DS.'times.ttf';

		$ret = array();

		/*--------------------------------*/
		/*---------PREMIÈRE FICHE---------*/
		/*--------------------------------*/
		if (in_array(1,$pages)) {
			$fiche = ROOT.DS.'webroot'.DS.'files'.DS.'sheets'.DS.'esteren1'.($printer_friendly === true?'-pf':'').'_'.P_LANG.'.jpg';
			$img = imagecreatefromjpeg($fiche);

			//Couleurs
			$grey = imagecolorallocate($img, 0x28, 0x28, 0x28);
			$brown = imagecolorallocate($img, 0x22, 0x11, 0x4);
			$darkgrey = imagecolorallocate($img, 0x14, 0x14, 0x14);

			$nimg = imagecreatetruecolor($x, $y);
			imagecopyresampled($nimg, $img, 0, 0, 0, 0, $x, $y, $x, $y);

			$taille_du_texte = 25;
			$police_du_texte = $lettrine;
			$char_name = '';
			$arr = str_split(ucfirst($this->get('details_personnage.name')));
			foreach ( $arr as $letter ){

				$teststring = $char_name.$letter;
				$testbox = imagettfbbox($taille_du_texte, 0, $police_du_texte, $teststring);
				if ( $testbox[2] <= 340 ){
					$char_name .= $letter;
				}
			}
			imagettftext($nimg, 25, 0, 213, 280, $grey, $lettrine, $char_name);
			$taille_du_texte = 21;
			$player_name = '';
			$arr = str_split(ucfirst($this->get('details_personnage.joueur')));
			foreach ( $arr as $letter ){

				$teststring = $player_name.$letter;
				$testbox = imagettfbbox($taille_du_texte, 0, $police_du_texte, $teststring);
				if ( $testbox[2] <= 235 ){
					$player_name .= $letter;
				}
			}
			imagettftext($nimg, 21, 0, 880, 280, $grey, $lettrine, $player_name);
			unset($taille_du_texte, $police_du_texte, $char_name, $arr, $player_name);

			imagettftext($nimg, 18, 0, 215, 322, $grey, $times, substr(tr($this->get('details_personnage.sexe'), true), 0, 1));
			imagettftext($nimg, 20, 0, 343, 322, $grey, $carolingia_bold, substr($this->get('age'), 0, 3));

			$taille_du_texte = 17;
			$police_du_texte = $carolingia;
			$desc = "";
			$arr = explode(' ', tr($this->get('details_personnage.description'), true, array(), 'characters.'.$this->id), 100);
			foreach ( $arr as $word ){

				$teststring = $desc.' '.$word;
				$testbox = imagettfbbox($taille_du_texte, 0, $police_du_texte, $teststring);
				if ( $testbox[2] > 824 ){
					$desc.=($desc==""?"":"\n").$word;
				} else {
					$desc.=($desc==""?"":' ').$word;
				}
			}
			$desc = explode("\n", $desc);
			foreach($desc as $i => $v) {
				if ($i == 0) {
					imagettftext($nimg, $taille_du_texte, 0, 295, 365, $grey, $police_du_texte, $v);
				} elseif ($i == 1) {
					imagettftext($nimg, $taille_du_texte, 0, 1115, 365, $grey, $police_du_texte, '(...)');
				} else {
					break;
				}
			}
			unset($i,$v,$desc,$arr,$word,$teststring,$testbox,$police_du_texte,$taille_du_texte);

			imagettftext($nimg, 18, 0, 530, 322, $grey, $lettrine, substr(tr($this->get('peuple'), true, null, 'create_char'), 0, 20));
			imagettftext($nimg, 18, 0, 895, 322, $grey, $lettrine, substr(tr($this->get('metier.name'), true, null, 'create_char'), 0, 25));

			// voies
			imagettftext($nimg, 28, 0, 325, 545, $grey, $carolingia_bold, $this->get('voies.1.val'));
			imagettftext($nimg, 28, 0, 325, 608, $grey, $carolingia_bold, $this->get('voies.3.val'));
			imagettftext($nimg, 28, 0, 325, 667, $grey, $carolingia_bold, $this->get('voies.2.val'));
			imagettftext($nimg, 28, 0, 325, 727, $grey, $carolingia_bold, $this->get('voies.4.val'));
			imagettftext($nimg, 28, 0, 325, 784, $grey, $carolingia_bold, $this->get('voies.5.val'));

			// voies des domaines ligne 1
			imagettftext($nimg, 22, 0, 290, 990, $grey, $unzialish, $this->get('voies.2.val'));
			imagettftext($nimg, 22, 0, 537, 990, $grey, $unzialish, $this->get('voies.4.val'));
			imagettftext($nimg, 22, 0, 800, 990, $grey, $unzialish, $this->get('voies.4.val'));
			imagettftext($nimg, 22, 0, 1069, 990, $grey, $unzialish, $this->get('voies.2.val'));
			// voies des domaines ligne 2
			imagettftext($nimg, 22, 0, 298, 1169, $grey, $unzialish, $this->get('voies.1.val'));
			imagettftext($nimg, 22, 0, 542, 1169, $grey, $unzialish, $this->get('voies.3.val'));
			imagettftext($nimg, 22, 0, 802, 1169, $grey, $unzialish, $this->get('voies.5.val'));
			imagettftext($nimg, 22, 0, 1060, 1169, $grey, $unzialish, $this->get('voies.4.val'));
			// voies des domaines ligne 3
			imagettftext($nimg, 22, 0, 280, 1335, $grey, $unzialish, $this->get('voies.3.val'));
			imagettftext($nimg, 22, 0, 540, 1335, $grey, $unzialish, $this->get('voies.3.val'));
			imagettftext($nimg, 22, 0, 820, 1335, $grey, $unzialish, $this->get('voies.1.val'));
			imagettftext($nimg, 22, 0, 1085, 1335, $grey, $unzialish, $this->get('voies.1.val'));
			// voies des domaines ligne 4
			imagettftext($nimg, 22, 0, 271, 1502, $grey, $unzialish, $this->get('voies.4.val'));
			imagettftext($nimg, 22, 0, 539, 1502, $grey, $unzialish, $this->get('voies.4.val'));
			imagettftext($nimg, 22, 0, 800, 1502, $grey, $unzialish, $this->get('voies.3.val'));
			imagettftext($nimg, 22, 0, 1065, 1502, $grey, $unzialish, $this->get('voies.3.val'));

			// Avantages et désavantages
			$av = array(); foreach(($this->get('avantages') ? $this->get('avantages') : array()) as $v) { $av[] = tr($v['name'], true, null, 'create_char').($v['val']>1 ? '    x'.$v['val'] : ''); }
			if (isset($av[0])) { imagettftext($nimg, 18, 0, 430, 500, $grey, $carolingia, substr($av[0], 0, 25)); }
			if (isset($av[1])) { imagettftext($nimg, 18, 0, 430, 540, $grey, $carolingia, substr($av[1], 0, 25)); }
			if (isset($av[2])) { imagettftext($nimg, 18, 0, 430, 580, $grey, $carolingia, substr($av[2], 0, 25)); }
			if (isset($av[3])) { imagettftext($nimg, 18, 0, 430, 620, $grey, $carolingia, substr($av[3], 0, 25)); }
			$dv = array(); foreach(($this->get('desavantages') ? $this->get('desavantages') : array()) as $v) { $dv[] = tr($v['name'], true, null, 'create_char').($v['val']>1 ? '    x'.$v['val'] : ''); }
			if (isset($dv[0])) { imagettftext($nimg, 18, 0, 430, 685, $grey, $carolingia, substr($dv[0], 0, 25)); }
			if (isset($dv[1])) { imagettftext($nimg, 18, 0, 430, 725, $grey, $carolingia, substr($dv[1], 0, 25)); }
			if (isset($dv[2])) { imagettftext($nimg, 18, 0, 430, 765, $grey, $carolingia, substr($dv[2], 0, 25)); }
			if (isset($dv[3])) { imagettftext($nimg, 18, 0, 430, 805, $grey, $carolingia, substr($dv[3], 0, 25)); }

			// Santé
			$health_array = $this->get_health_array();
			$health = array();
			foreach ($health_array as $k => $v) {
				$health[$k] = '';
				for ($i = 1; $i <= $v; $i++) { $health[$k] .= 'O '; }
			}
			imagettftext($nimg, 24, 0, 920, 527, $brown, $times, $health['Bon']);
			imagettftext($nimg, 24, 0, 920, 571, $brown, $times, $health['Moyen']);
			imagettftext($nimg, 24, 0, 920, 615, $brown, $times, $health['Grave']);
			imagettftext($nimg, 24, 0, 920, 658, $brown, $times, $health['Critique']);
			imagettftext($nimg, 24, 0, 920, 700, $brown, $times, $health['Agonie']);

			imagettftext($nimg, 22, 0, 1090, 755, $grey, $carolingia, $this->get('vigueur'));
			imagettftext($nimg, 22, 0, 1090, 798, $grey, $carolingia, $this->get('survie'));

			// Domaines
			$x_arr = array(0, 91, 91, 91, 350, 350, 350, 350, 614, 614, 614, 614, 874, 874, 874, 874, 91);
			$y_arr = array(0, 988, 1165, 1331, 988, 1165, 1333, 1499, 988, 1165, 1331, 1499, 988, 1165, 1331, 1499, 1499);
			$j = 0;
			foreach((array) $this->get('domaines') as $val) {
				if (is_array($val) && isset($val['name']) && isset($val['val']) && isset($val['bonus']) && isset($val['malus'])) {
					$score = $val['val'];
					$j++;
					if ($score >= 0) {
						for ($i = 1; $i <= $score; $i++) {
							imagettftext($nimg, 29, 0, $x_arr[$j]+($i-1)*23.75-7, $y_arr[$j]+4, ($printer_friendly === true ? $darkgrey : $brown), $arial, '●');
						}
					}
					if ($val['bonus']) {
						imagettftext($nimg, 16, 0, $x_arr[$j]+52, $y_arr[$j]+23, $grey, $unzialish, '+'.$val['bonus']);
					}
					if ($val['malus']) {
						imagettftext($nimg, 16, 0, $x_arr[$j]+143, $y_arr[$j]+23, $grey, $unzialish, '-'.$val['malus']);
					}
					$l = 0;
					foreach($val['disciplines'] as $v) {
						imagettftext($nimg, 13, 0, $x_arr[$j]+45, $y_arr[$j]+45+$l*22, $darkgrey, $times, tr($v['name'], true, null, 'create_char'));
						imagettftext($nimg, 17, 0, $x_arr[$j]+222, $y_arr[$j]+45+$l*22, $darkgrey, $carolingia, $v['val']);
						$l++;
					}
				}
			}
			$exp = imagejpeg($nimg, $ret_names[0], $printer_friendly === true?100:80);
			unset($fiche,$img,$nimg);

			if ($exp) {
				$ret[] = $ret_names[0];
			}
		}//Fin fiche 3

		/*--------------------------------*/
		/*---------DEUXIÈME FICHE---------*/
		/*--------------------------------*/
		if (in_array(2,$pages)) {

			$fiche = ROOT.DS.'webroot'.DS.'files'.DS.'sheets'.DS.'esteren2'.($printer_friendly === true?'-pf':'').'_'.P_LANG.'.jpg';
			$img = imagecreatefromjpeg($fiche);

			//Couleurs
			$grey = imagecolorallocate($img, 0x28, 0x28, 0x28);
			$brown = imagecolorallocate($img, 0x22, 0x11, 0x4);
			$darkgrey = imagecolorallocate($img, 0x14, 0x14, 0x14);

			$nimg = imagecreatetruecolor($x, $y);

			imagecopyresampled($nimg, $img, 0, 0, 0, 0, $x, $y, $x, $y);

			$i = 0;
			foreach((array) $this->get('inventaire.armes') as $v) {
				if (!is_array($v) && isset($v['name']) && isset($v['degats'])) {
					if ($i > 4) { break; }
					imagettftext($nimg, 14, 0, 123, 151+$i*43, $grey, $times, tr($v['name'], true, null, 'create_char'));//Affichage de l'arme
					imagettftext($nimg, 20, 0, 370, 157+$i*43-2, $grey, $carolingia, $v['degats']);
					$i++;
				}
			}

			imagettftext($nimg, 32, 0, 335, 366, $grey, $carolingia, $this->get('potentiel'));

			//Attitudes de combat
			$tir = $this->get_attack_tir();
			$cac = $this->get_attack_cac();
			$pot = $this->get('potentiel');
			$rap = $this->get('rapidite.base') + $this->get('rapidite.amelioration');
			$def = $this->get('defense.base') + $this->get('defense.amelioration');
			$attitudes = array(
				array('tir' => $tir,		'cac' => $cac,		'def' => $def,		'rap' => $rap		),//Attitudes standards
				array('tir' => $tir+$pot,	'cac' => $cac+$pot,	'def' => $def-$pot,	'rap' => $rap		),//Attitudes offensives
				array('tir' => $tir-$pot,	'cac' => $cac-$pot,	'def' => $def+$pot,	'rap' => $rap		),//Attitudes défensives
				array('tir' => $tir,		'cac' => $cac,		'def' => $def-$pot,	'rap' => $rap+$pot	),//Attitudes rapide
				array('tir' => 0,			'cac' => 0,			'def' => $def+$pot,	'rap' => $rap		),//Attitudes de mouvement
			);
			imagettftext($nimg, 13, 0, 475, 115, $grey, $times, 'CàC/Tir');
			foreach($attitudes as $k => $v) {
				imagettftext($nimg, 15, 0, 489, 161+$k*54, $grey, $carolingia_bold, $v['cac'].'/'.$v['tir']);
				imagettftext($nimg, 20, 0, 572, 161+$k*54, $grey, $carolingia_bold, $v['def']);
				imagettftext($nimg, 20, 0, 650, 161+$k*54, $grey, $carolingia_bold, $v['rap']);
			}

			//Défense améliorée
			if ($this->get('defense.amelioration')) {
				for ($i = 1; $i <= $this->get('defense.amelioration'); $i++) {
					if ($i > 5) { $off = 12; } else { $off = 0; }
					imagettftext($nimg, 29, 0, 767+($i-1)*27.8+$off, 135, ($printer_friendly === true ? $darkgrey : $brown), $arial, '●');
				}
			}

			//Rapidité améliorée
			for ($i = 1; $i <= $this->get('rapidite.amelioration'); $i++) {
				imagettftext($nimg, 29, 0, 767+($i-1)*27.8, 218, ($printer_friendly === true ? $darkgrey : $brown), $arial, '●');
			}

			if ($this->get('inventaire.armures')) {
				$arr = $this->get('inventaire.armures');
				$i = 0;
				foreach ($arr as $v) {
					if ($i > 3) { break; }
					$v = str_replace("\r", '', $v);
					$v = str_replace("\n", '', $v);
					$str = tr($v['name'], true, null, 'create_char').' ('.$v['protection'].')';
					imagettftext($nimg, 14, 0, 750, 277+($i*31), $grey, $times, $str);
					$i++;
				}
			}

			if ($this->get('arts_combat')) {
				$i = 0;
				foreach ($this->get('arts_combat') as $v) {
					$str = tr($v['name'], true, null, 'create_char');
					imagettftext($nimg, 20, 0, 448, 1026+($i*44), ($printer_friendly === true ? $darkgrey : $brown), $carolingia_bold, $str);
					$i++;
				}
			}

			if ($this->get('inventaire.artefacts')) {
				$arr = $this->get('inventaire.artefacts');
				foreach ($arr as $k => $v) {
					$v = str_replace("\r", '', $v);
					$v = str_replace("\n", '', $v);
					if ($v) { $arr[$k] = $v; } else { unset($arr[$k]); }
				}
				$taille_du_texte = 14;
				$police_du_texte = $times;
				$story = "";
				$str = implode(' – ', $arr);
				$arr = explode(' ', $str, 250);
				foreach ( $arr as $word ){

					$teststring = $story.' '.$word;
					$testbox = imagettfbbox($taille_du_texte, 0, $police_du_texte, $teststring);
					if ( $testbox[2] > 300 ){
						$story.=($story==""?"":"\n").$word;
					} else {
						$story.=($story==""?"":' ').$word;
					}
				}
				$story = explode("\n", $story);

				foreach($story as $i => $v) {
					if ($i <= 2) {
						imagettftext($nimg, $taille_du_texte, 0, 92, 1028+$i*43, $grey, $police_du_texte, $v);
					} elseif ($i == 3) {
						imagettftext($nimg, $taille_du_texte, 0, 390, 1115, $grey, $police_du_texte, '(...)');
					} else {
						break;
					}
				}
			}

			if ($this->get('inventaire.objets_precieux')) {
				$arr = (array)$this->get('inventaire.objets_precieux');
				foreach ($arr as $k => $v) {
					$v = str_replace("\r", '', $v);
					$v = str_replace("\n", '', $v);
					if ($v) { $arr[$k] = $v; } else { unset($arr[$k]); }
				}
				$taille_du_texte = 14;
				$police_du_texte = $times;
				$story = "";
				$str = implode(' – ', $arr);
				$arr = explode(' ', $str, 250);
				foreach ( $arr as $word ){

					$teststring = $story.' '.$word;
					$testbox = imagettfbbox($taille_du_texte, 0, $police_du_texte, $teststring);
					if ( $testbox[2] > 310 ){
						$story.=($story==""?"":"\n").$word;
					} else {
						$story.=($story==""?"":' ').$word;
					}
				}
				$story = explode("\n", $story);

				foreach($story as $i => $v) {
					if ($i <= 2) {
						imagettftext($nimg, $taille_du_texte, 0, 800, 790+$i*43, $grey, $police_du_texte, $v);
					} elseif ($i == 3) {
						imagettftext($nimg, $taille_du_texte, 0, 1105, 876, $grey, $police_du_texte, '(...)');
					} else {
						break;
					}
				}
			}

			//Possessions et équipements
			if ($this->get('inventaire.possessions')) {
				$i = 0;
				$x_offset = 85;
				$word_offset = 0;
				foreach($this->get('inventaire.possessions') as $v) {
					if ($i == 10) {
						if ($word_offset > 0) { break; }
						$x_offset = 445; $word_offset = 20; $i = 0;
					}

					$taille_du_texte = 14;
					$police_du_texte = $times;
					$equip_word = '';
					$arr = str_split(ucfirst($v));
					foreach ( $arr as $letter ){

						$teststring = $equip_word.$letter;
						$testbox = imagettfbbox($taille_du_texte, 0, $police_du_texte, $teststring);
						if ( $testbox[2] <= 310 - $word_offset){
							$equip_word .= $letter;
						} else {
							$equip_word .= '(...)';
							break;
						}
					}
					imagettftext($nimg, 14, 0, $x_offset, 535+$i*42.8, $grey, $times, $equip_word);
					$i++;
				}
			}

			if ($this->get('ogham')) {
				$arr = $this->get('ogham');
				$i = 0;
				foreach ($arr as $v) {
					if ($i > 5) { break; }
					imagettftext($nimg, 20, 0, 147, 1377+$i*43, $grey, $carolingia_bold, $v);
					$i++;
				}
			}

			if ($this->get('miracles')) {
				$miracles = $this->get('miracles');
				foreach ($miracles as $k => $v) {
					$taille_du_texte = 18;
					$police_du_texte = $carolingia_bold;
					$story = "";
					$offset = 0;
					if ($k === 'min') { $offset = 171; }
					$str = implode(' – ', $v);
					$arr = explode(' ', $str, 250);
					foreach ( $arr as $word ){

						$teststring = $story.' '.$word;
						$testbox = imagettfbbox($taille_du_texte, 0, $police_du_texte, $teststring);
						if ( $testbox[2] > 290 ){
							$story.=($story==""?"":"\n").$word;
						} else {
							$story.=($story==""?"":' ').$word;
						}
					}
					$story = explode("\n", $story);
					foreach($story as $i => $vv) {
						if ($i <= 2) {
							imagettftext($nimg, $taille_du_texte, 0, 457, 1341+$i*43+$offset, $grey, $police_du_texte, $vv);
						} elseif ($i == 3) {
							imagettftext($nimg, $taille_du_texte, 0, 750, 1426+$offset, $grey, $police_du_texte, '(...)');
						} else {
							break;
						}
					}
				}
			}

			imagettftext($nimg, 32, 0, 195, 1272, $grey, $carolingia_bold, $this->get('rindath.val').' / '.$this->get('rindath.max'));
			imagettftext($nimg, 32, 0, 540, 1272, $grey, $carolingia_bold, $this->get('exaltation.val').' / '.$this->get('exaltation.val'));

			//Argent
			$argent = $this->get_daols($this->get('inventaire.argent'));
			imagettftext($nimg, 28, 0, 830, 540, $grey, $carolingia_bold, $argent['braise']);
			imagettftext($nimg, 28, 0, 830, 609, $grey, $carolingia_bold, $argent['azur']);
			imagettftext($nimg, 28, 0, 830, 676, $grey, $carolingia_bold, $argent['givre']);

			$exp = imagejpeg($nimg, $ret_names[1], $printer_friendly === true?100:80);
			unset($fiche,$img,$nimg);

			if ($exp) {
				$ret[] = $ret_names[1];
			}
		}//Fin fiche 2

		/*---------------------------------*/
		/*---------TROISIÈME FICHE---------*/
		/*---------------------------------*/
		if (in_array(3,$pages)) {
			$fiche = ROOT.DS.'webroot'.DS.'files'.DS.'sheets'.DS.'esteren3'.($printer_friendly === true?'-pf':'').'_'.P_LANG.'.jpg';
			$img = imagecreatefromjpeg($fiche);

			//Couleurs
			$black = imagecolorallocate($img, 0, 0, 0);
			$grey = imagecolorallocate($img, 0x28, 0x28, 0x28);
			$darkgrey = imagecolorallocate($img, 0x14, 0x14, 0x14);
			$lightgrey = imagecolorallocate($img, 0x88, 0x88, 0x88);
			$brown = imagecolorallocate($img, 0x22, 0x11, 0x4);

			$nimg = imagecreatetruecolor($x, $y);
			imagecopyresampled($nimg, $img, 0, 0, 0, 0, $x, $y, $x, $y);

			$resist_mentale = $this->get('resistance_mentale.val') + $this->get('resistance_mentale.exp');
			imagettftext($nimg, 25, 0, 323, 528, $grey, $carolingia_bold, $resist_mentale);
			imagettftext($nimg, 28, 0, 1050, 897,  $grey, $carolingia_bold, $this->get('voies.1.val'));
			imagettftext($nimg, 28, 0, 1050, 969,  $grey, $carolingia_bold, $this->get('voies.2.val'));
			imagettftext($nimg, 28, 0, 1050, 1041, $grey, $carolingia_bold, $this->get('voies.3.val'));
			imagettftext($nimg, 28, 0, 1050, 1110, $grey, $carolingia_bold, $this->get('voies.4.val'));
			imagettftext($nimg, 28, 0, 1050, 1180, $grey, $carolingia_bold, $this->get('voies.5.val'));

			//Histoire
			if ($this->get('details_personnage.histoire')) {
				$taille_du_texte = 14;
				$police_du_texte = $times;
				$story = "";
				$arr = explode(' ', tr($this->get('details_personnage.histoire'), true, array(), 'characters.'.$this->id), 250);
				foreach ( $arr as $word ){

					$teststring = $story.' '.$word;
					$testbox = imagettfbbox($taille_du_texte, 0, $police_du_texte, $teststring);
					if ( $testbox[2] > 1015 ){
						$story.=($story==""?"":"\n").$word;
					} else {
						$story.=($story==""?"":' ').$word;
					}
				}
				$story = explode("\n", $story);

				foreach($story as $i => $v) {
					if ($i <= 5) {
						imagettftext($nimg, $taille_du_texte, 0, 90, 173+$i*43, $grey, $police_du_texte, $v);
					} elseif ($i == 6) {
						imagettftext($nimg, $taille_du_texte, 0, 1100, 173+($i-1)*43, $grey, $police_du_texte, '(...)');
					} else {
						break;
					}
				}
			}

			imagettftext($nimg, 14, 0, 557, 86, $grey, $carolingia, tr($this->get('region_naissance.royaume'), true, null, 'create_char').' - '.tr($this->get('region_naissance.name'), true, null, 'create_char').' - '.tr($this->get('residence_geographique'), true));

			imagettftext($nimg, 14, 0, 557, 114, $grey, $carolingia, tr($this->get('classe_sociale'), true, null, 'create_char'));

			$rev = array();
			if ($this->get('revers')) {
				foreach($this->get('revers') as $v) { $rev[] = tr($v['name'], true, null, 'create_char'); }
				$rev = implode(' - ', $rev);
				imagettftext($nimg, 14, 0, 557, 142, $grey, $carolingia, $rev);
			}
			//Points de traumatisme
			$trauma = $this->get('traumatismes.permanents') + $this->get('traumatismes.curables');
			$off = 0;
			for ($i = 1; $i <= $trauma; $i++) {
				if ($i <= $this->get('traumatismes.permanents')) {
					$color = $black;
				} else {
					$color = $lightgrey;
				}
				if (($i - 1) % 5 == 0) { $off += 12; }
				imagettftext($nimg, 32, 0, 219+($i-1)*27.75+$off, 595, $color, $arial, '●');
			}

			//Points d'endurcissement
			if ($this->get('endurcissement')) {
				$endurcissement = $this->get('endurcissement');
				$off = 0;
				for ($i = 1; $i <= $endurcissement; $i++) {
					if (($i-1) % 5 == 0) { $off += 12; }
					imagettftext($nimg, 32, 0, 219+($i-1)*27.75+$off, 631, ($printer_friendly === true ? $darkgrey : $brown), $arial, '●');
				}
			}

			//Orientation
			imagettftext($nimg, 21, 0, 271, 878, $grey, $carolingia_bold, $this->get('orientation.conscience'));
			imagettftext($nimg, 21, 0, 420, 878, $grey, $carolingia_bold, $this->get('orientation.instinct'));
			imagettftext($nimg, 18, 0, 645, 877, $grey, $carolingia_bold, tr($this->get('orientation.name'), true, null, 'create_char'));

			//Désordre mental
			imagettftext($nimg, 21, 0, 195, 674, $grey, $carolingia_bold, $this->get('desordre_mental.name'));

			//Qualité et défaut
			imagettftext($nimg, 21, 0, 270, 940, $grey, $carolingia_bold, tr('Qualité', true, null, 'create_char').' : '.tr($this->get('traits_caractere.qualite.name'), true, null, 'create_char'));
			imagettftext($nimg, 21, 0, 270, 982, $grey, $carolingia_bold, tr('Défaut', true, null, 'create_char').' : '.tr($this->get('traits_caractere.defaut.name'), true, null, 'create_char'));

			//Expérience
			imagettftext($nimg, 24, 0, 679, 1325, ($printer_friendly === true ? $grey : $brown), $carolingia_bold,
			tr('Reste', true).' :  '.$this->get('experience.reste').'         Total :  '.$this->get('experience.total'));

			//Faits marquants
			if ($this->get('details_personnage.faits')) {
				$this->set('details_personnage.faits', preg_replace('#\n|\r#isU', '', tr($this->get('details_personnage.faits'), true, null, 'characters.'.$this->id)));
				$taille_du_texte = 14;
				$police_du_texte = $times;
				$desc = array(0 => '');
				$arr = explode(' ', $this->get('details_personnage.faits'), 200);
				$line = 0;
				foreach ( $arr as $word ){
					$teststring = $desc[$line].' '.$word;
					$testbox = imagettfbbox($taille_du_texte, 0, $police_du_texte, $teststring);
					if ($line == 0) {
						$larg = 729;
					} elseif ($line == 1) {
						$larg = 929;
					} elseif ($line == 2) {
						$larg = 908;
					} elseif ($line == 3) {
						$larg = 898;
					} elseif ($line == 4) {
						$larg = 878;
					} elseif ($line > 4) {
						$larg = 856;
					} else {
                        $larg = INF;// Théoriquement impossible
                    }
					if ($testbox[2] > $larg) {
						if ($desc[$line] == "") {
							$desc[$line] .= $word;
						} else {
							$line++;
							$desc[$line] = $word;
						}
					} else {
						$desc[$line] .= ($desc[$line] == "" ? "" : " " ) . $word;
					}
				}

				foreach($desc as $i => $v) {
					if ($i <= 5) {
						$offset = 0;
						if ($i == 0) {
							$offset = 197;
						} elseif ($i == 2) {
							$offset = 18;
						} elseif ($i == 3) {
							$offset = 32;
						} elseif ($i == 4) {
							$offset = 52;
						} elseif ($i == 5) {
							$offset = 74;
						}
						imagettftext($nimg, $taille_du_texte, 0, 176 + $offset, 1388+$i*43, $grey, $police_du_texte, $v);
					} elseif ($i == 6) {
						imagettftext($nimg, $taille_du_texte, 0, 1102, 1388+($i-1)*43, $grey, $police_du_texte, '(...)');
					} else {
						break;
					}
				}
			}

			$exp = imagejpeg($nimg, $ret_names[2], $printer_friendly === true?100:80);;
			unset($fiche,$img,$nimg);

			if ($exp) {
				$ret[] = $ret_names[2];
			}
		}//Fin fiche 3

		Translate::$domain = null;
		return $ret;
	}
}
