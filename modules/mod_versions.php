<?php

$versions_xml = file_get_contents(ROOT.DS.'versions.xml');

$versions = new SimpleXMLElement($versions_xml);
unset($versions_xml);

$pagelist = array();
foreach($_PAGE['list'] as $v) {
	$pagelist[$v['page_getmod']] = $v['page_id'];
}

$updates = array();
$total_maj = 0;

$cacheFile = ROOT.DS.'tmp'.DS.'modversions.html';
if (file_exists($cacheFile) && filemtime($cacheFile) >= (time() - 86400) && $cnt = file_get_contents($cacheFile)) {
	echo $cnt;
	return;
}

ob_start();

$t = $db->req('SELECT %gen_step,%gen_mod,%gen_anchor FROM %%steps ORDER BY %gen_step ASC');//On génère la liste des étapes
$steps = array();
foreach ($t as $v) {//On formate la liste des étapes
	$steps[$v['gen_mod']] = 'Générateur : Étape '.$v['gen_step'].' - '.$v['gen_anchor'];
}

foreach ($versions->version as $v) {
	$day	= preg_replace('#^(\d{4})(\d{2})(\d{2})$#iU', '$3', (string)$v['date']);
	$month	= preg_replace('#^(\d{4})(\d{2})(\d{2})$#iU', '$2', (string)$v['date']);
	$year	= preg_replace('#^(\d{4})(\d{2})(\d{2})$#iU', '$1', (string)$v['date']);
	$date = $day.'/'.$month.'/'.$year;
	$code = (string) $v['code'];
	$updates[$code] = array(
		'tasks' => array(),
		'date' => $date,
	);
	$i = 0;
	foreach ($v->tasks->task as $task) {
		$element = array(
			'name' => (string)$task->element['name'],
			'id' => (int)$task->element['id'],
			'module' => (string) $task->element['module'],
			'title' => (string)$task->element,
			'comments' => array()
		);
		foreach ($task->comment as $comment) {
            if (strpos($comment, 'redmine#') !== false) {
                $comment = preg_replace('~redmine#(\d+)~', '', $comment);
            }
			$element['comments'][] = (string) $comment;
			$i++;
			$total_maj++;
		}
		$updates[$code]['tasks'][(string)$task['type']][] = $element;
	}
	$updates[$code]['modifications'] = $i;
}
unset($v, $comment, $task, $day, $month, $year, $date, $code, $element, $versions);

?>
<div class="container">
	<h2><?php echo 'Dernières mises à jour'; ?></h2>
	<p><?php echo 'Nombre total de versions : ', ' ', count($updates); ?></p>
	<p><?php echo 'Nombre total de mises à jour : ', ' ', $total_maj; ?></p>
	<ul id="versions">
		<?php
		unset($total_maj);
			foreach ($updates as $code => $update) { ?>
		<li class="version clearfix">
			<h4 class="version_name">
				<span class="icon-plus"></span>
				<?php echo 'Version', ' ', $code, ' &ndash; ', $update['date'];
                $now = new DateTime();
                $versionDate = DateTime::createFromFormat('d/m/Y', $update['date']);
				if ($now->diff($versionDate)->days < 7) { echo ' <small style="color:#881111;">', 'New !', '</small>'; }?>
				&nbsp;<small>(<?php echo $update['modifications'];?> <?php echo 'modification', $update['modifications'] > 1 ? 's' : ''; ?>)</small>
			</h4>
			<div class="taskslist">
			<?php
			foreach($update['tasks'] as $type => $taskslist) {
				?><div class="row-fluid">
					<div class="span2">
						<h5 class=""><?php
							if		($type == 'page')	{ echo 'Pages'; }
							elseif	($type == 'function'){echo 'Fonctionnalités'; }
							elseif	($type == 'css')	{ echo 'Design'; }
							elseif	($type == 'db')		{ echo 'Base de données'; }
							elseif	($type == 'js')		{ echo 'Javascript/jQuery'; }
							else						{ echo 'Autres'; }
						?></h5>
					</div>
					<div class="span9"><?php
					foreach ($taskslist as $task) {
						$element = '';
						if ($task['id'] && $_PAGE['list'][$task['id']]['page_anchor']) {
							if($task['id'] == 62 && $task['module']) {
								$mod = preg_replace('#^.*/([a-z0-9_]+)\.php$#isUu', '$1', $task['module']);
								if (isset($steps[$mod])) { $element = mkurl(array('val'=>62, 'type'=>'tag', 'attr'=>array('class'=>'btn btn-link btn-block btn_all_links'), 'anchor'=>$steps[$mod], 'params'=>$mod)); }
								//$element = mkurl(array());
								unset($mod);
							} else {
								$element = mkurl(array('val'=>$task['id'], 'type'=>'tag', 'attr' => array('class'=>'btn btn-link btn-block btn_all_links')));
							}
						} elseif ($task['name']) {
							foreach($pagelist as $get => $id) {
								if (strpos($task['name'], (string) $get) !== false) {
									if ($_PAGE['list'][$id]['page_anchor']) {
										$element = $_PAGE['list'][$id]['page_anchor'];
									}
								}
							}
							if (!$element) { $element = $task['title']; }
						} else { $element = $task['title']; }
						?>
							<div class="row-fluid elements_list">
								<div class="span4"><h6><?php echo $element; ?></h6></div>
								<div class="span8"><p><?php foreach ($task['comments'] as $com) { echo $com, '<br />'; }?></p></div>
							</div>
						<?php
					}
					?>
					</div>
				</div><?php
			}
			?>
			<div><!--/.taskslist-->
		</li><?php
			}
		unset($pagelist,$updates, $update, $code, $taskslist, $task, $element, $get, $id, $com, $type);
		?>
	</ul><!-- /ul#versions -->
</div>
	<?php
	buffWrite('css', /** @lang CSS */ '
	.btn_all_links { padding: 0; text-align: left; font-size: 1em; }
	#versions, #versions ul, #versions li, #versions ul p, #versions ul h5 { margin-top: 0; }
	#versions, #versions ul { list-style-type: none; }
	#corps .container ul#versions li { font-size: 0.75em; }
	li.version { margin-top: 20px; }
	li.version h4:hover { cursor: pointer; }
	.taskslist { display: none; }
	.elements_list {
		-webkit-border-radius: 15px;
		   -moz-border-radius: 15px;
		        border-radius: 15px;
		margin: 0;
		padding: 0 14px;
	}
	.taskslist {
		margin-bottom: 7px;
	}
	.elements_list:hover {
		-webkit-box-shadow: 0 0 10px #ddd;
		-moz-box-shadow: 0 0 10px #ddd;
		box-shadow: 0 0 10px #ddd;
	}
	li.version:after{
		display: block;
		content: "";
		width: 92%;
		height: 2px;
		-webkit-box-shadow: 0 0 10px #bbb;
		-moz-box-shadow: 0 0 10px #bbb;
		box-shadow: 0 0 10px #bbb;
		margin-bottom: 20px;
	}
	li.version:hover:after {
		-webkit-box-shadow: 0 0 10px #d69999;
		-moz-box-shadow: 0 0 10px #d69999;
		box-shadow: 0 0 10px #d69999;
	}
	h5,h6,p { margin-top: 3px; margin-bottom: 3px; }
	');

	buffWrite('js', /** @lang JavaScript */ <<<JSFILE
	$(document).ready(function(){
		$('li.version h4').hover(function(){
			$(this).find('span[class*=icon-]').addClass('icon-red');
		}, function(){
			$(this).find('span[class*=icon-]').removeClass('icon-red');
		});
		$('li.version h4').click(function(e){
			if (!$(e.target).is('a')) {
				$(this).parents('li.version').find('span[class*=icon-]').attr('class', function(){return $(this).is('.icon-plus') ? 'icon-minus' : 'icon-plus';});
				$(this).parents('li.version').find('.taskslist').stop().slideToggle(400);
			}
		});
	});
JSFILE
);


$cacheFileContent = ob_get_clean();

if (!is_dir(dirname($cacheFile))) {
	mkdir(dirname($cacheFile), 0777, true);
}

file_put_contents($cacheFile, $cacheFileContent);
touch($cacheFile);

echo $cacheFileContent;
