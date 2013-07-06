
	<div class="row">
		<div class="span5<?php echo $p_stepval == 'Tri-Kazel' ? ' checked' : ''; ?>" id="Tri-Kazel">
			<h3><?php tr('Peuple de Tri-Kazel'); ?></h3>
			<p><span><?php tr('Les Tri-Kazeliens constituent la très grande majorité de la population de la péninsule. La plupart d\'entre eux conservent une stature assez robuste héritée des Osags mais peuvent aussi avoir des traits d\'autres peuples. Les Tri-Kazeliens sont issus de siècles de mélanges entre toutes les cultures ayant un jour ou l\'autre foulé le sol de la péninsule.<br /><br />De par cette origine, le PJ connaît un dialecte local ; il faut donc préciser de quel pays et région il est originaire.'); ?></span></p>
		</div>
		<div class="span5<?php echo $p_stepval == 'Tarish' ? ' checked' : ''; ?>" id="Tarish">
			<h3><?php tr('Peuple Tarish'); ?></h3>
			<p><span>
				<?php tr("D'origine inconnue, le peuple Tarish forme une minorité nomade qui parcourt depuis des décennies les terres de la péninsule. Il est aussi appelé \"peuple de l'ouest\" car la légende veut qu'il soit arrivé par l'Océan Furieux. Les Tarishs se distinguent des Tri-Kazeliens par des pommettes hautes, le nez plutôt aquilin et les yeux souvent clairs. Beaucoup d'entre eux deviennent des saltimbanques, des mystiques ou des artisans.<br />La culture Tarish, même si elle est diluée aujourd'hui, conserve encore une base importante : c'est un peuple nomade habitué aux longs périples et leur langue n'a pas disparu, bien qu'aucun étranger ne l'ait jamais apprise."); ?>
			</span></p>
		</div>
		<div class="span5<?php echo $p_stepval == 'Osag' ? ' checked' : ''; ?>" id="Osag">
			<h3><?php tr("Peuple Osag"); ?></h3>
			<p><span>
				<?php tr("Habitués à ne compter que sur eux-mêmes, les Osags forment un peuple rude. Généralement dotés d'une carrure imposante, ils sont les descendants directs des clans traditionnels de la péninsule. La civilisation péninsulaire a beaucoup évolué depuis l'avènement des Trois Royaumes, mais certains clans sont restés fidèles aux traditions ancestrales et n'ont pas pris part à ces changements. Repliés sur leur mode de vie clanique, les Osags ne se sont pas métissés avec les autres peuples et ont gardé de nombreuses caractéristiques de leurs ancêtres. Les Osags font de grands guerriers et comptent parmi eux les plus célèbres Demorthèn.<br /><br />Leur langue a elle aussi survécu au passage des siècles. Les mots \"feondas\", \"C'maogh\", \"Dàmàthair\" - pour ne citer qu'eux - viennent tous de ce que les Tri-Kazeliens nomment la langue ancienne, mais qui est toujours utilisée par les Osags."); ?>
			</span></p>
		</div>
		<div class="span5<?php echo $p_stepval == 'Continent' ? ' checked' : ''; ?>" id="Continent">
			<h3><?php tr("Peuple du Continent"); ?></h3>
			<p><span>
				<?php tr("Les hommes et les femmes du Continent sont souvent plus minces et plus élancés que les natifs de Tri-Kazel. Leur visage aura tendance à être plus fin mais avec des traits parfois taillés à la serpe. Un PJ choisissant ce peuple ne sera pas natif du Continent, mais plutôt le descendant direct d'au moins un parent Continental. Si les origines Continentales du PJ sont davantage diluées, on estime qu'il fait partie du peuple de Tri-Kazel.<br /><br />En fonction du passé de la famille du PJ et de son niveau d'intégration dans la société tri-kazelienne, il pourrait avoir appris leur langue d'origine Continentale ou bien un patois de la péninsule, au choix du PJ."); ?>
			</span></p>
		</div>
	</div><!--/.row-->
	<?php
	buffWrite('css', <<<CSSFILE
		div[class*="span"]:hover { cursor: pointer; }
		#formgen div[class*="span"] h3 {
			padding: 10px;
			text-align: center;
		}
		#formgen div[class*="span"] p span {
			padding: 10px;
			display: block;
		}
		#formgen div[class*="span"] p {
			display: block;
			width: inherit;
		}
CSSFILE
, $page_mod);
	buffWrite('js', <<<JSFILE
		$(document).ready(function() {
			var values = { }, xhr;
			$('div[class*=span]').click(function() {
				$('div[class*=span]').removeClass('checked');
				$(this).addClass('checked');
				values.etape = {$page_step};
				values['{$page_mod}'] = $(this).attr('id');
				sendMaj(values, '{$p_action}');
			});
		});
JSFILE
, $page_mod);
