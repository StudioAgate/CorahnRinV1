<?php

use App\EsterenChar;

$folder = ROOT.DS.'webroot'.DS.'files'.DS.'characters_export';

$char_id = isset($_PAGE['request'][0]) ? $_PAGE['request'][0] : 0;

$ymlDumper = new Symfony\Component\Yaml\Dumper(2);

/** @var EsterenChar $charObj */
$orderby = $sort = $char = $charObj = $characters = $modifications = $users = null;

if ($char_id) {
	$char = $db->row('SELECT %char_id,%char_name,%%characters.%char_content FROM %%characters WHERE %char_id = ?', $char_id);
    if (!$char) {
        redirect(array(), 'Aucun personnage trouvé', 'warning');
    }
    $charObj = new EsterenChar($char_id);
    $modifications = $db->req('SELECT %charmod_date, %charmod_content_before, %charmod_content_after, %charmod_page_module, %char_id, %user_id FROM %%charmod WHERE %char_id = :char_id ORDER BY %charmod_date DESC', array('char_id' => $char_id));
    $usersModifiersIds = array_reduce($modifications?:[], function($result, $charmod) {
        $result[$charmod['user_id']] = $charmod['user_id'];
        return $result;
    }, array());
    $users = $db->req('SELECT %user_id, %user_name FROM %%users WHERE %user_id IN (%%%in)', array_values($usersModifiersIds));
    $users = array_reduce($users?:[], function($result, $user) {
        $result[$user['user_id']] = $user['user_name'];
        return $result;
    }, array());
} else {
    $orderby = isset($_PAGE['request']['orderby']) ? $_PAGE['request']['orderby'] : 'name';
	$orderby = strtolower($orderby);
	$auth_fields = array('name'=>1,'jobname'=>1,'origin'=>1,'people'=>1,'id'=>1);
	if (!isset($auth_fields[$orderby])) { $orderby = 'name'; }

	$sort = isset($_PAGE['request']['sort']) ? $_PAGE['request']['sort'] : 'asc';
	$sort = strtolower($sort);
	$auth_fields = array('asc'=>1,'desc'=>1);
	if (!isset($auth_fields[$sort])) { $sort = 'asc'; }

	$req = '
		SELECT
			%%regions.%region_name,
			%%characters.%char_id,
			%%characters.%char_name,
			%%characters.%char_job,
			%%characters.%char_people,
			%%characters.%char_origin,
			%%characters.%char_content,
			%%jobs.%job_name as %char_jobname
		FROM %%characters
		LEFT JOIN %%jobs
			ON %%jobs.%job_id = %char_job
		LEFT JOIN %%regions
			ON %%regions.%region_id = %char_origin
		ORDER BY %char_'.$orderby.' '.$sort;

	$characters = $db->req($req);
	unset($req);
}
?>

<div class="container">
	<?php if ($char_id && $char) {
        load_module('character_view', 'module', [
            'char_id' => $char_id,
            'charObj' => $charObj,
            'char' => $char,
            'modifications' => $modifications,
            'ymlDumper' => $ymlDumper,
            'users' => $users,
            '_PAGE' => $_PAGE,
        ]);
	} elseif (is_array($characters)) {
	    load_module('character_list', 'module', [
	        'characters' => $characters,
            'sort' => $sort,
            'orderby' => $orderby,
            '_PAGE' => $_PAGE,
        ]);
	}
	unset($char_id, $char, $folder, $orderby, $auth_fields, $step, $characters, $c, $key, $val, $sort, $tags_order, $anchor, $output);
	?>
</div><!-- /container -->

	<?php
	buffWrite('css', <<<CSSFILE
	.listlinks a { color: #0088CC; }
	.charid { width: 5%; }
	.charname { width: 25%; font-weight: bold; }
	.charjob { width: 25%; }
	.charexp { width: 7%; }
	.charpeople { width: 13%; }
	.charorigin { width: 25%; }
	ul.char_list { margin-top: 15px; width: 70%; }
	ul.char_list li.bl.char a.bl {
		padding: 2px 10px;
		-webkit-transition: none;
		-moz-transition: none;
		-o-transition: none;
		transition: none;
		-webkit-border-radius:10px;
		-moz-border-radius:10px;
		border-radius:10px;
	}
	ul.char_list li.bl.char:hover a.bl {
		background: #f8f8f8;
		-webkit-box-shadow: 0 0 8px #ddd;
		-moz-box-shadow: 0 0 8px #ddd;
		box-shadow: 0 0 8px #ddd;
	}
	ul.char_list li.bl.char span.ib {
		margin: 0;
		text-align: left;
		font-size: 13px;
	}
CSSFILE
);
	buffWrite('js', <<<JSFILE
	$(document).ready(function(){
		$(".pageview").click(function() { return !window.open(this.href); });
	});
JSFILE
);
