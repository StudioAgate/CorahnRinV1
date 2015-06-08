<?php
$char_name = $character->get('details_personnage.name');
$char_name = clean_word($char_name);

$pdf_filename = CHAR_EXPORT.DS.$character->id().DS.$char_name.'-original'.($printer_friendly === true ? '-print' : '').'_'.P_LANG.'.pdf';
// $pdf_destname = str_replace(ROOT, BASE_URL, $pdf_filename);
// $pdf_destname = str_replace(array('\\', '/'), array('/', '/'), $pdf_destname);
// $pdf_destname = str_replace('webroot'.DS, '', $pdf_destname);
if (!file_exists($pdf_filename)) {

    /** @var tFPDF $pdf_file */
	$pdf_file = $character->export_to_pdf($sheet_style, $printer_friendly);
	$pdf_file->Output($pdf_filename);
}
// 	if (url_exists($pdf_destname)) {
// 		$_PAGE['layout'] = 'default';
// 		echo '<iframe src="'.$pdf_destname.'" style="width:100%;margin: 0 auto;padding:0;border: none; height:6740px;"></iframe>';
// 		return;
// 	}

$_PAGE['layout'] = 'image';
$_PAGE['content_type'] = 'application/pdf';
$_PAGE['file_to_download'] = $pdf_filename;
