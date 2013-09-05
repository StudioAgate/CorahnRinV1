<?php
$content_for_layout = 'BLABLA';
$this->more_css('main.css');
?>
<div class="container">
	<h2><?php tr('Dernières mises à jour'); ?></h2>
	<p><?php echo tr('Nombre total de versions : ', true), ' ', count($updates); ?></p>
	<p><?php echo tr('Nombre total de mises à jour : ', true), ' ', $total_maj; ?></p>
	<ul id="versions">
		<?php
		unset($total_maj);
			foreach ($updates as $code => $update) { ?>
		<li class="version clearfix">
			<h4 class="version_name">
				<span class="icon-plus"></span>
				<?php echo tr('Version', true), ' ', $code, ' &ndash; ', $update['date'];
				if ($update['date'] === date('d/m/Y')) { echo ' <small style="color:#881111;">', tr('Aujourd\'hui !', true), '</small>'; }?>
				<small>(<?php echo $update['modifications'];?> <?php tr('modification'); echo $update['modifications'] > 1 ? 's' : ''; ?>)</small>
			</h4>
			<div class="taskslist">
			<?php
			foreach($update['tasks'] as $type => $taskslist) {
				?><div class="row-fluid">
					<div class="span2">
						<h5 class=""><?php
							if		($type == 'page')	{ tr('Pages'); }
							elseif	($type == 'function'){tr('Fonctionnalités'); }
							elseif	($type == 'css')	{ tr('Design'); }
							elseif	($type == 'db')		{ tr('Base de données'); }
							elseif	($type == 'js')		{ tr('Javascript/jQuery'); }
							elseif	($type == 'modules'){ tr('Modules'); }
							else						{ tr('Autres'); }
						?></h5>
					</div>
					<div class="span9"><?php
					foreach ($taskslist as $task) {
						$element = '';
						$element = $task['title'];
						?>
							<div class="row-fluid elements_list">
								<div class="span4"><h6><?php echo $element;?></h6></div>
								<div class="span8"><p><?php foreach ($task['comments'] as $com) { tr($com);echo '<br />'; }?></p></div>
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
<style type="text/css">
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
</style>