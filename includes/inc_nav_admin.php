<?php
##Menu du mode debug
use App\Users;

if (P_LOGGED === true) { ?>

			<div class="navbar">
				<div class="navbar-inner">
					<?php
					$class = ($_PAGE['id'] == 34 && !isset($_PAGE['request'][0]) ? ' class="brand active"' : 'class="brand"');
					echo mkurl(array('val'=>34, 'type' => 'tag', 'attr' => $class)); ?>
					<div class="nav-collapse collapse">
						<ul class="nav">
							<?php
							foreach($_PAGE['list'] as $id => $pg) {
								if ($pg['page_show_in_debug'] == '1' && $pg['page_acl'] >= Users::$acl) {
									$active = $_PAGE['get'] == $pg['page_getmod'] ? ' class="active"' : '';
									echo '<li'.$active.'>'.mkurl(array('val'=>$id, 'type'=>'tag','trans'=>true)).'</li>';
								}
							}
							unset($id,$pg,$active,$class);
							/*?>
							<li class="dropdown<?php if ($_PAGE['id'] == 34) { echo ' active'; } ?>">
								<a class="dropdown-toggle" data-toggle="dropdown" href><?php tr($_PAGE['list'][34]['page_anchor']); ?><span class="caret"></span></a>
								<ul class="dropdown-menu">
									<li><</li>
								</ul>
							</li>
							*/ ?>
						</ul><!--/ul.nav-->
					</div><!--/.nav-collapse-->
				</div><!--/.navbar-inner-->
			</div><!--/.navbar-->

<?php }
