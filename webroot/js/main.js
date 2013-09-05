const base_url = corahn_rin;
/**
 * Efface la sélection actuelle sur la page
 * 
 * @author Pierstoval
 */
function clearSelection() {
	if (document.selection) {
		document.selection.empty();
	} else if (window.getSelection) {
		window.getSelection().removeAllRanges();
	}
}

//$(document).ready(function(){ console.clear(); });

/**
 * Envoie les informations du formulaire d'une étape à la session
 * 
 * @param values Un tableau de données à transférer
 * @param action La destination de la page, sera le lien dans la balise de l'étape suivante
 * @param empty Si true, on envoie des données vides à la page pour annuler l'effet du formulaire
 * @param show_msg Si true, on affiche le résultat de la requête dans la balise id="err"
 * @author Pierstoval
 */
function sendMaj(values, action, empty, show_msg) {
	if (empty !== true) {
		$('#gen_send').html('<img src=\"'+base_url+'/img/ajax-loader.gif\" />').css('visibility', 'visible');
	} else {
		values['empty'] = '1';
		$('#gen_send').attr('href', '#').html('').css('visibility', 'hidden');
	}
	if (empty !== true) {  }
	xhr = $.ajax({
		url : base_url+'/ajax/aj_genmaj.php',
		type : 'post',
		data : values,
		success : function(msg) {
			if (empty !== true) {
				$('#gen_send').delay(1).attr('href', action).html(nextsteptranslate).css('visibility', 'visible');
			} else {
				$('#gen_send').delay(1).attr('href', '#').html(nextsteptranslate).css('visibility', 'hidden');
			}
			if (show_msg === true) {
				$('#err').html(msg).show();
			}
		}
	});
}

$(document).ready(function(){
	var ky = [];
	var ko = '38,38,40,40,37,39,37,39,66,65';
	var txt = 'Just decode the binary string in the source code of this page !';
	$(document).keydown(function(e) {
			ky.push(e.keyCode);
			if (ky.toString().indexOf(ko) >= 0){
				alert(txt);
				ky = [];
			}
		}
	);

	$('button.showhidden').click(function(){
		$(this).next('.hid').slideToggle(400);
	});
	
	$('#versions li.version h4').hover(function(){
		$(this).find('span[class*=icon-]').addClass('icon-red');
	}, function(){
		$(this).find('span[class*=icon-]').removeClass('icon-red');
	});
	$('#versions li.version h4').click(function(e){
		if (!$(e.target).is('a')) {
			$(this).parents('li.version').find('span[class*=icon-]').attr('class', function(){return $(this).is('.icon-plus') ? 'icon-minus' : 'icon-plus';});
			$(this).parents('li.version').find('.taskslist').stop().slideToggle(400);
		}
	});
});