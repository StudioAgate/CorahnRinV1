<?php
if (!isset($links)) {
	Session::setFlash('Le menu n\'a pas pu être correctement chargé...', 'error');
	return;
}
?>
<nav class="navbar">
				<div class="navbar-inner">
					<div class="container">
						<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"><?php echo tr('Menu du site'); ?></a>
						<?php
			// 				echo mkurl(array('val'=>1, 'type'=>'tag','attr'=>'class="brand'.($_PAGE['id'] == 1 ? ' active' : '').'"'));
							$active = $asked_uri ? '' : ' active';
							echo Router::link(array('route_name'=>'core_home', 'attr'=>array('class'=>'brand'.$active), 'type'=>'tag', 'anchor'=>'Accueil', 'translate'=>true));
						?>

						<div class="nav-collapse collapse">
							<ul class="nav"><?php
								foreach ($links as $anchor => $route) {
									$login = isset($route['login']) ? (bool) $route['login'] : null;
									$active = '';
									if ($login === P_LOGGED || $login === null) {
										if (isset($route['url'])) {
											if (strpos($asked_uri, $route['url'])) {
												$active = 'class="active"';
											}
											$rt = Router::get($route['url'], 'uri', 'route');
											if (isset($rt['name'])) {
												$a = Router::link(array('route_name'=>$rt['name'], 'force_route'=>true, 'anchor'=>$anchor, 'type'=>'tag'));
											} else {
												$a = Router::link(array('route'=>$route['url'], 'anchor'=>$anchor, 'type'=>'tag'));
											}
											?>

								<li<?php echo $active;?>><?php echo $a; ?></li><?php
										} elseif (isset($route['route'])) {
											if (strpos($asked_uri, Router::route('', $route['route']))) {
												$active = 'class="active"';
											}
											$a = Router::link(array('route_name'=>$route['route'], 'force_route' => true, 'anchor'=>$anchor, 'type'=>'tag'));
											?>

								<li<?php echo $active;?>><?php echo $a; ?></li><?php
										}
									}
								}
								/*?>
								<li<?php echo Session::read('etape') != 0 ? ' class="active"' : ''; ?>><?php
								if (Session::read('etape') == 21) {
									$step = 20;
								} elseif (Session::read('etape') > 0) {
									$step = Session::read('etape');
								} else {
									$step = 1;
								}
								//echo mkurl(array('val'=> $step, 'field' => 'step', 'type' =>  'TAG', 'anchor' =>  "Générer un personnage"));
								unset($step);

								?></li>
								<?php
								/*
								echo $dropdown; unset($dropdown); ?>
								<?php
								foreach($_PAGE['list'] as $id => $page) {
									if ($page['page_show_in_menu'] == '1') {
										$active = $_PAGE['get'] == $page['page_getmod'] ? ' class="active"' : '';
										echo '<li'.$active.'>'.mkurl(array('val'=>$id, 'type'=> 'TAG')).'</li>';
									}
								}
								unset($id,$page,$active);
								?>
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php tr('Changer la langue'); ?> <span class="caret"></span></a>
									<ul class="dropdown-menu">
										<li<?php echo P_LANG == 'fr' ? ' class="active"' : ''; ?>><?php echo mkurl(array('val'=>45, 'type' => 'tag', 'anchor' => 'Français', 'params' => array('fr'))); ?></li>
										<li<?php echo P_LANG == 'en' ? ' class="active"' : ''; ?>><?php echo mkurl(array('val'=>45, 'type' => 'tag', 'anchor' => 'Anglais', 'params' => array('en'))); ?></li>
									</ul>
								</li>
								<?php if (P_LOGGED === false) {
									?><li><?php echo mkurl(array('val'=>48, 'type' => 'tag')); ?></li><?php
									?><li><?php echo mkurl(array('val'=>56, 'type' => 'tag')); ?></li><?php
								} else {
									?><li><?php echo mkurl(array('val'=>48, 'type' => 'tag', 'anchor' => 'Déconnexion', 'params' => array('logout'))); ?></li><?php
								}
								//*/
								 ?>

							</ul>
						</div><!--/.nav-collapse -->
					</div><!--/.container-->
				</div><!--/.navbar-inner-->
			</nav><!--/.navbar-->
