<?php

	$sql = '
	SELECT
		%%domains.%domain_name, %%domains.%domain_id,
		%%jobs.%job_name, %%jobs.%job_desc, %%jobs.%job_book, %%jobs.%job_id,
		%%jobdomains.%jobdomain_primsec
	FROM %%jobdomains
	INNER JOIN %%domains
		ON %%jobdomains.%domain_id = %%domains.%domain_id
	INNER JOIN %%jobs
		ON %%jobdomains.%job_id = %%jobs.%job_id';
	$result = $db->req($sql);
	unset($sql);

	$jobs_books = array();
	foreach ($result as $v) {
		$jobs_books[$v['job_book']][$v['job_id']]['job_id'] = $v['job_id'];
		$jobs_books[$v['job_book']][$v['job_id']]['job_name'] = $v['job_name'];
		$jobs_books[$v['job_book']][$v['job_id']]['job_desc'] = $v['job_desc'];
		$jobs_books[$v['job_book']][$v['job_id']]['job_desc'] = $v['job_desc'];
		if ($v['jobdomain_primsec'] == '1') {
			$jobs_books[$v['job_book']][$v['job_id']]['domains_primsec']['primaire'] = array(
				'domain_id' => $v['domain_id'],
				'domain_name' => $v['domain_name'],
			);
		} else {
			$jobs_books[$v['job_book']][$v['job_id']]['domains_primsec']['secondaires'][] = array(
				'domain_id' => $v['domain_id'],
				'domain_name' => $v['domain_name'],
			);
		}
	}
	unset($v,$result);
?>
	<?php
		foreach ($jobs_books as $book => $jobs) {
			$book = (int) $book;
			if ($book === 0) {
				?><h2><?php tr('Métiers non officiels');?></h2><?php
			} elseif ($book === 1) {
				?><h2><?php tr('Métiers du Livre 1 - Univers');?></h2><?php
			}
			unset($book);
		$inc = 0;
			?>
	<div class="row"><?php
			foreach ($jobs as $job_id => $job) { ?>
		<div class="span3">
			<div class="btn-group btnslist">
				<a href="#" data-stepid="<?php echo $job['job_id']; ?>" class="btn jobindicator<?php echo $p_stepval == $job['job_id'] ? ' btn-inverse' : ''; ?>">
					<?php tr($job['job_name']); ?>
				</a>
				<a href="#job<?php echo $job['job_id']; ?>" role="button" data-toggle="modal" class="btn<?php echo $p_stepval == $job['job_id'] ? ' btn-inverse' : ''; ?>"><span class="icon-search"></span></a>
			</div>
			<div id="job<?php echo $job['job_id']; ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><span class="icon-remove"></span></button>
					<h3 class="myModalLabel"><?php tr($job['job_name']); ?></h3>
				</div>
				<div class="modal-body">
					<h4><?php tr("Description"); ?> :</h4>
					<p><?php tr(nl2br($job['job_desc'])); ?></p>
					<p><strong><?php tr("Domaine primaire"); ?></strong> : <?php tr($job['domains_primsec']['primaire']['domain_name']); ?></p>
					<p><strong><?php tr("Domaine(s) secondaire(s)"); ?></strong> : <?php
					if (isset($job['domains_primsec']['secondaires'][0])) {
						$i = 0;
						$count = count($job['domains_primsec']['secondaires']);
						foreach ($job['domains_primsec']['secondaires'] as $v) {
							tr($v['domain_name']);
							if ($i != $count-1) { echo ', '; }
							$i++;
						}
					} else {
						tr('Au choix');
					}
				?></p>
				</div>
				<div class="modal-footer">
					<button class="btn btn-inverse checkthisjob" data-dismiss="modal" data-stepid="<?php echo $job['job_id']; ?>"><?php tr("Choisir ce métier"); ?></button>
					<button class="btn" data-dismiss="modal" aria-hidden="true"><?php tr("Fermer"); ?></button>
				</div>
			</div>
		</div>
		<?php
			$inc++;

			if ($inc % 4 == 0 && $inc > 0) { ?>
	</div><!--/.row-->

	<div class="row">
			<?php }
			}
			?>
	</div><!--/.row--><?php
		} ## end foreach
		unset($jobs_books,$jobs,$job,$i,$v,$count,$inc,$job_id);
		?>
		<div class="span2 otherjob" data-stepid="<?php echo is_string($p_stepval) ? $p_stepval : 'autre'; ?>">
			<h4><input type="text" placeholder="<?php tr('Créer un autre métier'); ?>" value="<?php echo is_string($p_stepval) ? $p_stepval : ''; ?>" id="otherjob" /></h4>
		</div>
	<?php
	buffWrite('css', '
		@media(max-width: 979px) and (min-width: 768px) {
			.btn-group.btnslist { float: left; }
			.btn-group .btn.jobindicator{ font-size: 11px;min-width: 120px; }
			.btn-group .btn{ padding: 3px 10px; }
		}
		@media(min-width: 980px) { .btn-group .btn.jobindicator { min-width: 170px; } }
		.btn.jobindicator { min-width: 80%; color: black; }
		div[class*="span"] .btn-group {
			display: block;
			text-align: center;
		}
		a.btn.btn-inverse {
			color: white;
		}
		a.btn {
			margin: 5px 0;
			-webkit-transition-property: all;
			-moz-transition-property: all;
			-o-transition-property: all;
			transition-property: all;
			-webkit-transition-duration: 400ms;
			-moz-transition-duration: 400ms;
			-o-transition-duration: 400ms;
			transition-duration: 400ms;
		}
		div[class*="span"]:hover { cursor: default; }
		div[class*="span"]:hover h4 { text-shadow: none; }
		', $page_mod);
	buffWrite('js', "
	var values = {};
	\$(document).ready(function() {
		\$('.checkthisjob,.jobindicator').mouseup(function() {
			values.etape = ".$page_step.";
			values['".$page_mod."'] =  \$(this).attr('data-stepid');
			\$('.btn-group .btn-inverse').removeClass('btn-inverse');
			\$('a[href=#job'+$(this).attr('data-stepid')+']').parent().find('.btn').addClass('btn-inverse');
			sendMaj(values, '".$p_action."');
		});
		\$('#otherjob').blur(function(){
			if (\$(this).val()) {
				values.etape = ".$page_step.";
				values['".$page_mod."'] =  \$(this).val();
				\$(this).attr('data-stepid', \$(this).val());
				sendMaj(values, '".$p_action."');
				$('.btn-group .btn-inverse').removeClass('btn-inverse');
			}
		}).keydown(function (e){
		    if(e.keyCode == 13 && \$(this).val()){
				values.etape = ".$page_step.";
				values['".$page_mod."'] = \$(this).val();
				\$(this).attr('data-stepid', \$(this).val());
				sendMaj(values, '".$p_action."');
				\$(this).blur();
				$('.btn-group .btn-inverse').removeClass('btn-inverse');
		    }
		});
	});", $page_mod);
