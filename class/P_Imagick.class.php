<?php
class P_Imagick {

	private $cmd = array();
	private $actual_cmd_index = 0;
	private $source_file = '';
	private $convert_path = '';
	private $destination_file = '';

	public function get($var) {
		if ($var && isset($this->$var)) {
			return $this->$var;
		} else {
			trigger_error('P_Imagick Error : var '.$var.' does not exist', E_USER_NOTICE);
			return null;
		}
	}

	/**
	 * Crée les attributs de l'objet pour créer des lignes de commande ImageMagick
	 *
	 * @param string $source	Le fichier source à convertir
	 * @param string $dest		Le fichier destination si besoin
	 * @param number $x_resize	L'éventuel redimensionnement en largeur à effectuer
	 * @param number $y_resize	L'éventuel redimensionnement en hauteur à effectuer
	 */
	function __construct($source, $dest, $x_resize = 0, $y_resize = 0, $convert_path = 'convert') {
		if (!file_exists($source)) {
			trigger_error('P_Imagick Error : source image does not exist', E_USER_ERROR);
			return false;
		}
		$source = str_replace(DS, '/', $source);
		$dest = str_replace(DS, '/', $dest);
		$this->source_file = $source;
		$this->destination_file = $dest;
		if (!preg_match('#convert$#', $convert_path)) {
			$convert_path .= 'convert';
		}
		$this->convert_path = $convert_path;
		$this->cmd[0] = '"'.$convert_path.'" "'.$source.'" ';
	}

	/**
	 * Alias de __construct pour réutiliser la même instance de l'objet avec d'autres commandes
	 *
	 * @param string $source	Le fichier source à convertir
	 * @param string $dest		Le fichier destination si besoin
	 * @param number $x_resize	L'éventuel redimensionnement en largeur à effectuer
	 * @param number $y_resize	L'éventuel redimensionnement en hauteur à effectuer
	 */
	public function setup($source, $dest, $x_resize = 0, $y_resize = 0, $convert_path = '') {
		if (!$convert_path) { $convert_path = $this->convert_path; }
		$this->__construct($source, $dest, $x_resize = 0, $y_resize = 0, $convert_path);
	}

	public function reset() {
		$this->cmd = array();
		$this->actual_cmd_index = 0;
		$this->source_file = '';
		$this->destination_file = '';
	}

	/**
	 * Exécute les commandes et peut choisir d'afficher ou non l'image
	 *
	 * @param string $type Un paramètre optionnel
	 * @return boolean|string
	 */
	public function output($type = 'f') {
		$type = strtolower($type);
		$this->cmd[$this->actual_cmd_index] .= ' "'.$this->destination_file.'"';

		//Exécution des commandes
		foreach ($this->cmd as $command) {
			$raw_output = exec($command, $func_output, $error_code);
			if ($error_code !== 0) {
				pr(array('$raw_output'=>$raw_output,'$func_output'=>$func_output,'$error_code'=>$error_code,'$command'=>$command,));
				trigger_error('P_Imagick Error : command output has failed and returned error code '.$error_code, E_USER_WARNING);
			}
		}

		if ($type === 'b') {
			//Envoi au buffer. Attention aux headers !
			$cnt = file_get_contents($this->destination_file);
			echo $cnt;
		} elseif ($type === 'f') {
			return $this->destination_file;
		}
	}

	/**
	 * Récupère la commande demandée par les méthodes d'ImageMagick et l'ajoute aux commandes de l'objet.
	 * Si la chaîne de commande est trop longue (8194 sous Windows, largement supérieur sur la plupart des systèmes Unix)
	 *  alors une autre commande sera générée pour en effectuer plusieurs à la suite et ainsi éviter les bugs.
	 *
	 * @param string $cmd La fonction ImageMagick à ajouter
	 */
	public function command($cmd) {
		if (strlen($this->cmd[$this->actual_cmd_index].$cmd) > (8000 - strlen($this->destination_file)) && DS === "\\") {
			//Si on est sous Windows, on limite la taille de la commande
			$this->cmd[$this->actual_cmd_index] .= ' "'.$this->destination_file.'"';
			$this->actual_cmd_index ++;
			$this->cmd[$this->actual_cmd_index] = '"'.$this->convert_path.'" "'.$this->destination_file.'" '.$cmd;
		} else {
			//Sinon, sous Unix, en règle générale on peut avoir des dizaines de milliers de caractère sans avoir de bug.
			$this->cmd[$this->actual_cmd_index] .= $cmd;
		}
	}

	/**
	 * Génère une chaîne utilisée par ImageMagick en CLI dans le but d'écrire du texte
	 *
	 * @param int $size Taille du texte
	 * @param int $x Position X du texte dans l'image
	 * @param int $y Position Y du texte dans l'image
	 * @param string $color Couleur du texte en hexadécimal, #000000
	 * @param string $font Chemin vers le fichier de la police de caractères
	 * @param string $contents Le contenu à ajouter
	 */
	function text($size, $x, $y, $color, $font, $contents, $ratio = true) {
		$size = (int) $size;
		if ($ratio === true) { $size = (int) ($size * 1.4); }//Le ratio de la taille du texte est différent entre GD et Imagick
		$x = (int) $x;
		$y = (int) $y;
		$color = (string) $color;
		$font = (string) $font;
		$font = str_replace(DS, '/', $font);

		$contents = Translate::clean_word($contents);
// 		$contents = str_replace('"', '\"', $contents);
		$contents = Encoding::toISO8859($contents);

		$convert_cmd = ' -font "'.$font.'" -pointsize '.$size.' -fill "'.$color.'" -stroke "none" -annotate +'.$x.'+'.$y.' "'.$contents.'" ';
// 		if (DS === '/') {
// 			$convert_cmd .= '\\';
// 		} else {
// 			$convert_cmd .= '^';
// 		}
// 		$convert_cmd .= PHP_EOL;

		$this->command($convert_cmd);
		return $convert_cmd;
	}

	/**
	 * Génère une chaîne utilisée par ImageMagick en CLI dans le but d'effectuer un dessin d'ellipse
	 * Exemple : -stroke stroke_color -fill "#fill_color" -draw "ellipse x_center,y_center width,height angle_start,angle_end"
	 *
	 * @param number $x_center
	 * @param number $y_center
	 * @param number $width
	 * @param number $height
	 * @param string $fill_color
	 * @param string $stroke_color
	 * @param number $angle_start
	 * @param number $angle_end
	 * @return string
	 */
	public function ellipse($x_center, $y_center, $width, $height, $fill_color, $stroke_color = '', $angle_start = 0, $angle_end = 360) {
		$x_center = (int) $x_center;
		$y_center = (int) $y_center;
		$width = (int) $width;
		$height = (int) $height;
		$fill_color = '#'.str_pad((int) $fill_color, 6, '0');
		$stroke_color = '#'.str_pad((int) $stroke_color, 6, '0');
		$angle_start = (int) $angle_start;
		$angle_end = (int) $angle_end;

		$convert_cmd = '';
		if ($stroke_color) { $convert_cmd .= ' -stroke "'.$stroke_color.'"'; }
		$convert_cmd .= ' -fill "'.$fill_color.'" -draw "ellipse '.$x_center.','.$y_center.' '.$width.','.$height.' '.$angle_start.','.$angle_end.'"';
// 		if (DS === '/') {
// 			$convert_cmd .= '\\';
// 		} else {
// 			$convert_cmd .= '^';
// 		}
// 		$convert_cmd .= PHP_EOL;

		$this->command($convert_cmd);
		return $convert_cmd;
	}
}