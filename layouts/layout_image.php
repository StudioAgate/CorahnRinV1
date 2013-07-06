<?php
unset($filename);
if (isset($_PAGE['file_to_download'])) {
	$_PAGE['content_type'] = isset($_PAGE['content_type']) ? $_PAGE['content_type'] : 'image/jpeg';

	$valid_types = array(
		'image/jpeg'=>1,
		'image/png'=>1,
		'image/gif'=>1,
		'application/pdf'=>1,
	);
	if (!isset($valid_types[$_PAGE['content_type']])) {
		$_PAGE['content_type'] = 'text/html';
	}
	header('Content-type: '.$_PAGE['content_type']);

	if (FileAndDir::fexists($_PAGE['file_to_download'])) {
	    ob_clean();
	    ob_end_clean();
	    flush();
		readfile($_PAGE['file_to_download']);
	} else {
		echo 'Erreur';
		echo $_PAGE['file_to_download'];
	}
	exit;
}