<?php
if (!empty($_POST)) {

	$stepname = '';
	$t = $db->req('SELECT %gen_step,%gen_mod,%gen_anchor FROM %%steps ORDER BY %gen_step ASC');//On génère la liste des étapes
	$steps = array();
	foreach ($t as $v) {//On formate la liste des étapes
		$steps[$v['gen_step']] = array(
			'step' => $v['gen_step'],
			'mod' => $v['gen_mod'],
			'title' => $v['gen_anchor'],
		);
		if (isset($_POST[$v['gen_mod']])) {
			$stepname = $v['gen_mod'];
		}
	}

	$_SESSION['etape'] = isset($_POST['etape']) ? $_POST['etape'] : (isset($_SESSION['etape']) ? $_SESSION['etape'] : '');//Génère l'étape envoyée en post
	$_SESSION['etape'] = (int) $_SESSION['etape'];

	foreach($steps as $etape => $val) {
		if ($_SESSION['etape'] < $etape && isset($_SESSION[$val['mod']])) {
			unset($_SESSION[$val['mod']]);
		}
	}

// 	$stepname = array_keys($_POST);
// 	$stepname = isset($stepname[0]) ? $stepname[0] : '';
	if (!$stepname) {
		tr('Une erreur est survenue lors de la modification de l\'étape');
		if (P_DEBUG === true) {
			pr($_POST);
		}
		return;
	}

// 	if (isset($_POST['etape']) && is_numeric($_POST['etape'])) {
// 		$stepname = $steps[$_POST['etape']]['mod'];
// 		$_POST[$stepname] = isset($_POST[$stepname]) ? $_POST[$stepname] : '';
// 	}

	// Si l'étape n'existe pas ou n'est pas un nombre, alors c'est que aj_genmaj.php a été contourné avec AJAX
	if (!is_numeric($_SESSION['etape'])) {
		foreach($_SESSION as $k => $v) {
			foreach($_PAGE['list'] as $id => $page) {
				if (strpos($page['page_getmod'], $k) !== false) {
					unset($_SESSION[$k]);
				}
			}
		}
		$_SESSION['etape'] = 1;
		exit('erreur');
	}

	// Création de la variable si elle existe dans POST ou dans SESSION
	$_SESSION[$stepname] = isset($_POST[$stepname]) ? $_POST[$stepname] : (isset($_SESSION[$stepname]) ? $_SESSION[$stepname] : '');

	if (is_numeric($_SESSION[$stepname])) {
		$_SESSION[$stepname] = (int) $_SESSION[$stepname];
	}

	if (isset($_POST['empty'])) {
		unset($_SESSION[$stepname]);
		$stepname = '';
		$_SESSION['etape']--;
	}

	if (strpos($stepname, 'peuple') !== false) {
		$val = array('Tarish', 'Osag', 'Continent', 'Tri-Kazel');
		if (!in_array($_SESSION[$stepname], $val)) {
			unset($_SESSION[$stepname]);
			$_SESSION['etape']--;
		}
	}
	if (strpos($stepname, 'metier') !== false) {
		// Suppression des domaines primaires/secondaires
		if (isset($_SESSION[$steps[14]['mod']])) {
			unset($_SESSION[$steps[14]['mod']]);
		}
		// Suppression des améliorations de domaines
		if (isset($_SESSION[$steps[13]['mod']])) {
			unset($_SESSION[$steps[13]['mod']]);
		}
		if (!is_numeric($_SESSION[$stepname]) && !is_string($_SESSION[$stepname])) {
			unset($_SESSION[$stepname]);
			$_SESSION['etape']--;
		}
	}
	if (strpos($stepname, 'naissance') !== false
	|| strpos($stepname, 'sante_mentale' !== false)) {
		$_SESSION[$stepname] = (int) $_SESSION[$stepname];
		if (!$_SESSION[$stepname]) {
			unset($_SESSION[$stepname]);
			$_SESSION['etape']--;
		}
	}
	if (strpos($stepname, 'age') !== false) {
		$_SESSION[$stepname] = (int) $_SESSION[$stepname];
		if (!is_numeric($_SESSION[$stepname]) || $_SESSION[$stepname] > 35 || $_SESSION[$stepname] < 16) {
			unset($_SESSION[$stepname]);
			$_SESSION['etape']--;
		}
	}
	if (strpos($stepname, 'geo') !== false) {
		$val = array('Rural', 'Urbain');
		if (!in_array($_SESSION[$stepname], $val)) {
			unset($_SESSION[$stepname]);
			$_SESSION['etape']--;
		}
	}

	if (strpos($stepname, 'classe') !== false) {
		$val = array('Artisan', 'Bourgeois', 'Paysan', 'Clerge', 'Noblesse');
        if (isset($_SESSION[$stepname]['classe'])) {
            if (
                !in_array($_SESSION[$stepname]['classe'], $val)
                || !is_numeric($_SESSION[$stepname]['dom1'])
                || !is_numeric($_SESSION[$stepname]['dom2'])
                ) {
                unset($_SESSION[$stepname]);
                $_SESSION['etape']--;
            }
        } else {
            unset($_SESSION[$stepname]);
        }
	}

	if (strpos($stepname, 'voies') !== false) {
		//Suppression des traits potentiels
		if (isset($_SESSION[$steps[9]['mod']])) {
			unset($_SESSION[$steps[9]['mod']]);
		}
		//Suppression de l'orientation de la personnalité
		if (isset($_SESSION[$steps[10]['mod']])) {
			unset($_SESSION[$steps[10]['mod']]);
		}
		if (!preg_match('#^[0-9]+(,[0-9]+)+$#isU', $_SESSION[$stepname])) {
			unset($_SESSION[$stepname]);
			$_SESSION['etape']--;
		} else {
			$_SESSION[$stepname] = explode(',', $_SESSION[$stepname]);
			for ($i = count($_SESSION[$stepname]) - 1; $i >= 0; $i--) {
				$_SESSION[$stepname][$i + 1] = (int) $_SESSION[$stepname][$i];
			}
			unset($_SESSION[$stepname][0]);
		}
	}

	if (strpos($stepname, 'traits') !== false) {
		if (!preg_match('#^[0-9]+(,[0-9]+)*$#isU', $_SESSION[$stepname])) {
			echo 'ok';
			unset($_SESSION[$stepname]);
			$_SESSION['etape']--;
		} else {
			$_SESSION[$stepname] = explode(',', $_SESSION[$stepname]);
			foreach ($_SESSION[$stepname] as $key => $val) {
				if (!$val && $key > 0) { unset($_SESSION[$stepname][$key]); }
			}
			if (!$_SESSION[$stepname]) { $_SESSION[$stepname] = array(0); }
		}
	}

	if (strpos($stepname, 'des_avtg') !== false) {
		if (!$_SESSION[$stepname]) {
			$_SESSION[$stepname] = array();
		}
		$_SESSION[$stepname]['avantages'] = isset($_SESSION[$stepname]['avantages']) ? $_SESSION[$stepname]['avantages'] : array();
		$_SESSION[$stepname]['desavantages'] = isset($_SESSION[$stepname]['desavantages']) ? $_SESSION[$stepname]['desavantages'] : array();
		foreach ($_SESSION[$stepname]['desavantages'] as $key => $val) {
			if (!$val) { unset($_SESSION[$stepname]['desavantages'][$key]); }
		}
		foreach ($_SESSION[$stepname]['avantages'] as $key => $val) {
			if (!$val) { unset($_SESSION[$stepname]['avantages'][$key]); }
		}
	}

	if (strpos($stepname, 'domaines_primsec') !== false
		|| strpos($stepname, 'domaines_amelio') !== false
		|| strpos($stepname, 'bonusdom') !== false
		|| strpos($stepname, 'revers') !== false
		|| strpos($stepname, 'disciplines') !== false
		|| strpos($stepname, 'arts_combat') !== false) {

		//Cas particuliers où la variable peut être vide
		if (strpos($stepname, 'disciplines') !== false
			|| strpos($stepname, 'arts_combat') !== false
			|| strpos($stepname, 'revers') !== false
			|| strpos($stepname, 'bonusdom') !== false) {
			if (is_array($_SESSION[$stepname])) {
				foreach($_SESSION[$stepname] as $key => $val) {
					if (!$val) { unset($_SESSION[$stepname][$key]); }
				}
			}
			if (empty($_SESSION[$stepname])) { $_SESSION[$stepname] = array(); }
		} else {
			if (strpos($stepname, 'domaines_primsec') !== false) {
				$_SESSION[$stepname]['ost'] = isset($_POST['ost']) ? $_POST['ost'] : 2;
				if (isset($_POST['lettre'])) { $_SESSION[$stepname]['lettre'] = (int) $_POST['lettre']; }
			}
			if (!is_array($_SESSION[$stepname])) {
				unset($_SESSION[$stepname]);
				$_SESSION['etape']--;
			} else {
				foreach($_SESSION[$stepname] as $key => $val) {
					if (!$val) { unset($_SESSION[$stepname][$key]); }
				}
			}
		}
	}
	if (strpos($stepname, 'xpdom') !== false) {
		unset($_SESSION[$stepname][0]);
		if (!preg_match('#^([0-9]+,)+[0-9]+$#isU', implode(',', $_SESSION[$stepname]))) {
			unset($_SESSION[$stepname]);
			$_SESSION['etape']--;
		} else {
			foreach ($_SESSION[$stepname] as $key => $val) {
				if (!$val) { unset($_SESSION[$stepname][$key]);
				}
			}
		}
	}

	if (strpos($stepname, 'orientation') !== false) {
		$val = array('Rationnelle', 'Instinctive');
		if (!in_array($_SESSION[$stepname], $val)) {
			unset($_SESSION[$stepname]);
			$_SESSION['etape']--;
		}
	}

	if (strpos($stepname, 'description_histoire') !== false) {
		if (!$_SESSION[$stepname]['sex'] || !$_SESSION[$stepname]['name']) {
			unset($_SESSION[$stepname]);
			$_SESSION['etape']--;
		}
	}

	if (strpos($stepname, 'equipements') !== false) {
		$_SESSION[$stepname]['arme'] = (array) array_map('intval', (array) (isset($_SESSION[$stepname]['arme']) ? $_SESSION[$stepname]['arme'] : array()));
		$_SESSION[$stepname]['armure'] = (array) array_map('intval', (array) (isset($_SESSION[$stepname]['armure']) ? $_SESSION[$stepname]['armure'] : array()));
	}

	if (isset($_POST['bonusdom'])) { $_SESSION['bonusdom'] = $_POST['bonusdom']; }

	$_SESSION['etape']++;

	/*
	pr(array(
		'stepname' => $stepname,
		'post' => $_POST,
		'session stepname' => Session::read($stepname),
		'etape' => Session::read('etape'),
	));
	//*/
}