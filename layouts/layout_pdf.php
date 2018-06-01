<?php

use App\FileAndDir;

unset($filename);
if (isset($_PAGE['file_to_download'])) {

	header('Content-type: application/pdf');

	if (FileAndDir::fexists($_PAGE['file_to_download'])) {
	    ob_clean();
	    ob_end_clean();
	    flush();
		readfile($_PAGE['file_to_download']);
	} else {
		echo 'Erreur';
		echo $_PAGE['file_to_download'];
	}
}
