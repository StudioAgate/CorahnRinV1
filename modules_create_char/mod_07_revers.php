<?php

	$output = '';
	$t = $db->req('SELECT %rev_id,%rev_name,%rev_desc FROM %%revers ORDER BY %rev_id ASC');
	$revers = array();
	foreach ($t as $k => $v) {
		$revers[$v['rev_id']] = $v;
	}
	unset($revers[1], $t, $k, $v);

	$age = isset($_SESSION[$steps[6]['mod']]) ? $_SESSION[$steps[6]['mod']] : false;
	if ($age === false) {
		tr('L\'âge n\'a pas été défini, merci de vous rendre à l\'étape correspondante.');
		echo '<br />';
		mkurl(array('params'=>6, 'type' => 'tag', 'anchor' => 'Aller à la page des Voies', 'attr' => 'class="btn"'));
		return;
	}

    $nb_revers = 0;
    if ($age > 20) { $nb_revers ++; }
    if ($age > 25) { $nb_revers ++; }
    if ($age > 30) { $nb_revers ++; }

    $choose_manually = null;

	if ($nb_revers === 0) {
        $output = tr('Aucun revers (moins de 21 ans)', true);
        $dice = array(0=>0);
    } else {
		if (!$p_stepval) {
            if (isset($_GET['manually'])) {
                if ($_GET['manually'] === 'false') {
                    // Calcul automatique
                    $dice = array();
                    $dice[0] = rand(1,10);
                    if ($dice[0] == 10) {
                        $dice[1] = rand(2, 9);
                    } elseif ($dice[0] == 1) {
                        $output .= tr("<strong>Poisse</strong> ! Un revers supplémentaire !", true).'<br />';
                        $dice[0] = rand(2, 9);
                        $dice[1] = rand(2, 9);
                        do {
                            $dice[1] = rand(2,9);
                        } while ($dice[1] == $dice[0]);
                    }

                    if ($age >= 26) {
                        do {
                            $act = rand(2,9);
                        } while (in_array($act, $dice));
                        $dice[2] = $act;
                    }
                    if ($age >= 31) {
                        do {
                            $act = rand(2,9);
                        } while (in_array($act, $dice));
                        $dice[3] = $act;
                    }
                } elseif ($_GET['manually'] !== 'true') {
                    // Paramètre invalide
                    $choose_manually = true;
                    redirect(mkurl(array('params'=>$steps[$page_step]['mod'])), tr('Paramètre de choix de revers invalide.', true), 'error');
                }
            }

            if (!empty($_POST)) {
                if (isset($_POST['setbacks'])) {
                    // Validation des données POST
                    // Redirection événtuelle
                    if (count($_POST['setbacks']) === $nb_revers) {
                        $dice = array();
                        foreach ($_POST['setbacks'] as $setback) {
                            if (!isset($revers[$setback])) {
                                // Erreur
                                redirect(mkurl(array('params'=>$steps[$page_step]['mod'])), tr('Indiquez des revers corrects.', true), 'error');
                            } else {
                                $dice[$setback] = $setback;
                            }
                        }
                    } else {
                        // Message d'erreur sur la quantité de revers choisis
                        redirect(mkurl(array(
                            'params'=>$steps[$page_step]['mod'])),
                            tr(
                                'Vous devez choisir %nb_revers% revers, et vous en avez choisi %selected%, veuillez corriger votre choix.',
                                true,
                                array('%nb_revers%' => $nb_revers, '%selected%' => count($_POST['setbacks'])
                            )
                        ), 'warning');
                    }
                } else {
                    // Erreur dans le formulaire
                    redirect(mkurl(array('params'=>$steps[$page_step]['mod'])), tr('Erreur dans le formulaire.', true), 'error');
                }
            }
		} else {
			$dice = $p_stepval;
		}

        if (isset($dice) && !empty($dice)) {
            if (isset($dice[0]) && $dice[0] == 0) {
                $output = tr('Aucun revers (moins de 21 ans)', true);
            } elseif (isset($dice[0]) && $dice[0] > 0) {
                foreach ($dice as $val) {
                    if ($val) {
                        $output .= '<strong>'.tr($revers[$val]['rev_name'], true).'</strong>, '.tr($revers[$val]['rev_desc'], true).'<br />';
                    }
                }
            }
        }

	}
	if ($nb_revers === 0 || $age < 21) {
        $dice = array(0=>0);
		$_SESSION[$page_mod] = array(0=>0);
		if (!$p_stepval && P_DEBUG === false) {
			$_SESSION['etape']++;
            redirect(mkurl(array('params'=>$steps[$page_step+1]['mod'])));
		}
	}

	if (empty($p_stepval) && isset($dice)) {
		Session::write($page_mod, $dice);
		$_SESSION['etape'] = $page_step+1;
		$p_stepval = $dice;
	}

	?>
	<div class="row">
		<div class="span" id="revers">
		<?php
		if ($p_stepval) {
            if (isset($dice[0])) {
                ?>
                <p><?php tr('Aucun revers (moins de 21 ans)'); ?></p>
                <?php
            } else {
                foreach ($p_stepval as $setback) {
                    ?>
                    <p>
                        <strong><?php tr($revers[$setback]['rev_name']); ?></strong> <?php tr($revers[$setback]['rev_desc']); ?>
                    </p>
                    <?php
                }
            }
		} else {
            if ($choose_manually === null || $choose_manually === true) { ?>
                <p class="alert alert-info">
                    <?php tr('Votre personnage a <strong>%age%</strong> ans, il doit donc tirer <strong>%nb_revers%</strong> revers.', false, array('%age%' => $age, '%nb_revers%' => $nb_revers)); ?><br />
                    <?php tr('Vous pouvez choisir de les tirer aléatoirement, ou alors les choisir vous-même.'); ?>
                </p>
                <p class="alert alert-warning">
                    <?php tr('Attention ! Si vous choisissez manuellement vos revers, vous ne pourrez PAS choisir d\'échapper à un revers, ni subir un revers supplémentaire.'); ?>
                </p>
                <p>
                    <a class="btn btn-primary" href="<?php echo mkurl(array('params'=>$page_mod,'get'=>array('manually'=>'false'))); ?>"><?php tr('Tirer aléatoirement'); ?></a>
                    <button id="choose_manually" type="button" class="btn btn-info"><?php tr('Choisir manuellement'); ?></button>
                </p>
                <form id="setbacks_list" action="<?php echo mkurl(array('params'=>$page_mod,'get'=>array('manually'=>'true'))); ?>" <?php if ($choose_manually === null) { ?>style="display: none;"<?php } ?> method="post">
                    <?php foreach($revers as $id => $v) {
                        if ($id !== 1 && $id !== 10) { ?>
                        <label for="revers_<?php echo $id; ?>" class="checkbox">
                            <input id="revers_<?php echo $id; ?>" name="setbacks[<?php echo $id; ?>]" type="checkbox" value="<?php echo $id; ?>" class="input_setback_manual" />
                            <strong><?php tr($v['rev_name']); ?></strong>
                            <?php tr($v['rev_desc']); ?>
                        </label>
                        <?php }
                    } ?>
                    <input type="hidden" id="nb_revers" value="<?php echo $nb_revers; ?>" />
                    <p class="well well-small"><?php tr('Reste %remaining% revers à choisir.', false, array('%remaining%' => '<strong id="remaining_setbacks">'.$nb_revers.'</strong>')); ?></p>
                    <button id="send_setbacks" type="submit" class="btn btn-success" style="display: none;"><?php tr('Envoyer'); ?></button>
                </form><?php
            }
		}
		unset($dice);
		?><p id="generated"><?php
			if (!$p_stepval) { echo $output; }//Ce qui est affiché après avoir appuyé sur le bouton
			unset($output);
		?></p>
		</div>
	</div>

	<?php
// 	unset($output, $k);
echo "<style type=\"text/css\">#gen_send{visibility:visible;}</style>";
buffWrite('css', '
	#formgen p#generated { display: none; }
	#formgen div[class*="span"]:hover { cursor: default; }
', $page_mod);
buffWrite('js', "
	$(document).ready(function(){
	    var nb_revers_max = parseInt($('#nb_revers').val());
	    $('.input_setback_manual').on('change', function(){
	        var nb_revers = $('.input_setback_manual:checked').length;
	        if (nb_revers > nb_revers_max) {
	            this.checked = false;
	            nb_revers--;
                \$(this).removeAttr('checked').prop('checked', false);
	        }
	        if (nb_revers == nb_revers_max) {
                $('#send_setbacks').slideDown(400);
	        } else {
	            $('#send_setbacks').slideUp(400);
	        }
	        document.getElementById('remaining_setbacks').innerHTML = nb_revers_max - nb_revers;
	    });
	    $('#choose_manually').on('click', function(){
	        $('#setbacks_list').slideDown(400);
	    });
	});
", $page_mod);
