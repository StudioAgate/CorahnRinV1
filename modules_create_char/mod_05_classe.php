<?php
	$classe = isset($p_stepval['classe']) ? $p_stepval['classe'] : '';
	$dom = isset($p_stepval['dom1']) && isset($p_stepval['dom2']) ? array($p_stepval['dom1'],$p_stepval['dom2']) : array();
?>
	<p class="notif">
		<?php tr("Choisissez ici la classe sociale de laquelle est issu votre personnage"); ?><br />
		<?php tr("Une fois choisie, vous avez la possibilité de choisir <strong>2 domaines</strong> parmi ceux proposés qui bénéficieront d'un bonus de +1 chacun."); ?>
	</p>
	<div class="row">
		<div class="span4<?php echo $classe == 'Paysan' ? ' checked' : ''; ?>" data-stepid="Paysan">
			<h3><?php tr("Paysan"); ?></h3>
			<p><?php tr("Les roturiers font partie de la majorité de la population. Vous avez vécu dans une famille paysanne, à l'écart des villes et cités, sans pour autant les ignorer. Vous êtes plus proche de la nature.<br />les Demorthèn font également partie de cette classe sociale."); ?></p>
			<div class="domainchoice">
				<button class="btn<?php echo $classe == 'Paysan' && in_array(5, $dom) ? ' btn-inverse' : ''?>" data-dom="5"><?php tr("Milieu Naturel"); ?></button>
				<button class="btn<?php echo $classe == 'Paysan' && in_array(8, $dom) ? ' btn-inverse' : ''?>" data-dom="8"><?php tr("Perception"); ?></button>
				<button class="btn<?php echo $classe == 'Paysan' && in_array(10, $dom) ? ' btn-inverse' : ''?>" data-dom="10"><?php tr("Prouesses"); ?></button>
				<button class="btn<?php echo $classe == 'Paysan' && in_array(15, $p_stepval) ? ' btn-inverse' : ''?>" data-dom="15"><?php tr("Voyage"); ?></button>
			</div>
		</div>
		<div class="span4<?php echo $classe == 'Artisan' ? ' checked' : ''; ?>" data-stepid="Artisan">
			<h3><?php tr("Artisan"); ?></h3>
			<p><?php tr("Les roturiers font partie de la majorité de la population. Votre famille était composée d'un ou plusieurs artisans ou ouvriers, participant à la vie communale et familiale usant de ses talents manuels."); ?></p>
			<div class="domainchoice">
				<button class="btn<?php echo $classe == 'Artisan' && in_array(1, $dom) ? ' btn-inverse' : ''?>" data-dom="1"><?php tr("Artisanat"); ?></button>
				<button class="btn<?php echo $classe == 'Artisan' && in_array(16, $dom) ? ' btn-inverse' : ''?>" data-dom="16"><?php tr("Érudition"); ?></button>
				<button class="btn<?php echo $classe == 'Artisan' && in_array(13, $dom) ? ' btn-inverse' : ''?>" data-dom="13"><?php tr("Science"); ?></button>
				<button class="btn<?php echo $classe == 'Artisan' && in_array(11, $dom) ? ' btn-inverse' : ''?>" data-dom="11"><?php tr("Relation"); ?></button>
			</div>
		</div>
		<div class="span4<?php echo $classe == 'Bourgeois' ? ' checked' : ''; ?>" data-stepid="Bourgeois">
			<h3><?php tr("Bourgeois"); ?></h3>
			<p><?php tr("Votre famille a su faire des affaires dans les villes, ou tient probablement un commerce célèbre dans votre région, ce qui vous permet de vivre confortablement au sein d'une communauté familière."); ?></p>
			<div class="domainchoice">
				<button class="btn<?php echo $classe == 'Bourgeois' && in_array(1, $dom) ? ' btn-inverse' : ''?>" data-dom="1"><?php tr("Artisanat"); ?></button>
				<button class="btn<?php echo $classe == 'Bourgeois' && in_array(16, $dom) ? ' btn-inverse' : ''?>" data-dom="16"><?php tr("Érudition"); ?></button>
				<button class="btn<?php echo $classe == 'Bourgeois' && in_array(12, $dom) ? ' btn-inverse' : ''?>" data-dom="12"><?php tr("Représentation"); ?></button>
				<button class="btn<?php echo $classe == 'Bourgeois' && in_array(11, $dom) ? ' btn-inverse' : ''?>" data-dom="11"><?php tr("Relation"); ?></button>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="span4<?php echo $classe == 'Clerge' ? ' checked' : ''; ?>" data-stepid="Clerge">
			<h3><?php tr("Clergé"); ?></h3>
			<p><?php tr("Votre famille a toujours respecté l'Unique et ses représentants, et vous êtes issu d'un milieu très pieux. Vous avez probablement la foi, vous aussi."); ?></p>
			<div class="domainchoice">
				<button class="btn<?php echo $classe == 'Clerge' && in_array(9, $dom) ? ' btn-inverse' : ''?>" data-dom="9"><?php tr("Prières"); ?></button>
				<button class="btn<?php echo $classe == 'Clerge' && in_array(16, $dom) ? ' btn-inverse' : ''?>" data-dom="16"><?php tr("Érudition"); ?></button>
				<button class="btn<?php echo $classe == 'Clerge' && in_array(11, $dom) ? ' btn-inverse' : ''?>" data-dom="11"><?php tr("Relation"); ?></button>
				<button class="btn<?php echo $classe == 'Clerge' && in_array(15, $dom) ? ' btn-inverse' : ''?>" data-dom="15"><?php tr("Voyage"); ?></button>
			</div>
		</div>
		<div class="span4<?php echo $classe == 'Noblesse' ? ' checked' : ''; ?>" data-stepid="Noblesse">
			<h3><?php tr("Noblesse"); ?></h3>
			<p><?php tr("Vous portez peut-être un grand nom des affaires des grandes cités, ou avez grandi en ville. Néanmoins, votre famille est placée assez haut dans la noblesse pour vous permettre d'avoir eu des enseignements particuliers."); ?></p>
			<div class="domainchoice">
				<button class="btn<?php echo $classe == 'Noblesse' && in_array(2, $dom) ? ' btn-inverse' : ''?>" data-dom="2"><?php tr("Combat au contact"); ?></button>
				<button class="btn<?php echo $classe == 'Noblesse' && in_array(16, $dom) ? ' btn-inverse' : ''?>" data-dom="16"><?php tr("Érudition"); ?></button>
				<button class="btn<?php echo $classe == 'Noblesse' && in_array(13, $dom) ? ' btn-inverse' : ''?>" data-dom="13"><?php tr("Science"); ?></button>
				<button class="btn<?php echo $classe == 'Noblesse' && in_array(11, $dom) ? ' btn-inverse' : ''?>" data-dom="11"><?php tr("Relation"); ?></button>
			</div>
		</div>
	</div>
	<?php
	buffWrite('css', '
		#formgen div[class*="span"] h3 {
			padding: 5px;
			text-align: center;
		}
		div[class*="span"]:hover { cursor: pointer; }
		.domainchoice {
			text-align: center;
			max-height: 0;
			-webkit-transition:max-height 400ms;
			-moz-transition:max-height 400ms;
			-ms-transition:max-height 400ms;
			-o-transition:max-height 400ms;
			transition:max-height 400ms;
			overflow:hidden;

		}
		.checked .domainchoice {
			max-height: 80px;
		}
		.domainchoice button {
			margin: 0 2px 10px 2px;
		}
		[class*="span"] p {
			padding: 10px;
		}', $page_mod);
	buffWrite('js', "
		$(document).ready(function() {
			var values = { }, xhr;
			$('div[class*=span]').click(function(e) {
				if (!$(e.target).is('button')) {
					$('button.btn-inverse').removeClass('btn-inverse');
					$('div[class*=span]').removeClass('checked');
					$(this).addClass('checked');
				} else {
					$('div[class*=span]:not(.checked) button').removeClass('btn-inverse');
					$('button.btn-inverse:gt(0)').removeClass('btn-inverse');
					$(e.target).toggleClass('btn-inverse');
				}
				if ($('button.btn-inverse').length == 2) {
					values.etape = ".$page_step.";
					values['".$page_mod."'] = {};
					values['".$page_mod."'].classe = $(this).attr('data-stepid');
					values['".$page_mod."'].dom1 = $('button.btn-inverse').eq(0).attr('data-dom');
					values['".$page_mod."'].dom2 = $('button.btn-inverse').eq(1).attr('data-dom');
					sendMaj(values, '".$p_action."');
				} else {
					values['".$page_mod."'] = '';
					$('#gen_send').attr('href', '#').css('visibility', 'hidden');
					xhr = $.ajax({
                        url : with_lang+'/ajax/aj_genmaj.php',
						type : 'post',
						data : values
					});
				}
			});
		});
	", $page_mod);
