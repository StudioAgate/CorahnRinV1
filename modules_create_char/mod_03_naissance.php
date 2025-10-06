<?php
	$result = $db->req('SELECT * FROM %%regions ORDER BY %region_id ASC');
	$p_regions = [];
	if ($result) {
		foreach($result as $data) {
			$p_regions[$data['region_id']] = $data;
		}
	}
	unset($data);

?>
		<div id="infobulle"></div>
		<?php
			echo '<div id="TaolKaer"><svg version="1.1" xmlns="http://www.w3.org/2000/svg">';
			foreach ($p_regions as $id => $val) {
				if ($val['region_kingdom'] == 'Taol-Kaer') {
					?><polygon points="<?php echo $val['region_htmlmap']; ?>" title="<?php tr($val['region_name']); ?>" data-naissid="<?php echo $id; ?>" <?php echo $p_stepval == $id ? ' class="checked"' : ''; ?> /><?php
				}//endif
			}//endforeach
			echo '</svg></div>';

			echo '<div id="Gwidre"><svg version="1.1" xmlns="http://www.w3.org/2000/svg">';
			foreach ($p_regions as $id => $val) {
				if ($val['region_kingdom'] == 'Gwidre') {
					?><polygon points="<?php echo $val['region_htmlmap']; ?>" title="<?php tr($val['region_name']); ?>" data-naissid="<?php echo $id; ?>" <?php echo $p_stepval == $id ? ' class="checked"' : ''; ?> /><?php
				}//endif
			}//endforeach
			echo '</svg></div>';

			echo '<div id="Reizh"><svg version="1.1" xmlns="http://www.w3.org/2000/svg">';
			foreach ($p_regions as $id => $val) {
				if ($val['region_kingdom'] == 'Reizh') {
					?><polygon points="<?php echo $val['region_htmlmap']; ?>" title="<?php tr($val['region_name']); ?>" data-naissid="<?php echo $id; ?>" <?php echo $p_stepval == $id ? ' class="checked"' : ''; ?> /><?php
				}//endif
			}//endforeach
			echo '</svg></div>';

		?>
	<?php
	buffWrite('css', /** @lang CSS */ '
		#formgen #Gwidre, #formgen #Reizh, #formgen #TaolKaer {
			margin: 0 auto;
			border: solid 1px #000;
		}
		#formgen #Gwidre, #formgen #Gwidre svg {
			width: 1000px; height: 648px;
		}
		#formgen #Gwidre {
			background: url(\''.base_url().'/img/carte_Gwidre.jpg\');
		}
		#formgen #Reizh, #formgen #Reizh svg {
			width: 1000px; height: 838px;
		}
		#formgen #Reizh {
			background: url(\''.base_url().'/img/carte_Reizh.jpg\');
		}
		#formgen #TaolKaer, #formgen #TaolKaer svg {
			width: 1000px; height: 594px;
		}
		#formgen #TaolKaer {
			background: url(\''.base_url().'/img/carte_Taol-Kaer.jpg\');
		}
		#formgen svg {
			position: absolute;
			z-index: 1;
		}
		#formgen polygon {
			fill: white;
			opacity: 0.3;
			transition-property:opacity;
			-moz-transition-property:opacity;
			-o-transition-property:opacity;
			-webkit-transition-property:opacity;
			transition-duration: 600ms;
			-moz-transition-duration: 600ms;
			-o-transition-duration: 600ms;
			-webkit-transition-duration: 600ms;
		}
		#formgen polygon:hover,
		#formgen polygon.checked {
			opacity: 0.0;
			cursor:pointer;
		}
	', $page_mod);
	buffWrite('js', /** @lang JavaScript */ "
		$(document).ready(function() {
			var values = { }, checked = 0, xhr;
			$('polygon').click(function() {
				$('polygon').attr('class', '');
				$(this).attr('class', 'checked');
				values.etape = ".$page_step.";
				values['".$page_mod."'] = $(this).attr('data-naissid');
				sendMaj(values, '".$p_action."');
			});
			$('#infobulle').hide().text('');
			$('#formgen polygon').mouseenter(function() {
				var thisone = $(this), xs = 0, ys = 0;
				checked = thisone.attr('id');
				xs = thisone.offset().left + this.getBBox().width / 2;
				ys = thisone.offset().top + this.getBBox().height / 2;
				if ($('#infobulle').is(':animated')) {
					$('#infobulle').stop();
				}
				$('#infobulle').show().text(thisone.attr('title')).animate({
					top : ys - $('#infobulle').height() / 2,
					left : xs - $('#infobulle').width() / 2
				});
			});
		});
	", $page_mod);
