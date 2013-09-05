<?php

class PagesController extends Controller {

	function index_action() {}

	function _404_action() {}

	function menu_action() {
		$cnt = file_get_contents(ROOT.DS.'configs'.DS.'menu.json');
		if ($cnt) {
			$cnt = json_decode($cnt, true);
		} else {
			return tr('Erreur dans le chargement du fichier du menu', true);
		}
		$this->set('links', $cnt);
		$this->set('asked_uri', $this->request()->asked_uri());
	}

	function versions_action() {
		$versions_xml = file_get_contents(ROOT.DS.'versions.xml');

		$versions = new SimpleXMLElement($versions_xml);
		unset($versions_xml);

		$updates = array();
		$total_maj = 0;

		$stepsModel = new StepsModel();

		$t = $stepsModel->req('SELECT %gen_step,%gen_mod,%gen_anchor FROM [TABLE] ORDER BY %gen_step ASC');//On génère la liste des étapes
		$steps = array();
		foreach ($t as $v) {//On formate la liste des étapes
			$steps[$v['gen_mod']] = 'Générateur : Étape '.$v['gen_step'].' - '.$v['gen_anchor'];
		}

		foreach ($versions->version as $v) {
			$day	= preg_replace('#^([0-9]{4})([0-9]{2})([0-9]{2})$#isU', '$3', (string)$v['date']);
			$month	= preg_replace('#^([0-9]{4})([0-9]{2})([0-9]{2})$#isU', '$2', (string)$v['date']);
			$year	= preg_replace('#^([0-9]{4})([0-9]{2})([0-9]{2})$#isU', '$1', (string)$v['date']);
			$date = $day.'/'.$month.'/'.$year;
			$code = (string) $v['code'];
			$updates[$code] = array(
				'tasks' => array(),
				'date' => $date,
			);
			$i = 0;
			foreach ($v->task as $task) {
				$element = array(
					'name' => (string)$task->element['name'],
					'id' => (int)$task->element['id'],
					'module' => (string) $task->element['module'],
					'title' => (string)$task->element,
					'comments' => array()
				);
				foreach ($task->comment as $comment) {
					$element['comments'][] = (string) $comment;
					$i++;
					$total_maj++;
				}
				$updates[$code]['tasks'][(string)$task['type']][] = $element;
			}
			$updates[$code]['modifications'] = $i;
		}
		$this->set('updates', $updates);
		$this->set('total_maj', $total_maj);
		unset($v, $comment, $task, $day, $month, $year, $date, $code, $element, $versions);
	}
}