<?php
if (isset($_POST['propos']) && isset($_POST['trad']) && !empty($_POST['propos']) && !empty($_POST['trad'])) {

	Translate::write_propos_en($_POST['propos'], $_POST['trad']);

} elseif (isset($_POST['maj_propos']) && isset($_POST['maj_trad']) && !empty($_POST['maj_propos']) && !empty($_POST['maj_trad'])) {

	Translate::write_words_en($_POST['maj_propos'], $_POST['maj_trad']);

}