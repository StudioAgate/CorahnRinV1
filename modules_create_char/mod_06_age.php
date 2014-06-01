<?php

	$slidevalue = $p_stepval ? $p_stepval : 16;

?>
	<div class="row">
		<div class="span">
			<h3><?php tr("Âge <small>(Min 16, max 35)</small>"); ?></h3>
			<span id="age_val" class="btn btn-inverse disabled"></span>
			<div id="age_slider"></div>
		</div>
	</div>
	<script type="text/javascript">var slidevalue = <?php echo (int) $slidevalue; ?>;</script>
	<?php
	buffWrite('css', '
		#formgen div.row div h3,
		#formgen div.row div a.ib,
		#formgen div.row div span {
			display: inline-block;
			vertical-align: middle;
		}
		#formgen div.row div h3{
		width: 270px;
			padding-left: 10px;
		}', $page_mod);
	buffWrite('js', "
	$(document).ready(function() {
		var values = { }, xhr;
		$('#age_slider').slider({
			range: 'min',
			value: slidevalue,
			min: 16,
			max: 35,
			slide: function( event, ui ) {
				$('#age_val').text(ui.value);
			}
		});
		$('#age_slider').mousedown(function() {
			$('#gen_send').html('<img src=\"".BASE_URL."/img/ajax-loader.gif\" />').css('visibility', 'visible');
		});
		$('#age_slider').mouseup(function(){
			values.etape = ".$page_step.";
			values['".$page_mod."'] = $('#age_val').text();
			sendMaj(values, '".$p_action."');
		});
		$('#age_val').text($('#age_slider').slider('value'));
	});
	", $page_mod);
