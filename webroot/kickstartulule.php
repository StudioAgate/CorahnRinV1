<?php
$ulule = $kickstarter = $ulule_dollar = $cours = 0;

$user_agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.72 Safari/537.36';

//Kickstarter
$url = 'http://www.kickstarter.com/projects/1176616619/shadows-of-esteren-a-medieval-horror-rpg-travels';
$content = shell_exec('curl "'.$url.'" -A "'.$user_agent.'"');
$m = preg_match('~Project\[pledged\]">.([0-9\.,]+)<~isUu', $content, $matches);
//var_dump($m, $matches, htmlspecialchars($content));
if (array_key_exists(1, $matches)) { $kickstarter = (int) str_replace(',','', $matches[1]); }

//Ulule
$url = 'http://fr.ulule.com/esteren-voyages/';
$content = shell_exec('curl "'.$url.'" -b "cookie_fr.txt" -A "'.$user_agent.'"');
$m = preg_match('~promised">[^<]*<div class="sum">([^<]+)<~isUu', $content, $matches);
if (array_key_exists(1, $matches)) {
	$str = $matches[1];
	$str = preg_replace('~[^0-9]~isUu', '', $str);
	$ulule = (int) $str;
}

//Cours
$url = 'http://fr.ulule.com/esteren-voyages/';
$content = shell_exec('curl "'.$url.'" -b "cookie_en.txt" -A "'.$user_agent.'"');
$m = preg_match('~promised">[^<]*<div class="sum">([^<]+)<~isUu', $content, $matches);
if (array_key_exists(1, $matches)) {
	$str = $matches[1];
	$str = preg_replace('~[^0-9]~isUu', '', $str);
	$ulule_dollar = (int) $str;
}

$cours = $ulule_dollar / $ulule;

if ($cours === 1) { $cours = 1.3158513871189; $dot = false; } else { $dot = true; }

if ($kickstarter && $ulule) {

	$euro = $ulule + ($kickstarter / $cours);
	$dollar = $kickstarter + ($ulule * $cours);
	$euro = number_format($euro, 0, ',', ' ');
	$dollar = number_format($dollar, 0, '.', ',');
	$ulule = number_format($ulule, 0, '.', ' ');
	$kickstarter = number_format($kickstarter, 0, '.', ',');

	$img = imagecreatefromjpeg('img/little_banner.jpg');

	$x = 560;
	$y = 48;
	$nimg = imagecreatetruecolor($x, $y);
	imagecopyresampled($nimg, $img, 0, 0, 0, 0, $x, $y, $x, $y);

	$black = imagecolorallocate($nimg, 0, 0, 0);
	$grey = imagecolorallocate($nimg, 0x28, 0x28, 0x28);
	$brown = imagecolorallocate($nimg, 0x22, 0x11, 0x4);
	$darkgrey = imagecolorallocate($nimg, 0x14, 0x14, 0x14);
	$white = imagecolorallocate($nimg, 255, 255, 255);

	//Polices de caractère
	$unzialish			= 'css/fonts/UnZialish.ttf';
	$arial				= 'css/fonts/arial.ttf';
	$arial				= 'css/fonts/arial.ttf';
	$carolingia			= 'css/fonts/carolingia.ttf';
	$carolingia_bold	= 'css/fonts/carolingia_old.ttf';
	$lettrine			= 'css/fonts/LettrinEsteren-Regular.ttf';
	$ubuntu				= 'css/fonts/Ubuntu-R_0.ttf';
	$times				= 'css/fonts/times.ttf';

	imagettftext($nimg, 15, 0, 25, 20, $white, $carolingia, 'Ulule');
	imagettftext($nimg, 15, 0, 25, 39, $white, $carolingia, 'Kickstarter');
	imagettftext($nimg, 15, 0, 140, 20, $white, $unzialish, $ulule);
	imagettftext($nimg, 15, 0, 140, 39, $white, $unzialish, $kickstarter);

	imagettftext($nimg, 15, 0, 230, 20, $white, $carolingia, 'Total Euros');
	imagettftext($nimg, 15, 0, 385, 20, $white, $unzialish, $euro);

	imagettftext($nimg, 15, 0, 230, 39, $white, $carolingia, 'Total Dollars');
	imagettftext($nimg, 15, 0, 385, 39, $white, $unzialish, $dollar);
	
	$change_x = (int)($x*0.85);
	$new_img = imagecreatetruecolor($change_x, $y);
	imagecopyresampled($new_img, $nimg, 0, 0, 0, 0, $change_x, $y, $x, $y);
	
	header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
	header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
	header( 'Cache-Control: post-check=0, pre-check=0', false ); 
	header( 'Pragma: no-cache' ); 
	header('Content-type:image/jpg');

	imagejpeg($new_img, null, 100);

	imagedestroy($img);
	imagedestroy($nimg);
	imagedestroy($new_img);
}
