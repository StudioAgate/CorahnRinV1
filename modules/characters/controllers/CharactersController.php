<?php
class CharactersController extends Controller {

	function create_action($step = '1') {
		$step = explode('_', $step, 2);
		$slug = @$step[1];
		$step = $step[0];
		$model = $this->load_model('Steps');
		$db_find = $model->req('SELECT * FROM [TABLE]');
		pr($db_find);
	}
}