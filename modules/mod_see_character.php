<?php

$folder = ROOT.DS.'webroot'.DS.'files'.DS.'characters_export';

$char_id = isset($_PAGE['request'][0]) ? $_PAGE['request'][0] : 0;

$ymlDumper = new Symfony\Component\Yaml\Dumper();
$ymlDumper->setIndentation(2);

$orderby = $sort = $char = $characters = $modifications = $users = null;
if ($char_id) {
	$char = $db->row('SELECT %char_id,%char_name,%%characters.%char_content FROM %%characters WHERE %char_id = ?', $char_id);
    if (!$char) {
        redirect(array(), 'Aucun personnage trouvé', 'warning');
    }
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
		$_PAGE['title_for_layout'] = $char['char_name'];
		?>
		<h3><?php echo $char['char_name']; ?></h3>
		<?php if (P_DEBUG === true) { ?>
		<button class="showhidden btn btn-small"><span class="icon-plus"></span></button><div class="hid"><?php pr(Esterenchar::sdecode_char($char['char_content'])); ?></div>
		<?php } ?>
		<div class="row-fluid">
			<div class="span6 sheetlist">
			<?php
				echo mkurl(
					array(
						'val'=>49,
						'type'=>'tag',
						'ext' => 'zip',
						'anchor'=>'Tout télécharger au format ZIP',
						'trans' => true,
						'attr'=>array('class'=>'btn pageview btn-block'),
						'params'=>array(
							$char_id,
							'zip'=>true,
							clean_word($char['char_name'])
						)
					)
				);
				?>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span6 sheetlist">
                <br>
				<?php
				echo mkurl(array('val'=>49, 'type'=>'tag', 'ext' => 'pdf', 'anchor'=>'Version originale', 'trans' => true, 'attr'=>array('class'=>'btn pageview'), 'params'=>array($char_id,'pdf'=>true, clean_word($char['char_name']))));
				?>
                <br>
				<?php
				echo mkurl(array('val'=>49, 'type'=>'tag', 'ext' => 'pdf', 'anchor'=>'Version originale "Printer Friendly"', 'trans' => true, 'attr'=>array('class'=>'btn pageview'), 'params'=>array($char_id,'pdf'=>true,'print'=>true, clean_word($char['char_name']))));
				?>
			</div>
		</div>

        <?php if ($modifications && count($modifications)) { ?>
            <h2>Modifications:</h2>
            <div class="row-fluid">
                <div class="span4"><h3 class="center"><?php tr('Date &ndash; utilisateur'); ?></h3></div>
                <div class="span8"><h3 class="center"><?php tr('Modification'); ?></h3></div>
            </div>
            <?php
            $domains = []; $a = $db->req('SELECT %domain_id, %domain_name FROM %%domains');
            foreach ($a as $domain) { $domains[$domain['domain_id']] = $domain; }
            $disciplines = []; $a = $db->req('SELECT %disc_id, %disc_name FROM %%disciplines');
            foreach ($a as $discipline) { $disciplines[$discipline['disc_id']] = $discipline; }

            foreach ($modifications as $mod) { ?>
                <?php
                $contentBefore = json_decode($mod['charmod_content_before'], true);
                $contentAfter = json_decode($mod['charmod_content_after'], true);
                if (!count($contentBefore) && !count($contentAfter)) { continue; }
                $before = $after = array();
                $processed = load_module('character_diff', 'module', array(
                    'before' => $contentBefore,
                    'after' => $contentAfter,
                    'referenceDomains' => $domains,
                    'referenceDisciplines' => $disciplines,
                ));
                if (empty($processed)) { continue; }
                $processed = $ymlDumper->dump($processed, 6, 0);
                $content = $processed;
                $content = str_replace("': '", ': ', $content);
                $content = str_replace("'\n", "\n", $content);
                $content = str_replace("''", "'", $content);
                $content = str_replace(": '", ': ', $content);
                $content = preg_replace('~\n *( *- *)?\'~isUu', "\n  ", $content);
                ?>
                <div class="row-fluid">
                    <div class="span4">
                        <strong><?php echo date('Y-m-d \&\n\b\s\p\; H:i:s', $mod['charmod_date']); ?></strong> &ndash;
                        <?php echo $users[$mod['user_id']]; ?>
                    </div>
                    <div class="span8">
                        <pre><?php echo $content; ?></pre>
                    </div>
                </div>
        <?php }
        }




	} elseif (is_array($characters)) {
		?>
		<h3><?php tr('Personnages enregistrés'); ?> : <?php echo count($characters); ?></h3>
		<ul class="unstyled char_list bl mid"><?php

		$tags_order = array(
			'charname'	=>$orderby == 'name'		? ($sort == 'asc' ? '&#x25b2;' : '&#x25bc;') : '',
			'charjob'	=>$orderby == 'jobname'	? ($sort == 'asc' ? '&#x25b2;' : '&#x25bc;') : '',
			'charpeople'=>$orderby == 'people'	? ($sort == 'asc' ? '&#x25b2;' : '&#x25bc;') : '',
			'charorigin'=>$orderby == 'origin'	? ($sort == 'asc' ? '&#x25b2;' : '&#x25bc;') : '',
		);

		$sort = isset($_PAGE['request']['sort']) ? ($sort == 'asc' ? 'desc' : 'asc') : 'asc';
			$output = '
				<li class="bl"><span class="btn btn-block btn-link listlinks">'
					.mkurl(array('val'=>$_PAGE['id'], 'type'=> 'TAG', 'anchor'=>$tags_order['charname'].'#', 'attr' =>'class="ib charid"', 'params'=>array('orderby'=>'id', 'sort'=>$sort)))
					.mkurl(array('val'=>$_PAGE['id'], 'type'=> 'TAG', 'anchor'=>$tags_order['charname'].tr('Nom',true), 'attr' =>'class="ib charname"', 'params'=>array('orderby'=>'name', 'sort'=>$sort)))
					.mkurl(array('val'=>$_PAGE['id'], 'type'=> 'TAG', 'anchor' =>$tags_order['charjob'].tr('Métier',true), 'attr' =>'class="ib charjob"', 'params' =>array('orderby'=>'jobname', 'sort'=>$sort)))
					.mkurl(array('val'=>$_PAGE['id'], 'type'=> 'TAG', 'anchor' =>$tags_order['charpeople'].tr('Peuple', true),'attr'=>'class="ib charpeople"', 'params' =>array('orderby'=>'people', 'sort'=>$sort)))
					.mkurl(array('val'=>$_PAGE['id'], 'type'=> 'TAG', 'anchor' =>$tags_order['charorigin'].tr('Origine', true),'attr'=>'class="ib charorigin"', 'params' =>array('orderby'=>'origin', 'sort'=>$sort)))
				.'</span></li>';
			foreach ($characters as $c) {
				if (strlen($c['char_name']) > 2) {
					$anchor =
						'<span class="ib charid">'.$c['char_id'].'</span>'
						.'<span class="ib charname">'.$c['char_name'].'</span>'
						.'<span class="ib charjob">'.($c['char_jobname'] ? $c['char_jobname'] : ' ('.tr('Personnalisé', true).') '.$c['char_job']).'</span>'
						.'<span class="ib charpeople">'.$c['char_people'].'</span>'
						.'<span class="ib charorigin">'.$c['region_name'].'</span>';
					$output .= '<li class="bl char">'.mkurl(array('val'=>$_PAGE['id'], 'type'=>'TAG', 'anchor'=>$anchor, 'attr'=>'class="bl mid"', 'params'=>$c['char_id'])).'</li>';
				}
			}
			echo $output;
			?>
		</ul><?php
	}
	unset($char_id, $char, $folder, $orderby, $auth_fields, $step, $characters, $c, $key, $val, $sort, $tags_order, $anchor, $output);
	?>
</div><!-- /container -->

	<?php
	buffWrite('css', <<<CSSFILE
	.listlinks a { color: #0088CC; }
	.charid { width: 6%; }
	.charname { width: 27%; font-weight: bold; }
	.charjob { width: 27%; }
	.charpeople { width: 15%; }
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
