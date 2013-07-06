<?php
/**
 * Fonction de gestion zip récupérée sur http://php.net/
 *
 * @param array $files Contient la liste des fichiers et du chemin de ceux-ci
 * @param string $destination Destination du fichier zip
 * @param boolean $overwrite Si false, on ne réécrit pas par-dessus. Sinon, on remplace le fichier zip
 */
function create_zip($files,$destination, $destination_names = array(), $overwrite = true, $debug = false) {

	if(file_exists($destination) && !$overwrite) {//if the zip file already exists and overwrite is false, return false
		if ($debug === true && P_LOGGED === true) { echo 'Le fichier existe déjà'; }
		return false;
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
		if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {//try opening zip file
			return false;
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

		if (count($invalid_files) && $debug === true && P_LOGGED === true) {
			echo 'Fichiers invalides : ';
			foreach($invalid_files as $file) {
				echo '<br />'.$file;
			}
		}

		return file_exists($destination);//check to make sure the file exists
	} else {
		if ($debug === true && P_LOGGED === true) {
			echo 'Aucun fichier correct';
		}
		return false;
	}
}