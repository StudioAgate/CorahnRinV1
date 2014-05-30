<div class="container"><?php

$lang = isset($_PAGE['request'][0]) ? $_PAGE['request'][0] : (isset($_GET['lang']) ? $_GET['lang'] : false);

if ($lang === false) {
	tr('La langue n\'a pas été correctement sélectionnée.');
	echo '<br />'.mkurl(array('val'=>1, 'type' => 'tag', 'anchor' => 'Retourner à l\'accueil', 'attr' => 'class="btn btn-danger" style="color:white;"'));
} elseif ($lang == 'en') {
	$_SESSION['lang'] = 'en';
} elseif ($lang == 'fr') {
	$_SESSION['lang'] = 'fr';
}

if (isset($_PAGE['referer']['full_url']) && $_PAGE['referer']['full_url'] && $_PAGE['referer']['full_url'] != mkurl(array('val'=>$_PAGE['id']))) {
	header('Location:'.$_PAGE['referer']['full_url']);
	exit;
} else {
	header('Location:'.mkurl(array('val'=>1)));
	exit;
}
?></div>