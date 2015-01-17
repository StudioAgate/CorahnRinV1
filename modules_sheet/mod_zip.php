<?php

$char_name = $character->get('details_personnage.name');
$char_name = clean_word($char_name);
$zip_dest_name = CHAR_EXPORT.DS.$char_id.DS.$char_name.'_'.P_LANG.'.zip';

$files_to_load = array(
	CHAR_EXPORT.DS.$character->id().DS.$char_name.'_original1-print_'.P_LANG.'.jpg'	=> $char_name.'-original-page1-print_'.P_LANG.'.jpg',
	CHAR_EXPORT.DS.$character->id().DS.$char_name.'_original2-print_'.P_LANG.'.jpg'	=> $char_name.'-original-page2-print_'.P_LANG.'.jpg',
	CHAR_EXPORT.DS.$character->id().DS.$char_name.'_original3-print_'.P_LANG.'.jpg'	=> $char_name.'-original-page3-print_'.P_LANG.'.jpg',
	CHAR_EXPORT.DS.$character->id().DS.$char_name.'_original1_'.P_LANG.'.jpg'			=> $char_name.'-original-page1_'.P_LANG.'.jpg',
	CHAR_EXPORT.DS.$character->id().DS.$char_name.'_original2_'.P_LANG.'.jpg'			=> $char_name.'-original-page2_'.P_LANG.'.jpg',
	CHAR_EXPORT.DS.$character->id().DS.$char_name.'_original3_'.P_LANG.'.jpg'			=> $char_name.'-original-page3_'.P_LANG.'.jpg',
	CHAR_EXPORT.DS.$character->id().DS.$char_name.'-original_'.P_LANG.'.pdf'			=> $char_name.'-original_'.P_LANG.'.pdf',
	CHAR_EXPORT.DS.$character->id().DS.$char_name.'-original-print_'.P_LANG.'.pdf'		=> $char_name.'-original-print_'.P_LANG.'.pdf',
);

$imgs = array();

if (!FileAndDir::fexists($zip_dest_name)) {
	$generate_all = false;
	foreach ($files_to_load as $file => $name) {
		if (!FileAndDir::fexists($file)) {//Si l'un des éléments n'existe pas, on va générer le tout pour remettre à jour le cache personnages du site
			$generate_all = true;
			break;
		}
	}

	if ($generate_all === true) {//Export de toutes les feuilles de personnage si les fichiers n'existent pas
		$imgs_norm = $character->export_to_img('original', false);//Export des jpg
		$imgs_print = $character->export_to_img('original', true);//Export des jpg

		$pdf_names = array(
			CHAR_EXPORT.DS.$character->id().DS.$char_name.'-original.pdf',
			CHAR_EXPORT.DS.$character->id().DS.$char_name.'-original-print.pdf',
		);
		$pdf_norm = $character->export_to_pdf('original', false);//Création du pdf
		$pdf_norm->Output($pdf_names[0]);//Export du pdf

		$pdf_norm = $character->export_to_pdf('original', true);//Création du pdf
		$pdf_norm->Output($pdf_names[1]);//Export du pdf

		$imgs = array_merge($imgs_print, $imgs_norm, $pdf_names);//Fusion de tous les tableaux

		foreach ($imgs as $k => $v) {
			unset($imgs[$k]);
			if (isset($files_to_load[$v])) {
				$imgs[$v] = $files_to_load[$v];
			}
		}
		foreach ($imgs as $v => $k) {
			if (!FileAndDir::fexists($v)) {
				Session::setFlash('Une erreur est survenue lors de l\'affichage de la feuille de personnage/'.$v, 'error');
				return;
			}
		}
		$create_zip = create_zip(array_keys($imgs), $zip_dest_name, array_values($imgs), true, true);
	} else {
		$imgs = $files_to_load;
		$create_zip = true;
	}
} else {
	$imgs = $files_to_load;
	$create_zip = true;
}

FileAndDir::createPath(CHAR_EXPORT.DS.$char_id);

if ($create_zip === true) {
	$_PAGE['layout'] = 'file';
	$_PAGE['file_to_download'] = $zip_dest_name;
	return;
}
