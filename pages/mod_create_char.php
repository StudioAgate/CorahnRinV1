
<div class="container">
	<?php

	$page_mod = isset($_PAGE['request'][0]) ? $_PAGE['request'][0] : '';
	$t = $db->req('SELECT %gen_step,%gen_mod,%gen_anchor,%gen_id FROM %%steps ORDER BY %gen_step ASC');//On génère la liste des étapes
	$steps = array();
	$page_step = 0;
    $page_title = null;

	foreach ($t as $v) {//On formate la liste des étapes
		$steps[$v['gen_id']] = array(
			'step' => $v['gen_step'],
			'mod' => $v['gen_mod'],
			'title' => $v['gen_anchor'],
		);
		if ($page_mod === $v['gen_mod']) {
			$page_title = $v['gen_anchor'];
			$page_step = (int) $v['gen_step'];
		}
	}

	if (!$page_mod || !$page_title || !$page_step) {
		$page_mod = $steps[1]['mod'];
		header('Location:'.mkurl(array('params'=>array($page_mod))));
		exit;
	}
	if (!$page_step) { return; }
	//if (isset($page_title)) { $_PAGE['anchor'] = $page_title; }
	unset($t, $v);
	if (!Session::read('etape')) { Session::write('etape', 1); }
	$step = Session::read('etape');//On récupère l'étape en cours

	if (!FileAndDir::fexists(ROOT.DS.'modules_'.$_PAGE['get'].DS.'mod_'.$page_mod.'.php')) {
		Session::setFlash('Le module "'.$page_mod.'" n\'existe pas dans "'.$_PAGE['get'].'"', 'error');
		return;
	}

	if ($step < $page_step && $step > 0) {
		header('Location:'.mkurl(array('params'=>array($steps[$step]['mod']))));
		exit;
	}

	if (isset($steps[$page_step])) {

		$datas = array(
			'steps' => $steps,
			'page_mod' => $page_mod,
			'page_step' => $page_step,
			'p_stepval' => Session::read($steps[$page_step]['mod']),
			'p_action' => isset($steps[$page_step+1]) ? mkurl(array('params'=>array($steps[$page_step+1]['mod']))) : '',
			'p_prev' => $page_step > 1 ? mkurl(array('params'=>array($steps[$page_step-1]['mod']))) : '',
		);
		$_PAGE['more_css'][] = BASE_URL.'/css/pages/pg_'.$page_mod.'.css';
		$_PAGE['more_js'][] = BASE_URL.'/js/pages/pg_'.$page_mod.'.js';


		/*
		if (P_DEBUG === true) {
			# Affichage de l'étape en cours
			echo '<div class="container">';
			pr(array($steps[$page_step]['mod'] => $datas['p_stepval']));
			echo '</div>';
		}
		//*/
		?>
		<div id="formgen" class="container">

			<ul id="create_char_links" class="nav nav-tabs"><?php

			foreach ($steps as $etape => $v) {
				$active = $page_step == $etape ? ' class="active"' : '';
				$anchor = $v['step'].'. '.tr($v['title'], true);
				if ($step >= $etape) {
					?><li<?php echo $active; ?>>
						<?php echo mkurl(array('params'=>array($v['mod']), 'type' => 'tag', 'attr' => array('class'=>'create_char_link'), 'anchor' => $anchor)); ?>
					</li><?php
				} else {
					?><li<?php echo $active; ?>>
						<a class="create_char_link disabled"><?php echo $anchor; ?></a>
					</li><?php
				}
			}

			?>
				<li>
					<?php echo mkurl(array('val'=>52, 'type' => 'tag', 'trans'=>true, 'attr' => array('class'=>'create_char_link'))); ?>
				</li>
			</ul>
			<!--<div class="progress"><div class="bar bar-black" style="width: <?php echo $page_step*5; ?>%;"></div></div>-->

			<h2><?php tr($page_title); ?></h2>
			<?php
			if ($datas['p_prev']) {
				?><a href="<?php echo $datas['p_prev']; ?>" class="btn" id="gen_prev">&larr; <?php tr("Étape précédente"); ?></a>
			<?php } ?>
			<a href="<?php echo isset($_SESSION[$page_mod]) ? $datas['p_action'] : '#'; ?>" class="btn<?php echo $datas['p_stepval'] ? ' vsbl' : ''; ?>" id="gen_send"><?php tr("Étape suivante"); ?> &rarr;</a>

			<?php
				load_module($page_mod, 'module', $datas);
			?>

		</div><!--/#formgen-->
		<?php
	}
	?>
<script type="text/javascript">var nextsteptranslate = '<?php tr("Étape suivante &rarr;"); ?>';</script>
</div>
<!-- /container -->
<?php
buffWrite('css', <<<CSSFILE
	ul#create_char_links {
		/*border: medium none;*/
		margin: 0;
	}
	ul#create_char_links .create_char_link {
		font-size: 12px;
		margin-bottom: 1px;
		padding: 3px 6px 1px 6px;
		color: #000;
	}
	ul#create_char_links .create_char_link.disabled {
		color: #aaa;
	}
	ul#create_char_links .create_char_link.disabled:hover {
		background: transparent;
		border-color: transparent;
	}

CSSFILE
);
	buffWrite('js', <<<JSFILE

JSFILE
);