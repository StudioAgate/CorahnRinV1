<?php

use App\Session;
use App\Translate;

if (isset($_SESSION['etape'])) {
		$etape = (int) $_SESSION['etape'];
	} else {
		$etape = 1;
	}
	$dropdown = '';
	unset($attr, $etape, $val, $key);
	if ($dropdown) {
		$dropdown = ($_SESSION['etape'] > 0 ? '<li>'.mkurl(array('val'=>52, 'type' => 'TAG')).'</li>' : '').'
		<li class="dropdown">
			<a class="dropdown-toggle" data-toggle="dropdown">'.tr('Revenir à une autre étape', true).' <b class="caret"></b></a>
			<ul class="dropdown-menu">'.$dropdown.'</ul>
		</li>';
	}

    Translate::$domain = 'menu';

	//$_PAGE['list'] = Hash::sort($_PAGE['list'], 'page_step', 'asc');
?>

			<div class="navbar">
				<div class="navbar-inner">
					<div class="container">
						<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"><?php echo tr('Menu du site'); ?></a>
						<?php echo mkurl(array('val'=>1,'trans'=>true, 'type'=>'tag','attr'=>'class="brand'.($_PAGE['id'] == 1 ? ' active' : '').'"')); ?>
						<div class="nav-collapse collapse">
							<ul class="nav">
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
								<?php echo $dropdown; unset($dropdown); ?>
								<?php
								foreach($_PAGE['list'] as $id => $page) {
									if ($page['page_show_in_menu'] == '1') {
										$active = $_PAGE['get'] == $page['page_getmod'] ? ' class="active"' : '';
										echo '<li'.$active.'>'.mkurl(array('val'=>$id, 'type'=> 'TAG','trans'=>true)).'</li>';
									}
								}
								unset($id,$page,$active);
								?>
								<li class="dropdown">
									<a class="dropdown-toggle" data-toggle="dropdown"><?php tr('Changer la langue'); ?> <span class="caret"></span></a>
									<ul class="dropdown-menu">
										<li<?php echo P_LANG === 'fr' ? ' class="active"' : ''; ?>><?php echo mkurl(array('val'=>$_PAGE['id'], 'type' => 'tag', 'anchor' => 'Français','lang'=>'fr', 'params' => $_PAGE['request'])); ?></li>
										<li<?php echo P_LANG === 'en' ? ' class="active"' : ''; ?>><?php echo mkurl(array('val'=>$_PAGE['id'], 'type' => 'tag', 'anchor' => 'Anglais','lang'=>'en', 'params' => $_PAGE['request'])); ?></li>
									</ul>
								</li>
								<?php if (P_LOGGED === false) {
									?><li><?php echo mkurl(array('val'=>48, 'type' => 'tag')); ?></li><?php
									?><li><?php echo mkurl(array('val'=>56, 'type' => 'tag')); ?></li><?php
								} else {
									?><li><?php echo mkurl(array('val'=>48, 'type' => 'tag', 'anchor' => 'Déconnexion', 'trans'=>true, 'params' => array('logout'))); ?></li><?php
								} ?>
							</ul>
						</div><!--/.nav-collapse -->
					</div><!--/.container-->
				</div><!--/.navbar-inner-->
			</div><!--/.navbar-->

<?php if (P_LOGGED === true) { require ROOT.DS.'includes'.DS.'inc_nav_admin.php'; }

Translate::$domain = null;
