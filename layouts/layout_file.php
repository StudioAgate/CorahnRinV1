<?php

use App\FileAndDir;

unset($filename);

if (isset($_PAGE['file_to_download']) && FileAndDir::fexists($_PAGE['file_to_download'])) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($_PAGE['file_to_download']));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($_PAGE['file_to_download']));
    ob_clean();
    ob_end_clean();
    flush();
    readfile($_PAGE['file_to_download']);
}
