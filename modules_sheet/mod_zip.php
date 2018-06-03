<?php

use App\FileAndDir;
use App\Session;

$char_name = clean_word($character->get('details_personnage.name'));

$charOutputDirectory = CHAR_EXPORT.DS.$char_id;

$zip_dest_name = $charOutputDirectory.DS.$char_name.'_'.P_LANG.'.zip';

$files_to_check = array(
	$charOutputDirectory.DS.$char_name.'_original1-print_'.P_LANG.'.jpg',
	$charOutputDirectory.DS.$char_name.'_original2-print_'.P_LANG.'.jpg',
	$charOutputDirectory.DS.$char_name.'_original3-print_'.P_LANG.'.jpg',
	$charOutputDirectory.DS.$char_name.'_original1_'.P_LANG.'.jpg',
	$charOutputDirectory.DS.$char_name.'_original2_'.P_LANG.'.jpg',
	$charOutputDirectory.DS.$char_name.'_original3_'.P_LANG.'.jpg',
	$pdfNonPrintFriendly = CHAR_EXPORT.DS.$char_id.DS.$char_name.'-original_'.P_LANG.'.pdf',
	$pdfPrintFriendly = CHAR_EXPORT.DS.$char_id.DS.$char_name.'-original-print_'.P_LANG.'.pdf',
);

$imgs = array();

if (!FileAndDir::fexists($zip_dest_name)) {
	$generate_all = false;
	foreach ($files_to_check as $file) {
		if (!FileAndDir::fexists($file)) {//Si l'un des éléments n'existe pas, on va générer le tout pour remettre à jour le cache personnages du site
			$generate_all = true;
			break;
		}
	}
    $generate_all = true;

	if ($generate_all === true) {//Export de toutes les feuilles de personnage si les fichiers n'existent pas
		$imgs_norm = $character->export_to_img('original', false);//Export des jpg
		$imgs_print = $character->export_to_img('original', true);//Export des jpg

		$pdf_norm = $character->export_to_pdf('original', false);//Création du pdf
        if (!$pdf_norm) {
            throw new RuntimeException('Failed to create non-printer friendly PDF');
        }
		$pdf_norm->Output($pdfNonPrintFriendly, 'F');//Export du pdf

		$pdf_norm = $character->export_to_pdf('original', true);//Création du pdf
        if (!$pdf_norm) {
            throw new RuntimeException('Failed to create non-printer friendly PDF');
        }
		$pdf_norm->Output($pdfPrintFriendly, 'F');//Export du pdf

        $finalFiles = array_merge($imgs_norm, $imgs_print, [$pdfNonPrintFriendly, $pdfPrintFriendly]);

		foreach ($finalFiles as $v) {
			if (!FileAndDir::fexists($v)) {
				Session::setFlash('Une erreur est survenue lors de l\'affichage de la feuille de personnage/'.$v, 'error');
				return;
			}
		}

		$create_zip = create_zip($finalFiles, $zip_dest_name, array_map('basename', $finalFiles), true, true);

		if (false === $create_zip) {
            throw new RuntimeException('Failed to create the ZIP file');
        }
	} else {
		$create_zip = true;
	}
} else {
	$create_zip = true;
}

FileAndDir::createPath(CHAR_EXPORT.DS.$char_id);

if ($create_zip === true) {
	$_PAGE['layout'] = 'file';
	$_PAGE['file_to_download'] = $zip_dest_name;
	return;
}
