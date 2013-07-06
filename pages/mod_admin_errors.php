<?php

$err_type = isset($_PAGE['request'][0]) ? $_PAGE['request'][0] : '';
$err_file = isset($_PAGE['request'][1]) ? $_PAGE['request'][1] : '';

$types_ok = array(
	'404' => 'Erreurs 404',
	'sql' => 'Erreurs BDD',
	'exectime' => 'Temps d\'exécution',
	'error_tracking' => 'Traçage des erreurs',
	'referer' => 'Différents referer',
);

$arr = array();
foreach ($types_ok as $v => $k) {
	$arr[$v] = scandir(ROOT.DS.'logs'.DS.$v.DS);
}

?>

<div class="container">

<div class="row-fluid">
	<?php
	foreach ($arr as $type => $list) {
		?><div class="span2">
		<h4><?php echo $types_ok[$type]; ?></h4>
		<?php
		foreach ($list as $k => $v) {
			if ($v !== '.' && $v !== '..') {
				$list[$k] = array('name'=>$v,'title'=>str_replace('.log', '', $v));
			} else {
				unset($list[$k]);
			}
		}
		unset($k,$v);
		//pr($list);
		$list = Hash::sort($list, '{n}.title', 'desc', 'natural');
		$i = 0;
		foreach ($list as $v) {
			if ($i >= 10) { break; }
			$size = filesize(ROOT.DS.'logs'.DS.$type.DS.$v['name']);
			if ($size >= 1073741824)	{ $size = number_format($size / 1073741824, 2) . ' Go'; }
			elseif ($size >= 1048576)	{ $size = number_format($size / 1048576, 2) . ' Mo'; }
			elseif ($size >= 1024)		{ $size = number_format($size / 1024, 2) . ' Ko'; }
			elseif ($size > 1)			{ $size = $size . ' octets'; }
			elseif ($size == 1)			{ $size = $size . ' octets'; }
			else { $size = 'Vide'; }
			$v['name'] = str_replace('.log', '', $v['name']);

			if ($v['title'] === date('Y.m.d')) {
				$v['title'] = '<span class="icon-red icon-arrow-right"></span> '.$v['title'];
			}
			$anchor = $v['title'].' <small>('.$size.')</small>';

			$request = array(
				isset($_PAGE['request'][0]) ? $_PAGE['request'][0] : '',
				isset($_PAGE['request'][1]) ? $_PAGE['request'][1] : ''
			);
			$add_class = $v['name'] === $request[1] && $type === $request[0] ? ' active' : '';
			echo mkurl(array('type' => 'tag', 'anchor' => $anchor, 'attr' => array('class'=>'btn btn-link btn-small btn-block'.$add_class), 'params' => array($type, $v['name'])));
			unset($anchor);
			$i++;
			//}
		}
		?>
		</div><?php
	}?>
</div>

<?php
unset($arr,$k,$type,$list,$size,$v);
if ($err_type && $err_file) {
	if (!isset($err_file) || !isset($err_type)) {
		Session::setFlash('Le fichier n\'a pas pu être chargé', 'error');
		return;
	}

	$contents = array();
	$fields = array();

	$file_to_load = ROOT.DS.'logs'.DS.$err_type.DS.$err_file.'.log';
	$exec_all = array();
	$exec_sum = 0;
	if (FileAndDir::fexists($file_to_load)) {
		$content = FileAndDir::get($file_to_load);

		$content = explode('*|*|*', $content);

		foreach ($content as $k => $v) {
			if ($v) {
				$v = explode('||', $v);
				foreach ($v as $kk => $vv) {
					$vv = explode('=>', $vv);
					$vv[1] = json_decode($vv[1], true);
					if (!isset($fields[$vv[0]])) { $fields[$vv[0]] = 1; }
					$contents[$k][$vv[0]] = @$vv[1];
					if ($vv[0] === 'Exectime') { $exec_all[] = $vv[1]; $exec_sum += $vv[1]; }
				}
			}
		}
	}
	unset($v,$k,$vv,$kk,$file_to_load, $content);

	$exec = false;
	if (!empty($exec_all)) {
		$exec = true;
		$exec_max = max($exec_all);
		$exec_sum = $exec_sum / count($exec_all);
		$exec_min = min($exec_all);
	}
	unset($exec_all);
	rsort($contents);

	?>
		<h3><?php echo $types_ok[$err_type]; ?> <small>le <?php echo $err_file; ?></small></h3>
			<?php
			unset($types_ok, $err_type, $err_file);
			if ($exec === true) { ?>
			<table class="table"><tr>
				<td class="maxval"><?php tr('Temps maximum'); ?> : <strong><?php echo number_format($exec_max, 4, ',', ' '); ?></strong> <span class="icon-search"></span></td>
				<td class="medval"><?php tr('Temps moyen'); ?> : <strong><?php echo number_format($exec_sum, 4, ',', ' '); ?></strong></td>
				<td class="minval"><?php tr('Temps minimum'); ?> : <strong><?php echo number_format($exec_min, 4, ',', ' '); ?></strong> <span class="icon-search"></span></td>
			</tr></table>
			<?php }
			unset($exec);
			?>
		<table id="content_table" class="table table-striped table-condensed table-hover">
			<tr>
			<?php foreach ($fields as $field => $v) { ?>
				<th><?php echo $field; ?></th>
			<?php }
			unset($fields, $field, $v);?>
			</tr>
			<?php foreach ($contents as $v) {
				$class = '';
				if (isset($v['Exectime'])) {
					$value = $v['Exectime'];
					if ($value === $exec_max) {
						$class = ' class="warning"';
					} elseif ($value === $exec_min) {
						$class = ' class="success"';
					}
				} ?>
				<tr<?php echo $class;?>>
					<?php foreach ($v as $item => $value) { ?>
					<td class="<?php echo $item; ?>"><?php
						if ($item === 'Traçage') {
							?><button class="showhidden btn btn-small"><span class="icon-plus"></span></button><div class="hid"><?php pr($value); ?></div><?php
						} elseif ($item === 'Date') {
							$val = strtotime($value);
							echo date('H:i:s', $val);
						} elseif ($item === 'Exectime') {
							if ($value === $exec_max) {
								echo '<span class="warning">'.number_format($value, 4, ',', ' ').'</span>';
							} elseif ($value === $exec_min) {
								echo '<span class="success">'.number_format($value, 4, ',', ' ').'</span>';
							} else { echo number_format($value, 4, ',', ' '); }
						} elseif (is_array($value) || is_object($value)) {
							foreach ($value as $kk => $vv) {
								if (is_array($vv)) {
									?><button class="showhidden btn btn-small"><span class="icon-plus"></span></button><div class="hid"><?php pr($vv); ?></div><?php
								} else {
									echo '['.$kk.'] = '.$vv.'<br />';
								}
							}
							unset($kk,$vv);
						} else {
							echo $value;
						}
						unset($value, $item);
					?></td>
					<?php } ?>
				</tr>
			<?php }
		unset($exec_max,$exec_sum,$exec_min,$contents, $v);
		?>
		</table>
<?php }
unset($err_file,$types_ok, $err_type);
?>

</div><!-- /container -->

<?php
	buffWrite('css', <<<CSSFILE
	.maxval:hover, .minval:hover { cursor: pointer; }
	.maxval:hover { background-color: #FCF8E3; color: #C09853; border: solid 1px #FBEED5; }
	.minval:hover { background-color: #DFF0D8; color: #468847; border: solid 1px #D6E9C6;  }
	table tr td.maxval, table tr td.minval, table tr td.medval { border: solid 1px transparent;  }
	[class*="Page.request"] {
		width: 110px;
	}
	#content_table {
		font-size: 11px;
	}
	h4, th, table tr td.maxval, table tr td.minval, table tr td.medval { text-align: center; }
CSSFILE
);
	buffWrite('js', <<<JSFILE
	$(document).ready(function(){
		$('td.maxval').hover(function(){
			$(this).find('[class*=icon-]').addClass('icon-yellow');
		}, function(){
			$(this).find('[class*=icon-]').removeClass('icon-yellow');
		});
		$('td.minval').hover(function(){
			$(this).find('[class*=icon-]').addClass('icon-green');
		}, function(){
			$(this).find('[class*=icon-]').removeClass('icon-green');
		});
		$('td.maxval').click(function(){
			$("html, body").animate({ scrollTop: $('tr.warning').position().top }, 750);
		});
		$('td.minval').click(function(){
			$("html, body").animate({ scrollTop: $('tr.success').position().top }, 750);
		});
	});
JSFILE
);