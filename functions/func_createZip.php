<?php

/**
 * Fonction de gestion zip récupérée sur http://php.net/
 */
function create_zip(array $files, $destination, array $destination_names = array(), $overwrite = true, $debug = false)
{
	if(!$overwrite && file_exists($destination)) {
	    // If file exists and we don't need to recreate it, it's ok
		return true;
	}

	$valid_files = $invalid_files = array();//vars

	if(is_array($files)) {//if files were passed in...
		foreach($files as $i => $file) {//cycle through each file
			if(file_exists($file)) {//make sure the file exists
				$valid_files[$i] = $file;
			} else {
				$invalid_files[] = $file;
			}
		}
	}

	if(count($valid_files)) {//if we have good files...
		$zip = new ZipArchive();//create the archive
		if(true !== $zipReturn = $zip->open($destination,ZIPARCHIVE::OVERWRITE | ZIPARCHIVE::CREATE)) {//try opening zip file
		    throw new \RuntimeException('Zip error: '.zipErrorMessage($zipReturn));
		}

		foreach($valid_files as $i => $file) {
			if (isset($destination_names[$i])) {
				$path = $destination_names[$i];
			} else {
				$path = pathinfo($file);
				$path = $path['basename'];
			}
			$zip->addFile($file,$path);//add the files
		}

		//debug
		if ($debug === true && P_LOGGED === true) { echo 'L\'archive contient ',$zip->numFiles,' fichiers avec ce statut : ',$zip->status; }

		$zip->close();//close the zip -- done!

		if (P_LOGGED === true && $debug === true && count($invalid_files)) {
			echo 'Fichiers invalides : ';
			foreach($invalid_files as $file) {
				echo '<br />'.$file;
			}
		}

		return file_exists($destination);//check to make sure the file exists
	}

    if ($debug === true && P_LOGGED === true) {
        echo 'Aucun fichier correct';
    }

    return false;
}

function zipErrorMessage($code)
{
    switch (true)
    {
        case $code === 0:
            return 'No error';
        case $code === 1:
            return 'Multi-disk zip archives not supported';
        case $code === 2:
            return 'Renaming temporary file failed';
        case $code === 3:
            return 'Closing zip archive failed';
        case $code === 4:
            return 'Seek error';
        case $code === 5:
            return 'Read error';
        case $code === 6:
            return 'Write error';
        case $code === 7:
            return 'CRC error';
        case $code === 8:
            return 'Containing zip archive was closed';
        case $code === 9:
            return 'No such file';
        case $code === 10:
            return 'File already exists';
        case $code === 11:
            return 'Can\'t open file';
        case $code === 12:
            return 'Failure to create temporary file';
        case $code === 13:
            return 'Zlib error';
        case $code === 14:
            return 'Malloc failure';
        case $code === 15:
            return 'Entry has been changed';
        case $code === 16:
            return 'Compression method not supported';
        case $code === 17:
            return 'Premature EOF';
        case $code === 18:
            return 'Invalid argument';
        case $code === 19:
            return 'Not a zip archive';
        case $code === 20:
            return 'Internal error';
        case $code === 21:
            return 'Zip archive inconsistent';
        case $code === 22:
            return 'Can\'t remove file';
        case $code === 23:
            return 'Entry has been deleted';
        default:
            return 'An unknown error has occurred('.$code.')';
    }
}
