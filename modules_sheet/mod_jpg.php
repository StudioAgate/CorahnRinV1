<?php

use App\FileAndDir;
use App\Session;

$char_name_dest = clean_word($character->get('details_personnage.name'));

$filename = CHAR_EXPORT.DS.$character->id().DS.$char_name_dest.'_original'.$sheet_page.($printer_friendly === true ? '-print' : '').'_'.P_LANG.'.jpg';
if (!FileAndDir::fexists($filename)) {
	$img = $character->export_to_img($sheet_style, $printer_friendly, array($sheet_page));
	$img = (string) @$img[0];
} else {
	$img = $filename;
}
if ($img) {
	if (!FileAndDir::fexists($img)) {
		Session::setFlash('Une erreur est survenue lors de l\'affichage de la feuille de personnage #002', 'error');
		header('Location:'.mkurl(array('val'=>1)));
		exit;
	}
	$_PAGE['layout'] = 'image';
	$_PAGE['content_type'] = 'image/jpeg';
	$_PAGE['file_to_download'] = $img;

} else {
	return;
}
