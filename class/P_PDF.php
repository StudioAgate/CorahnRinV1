<?php
/**
 * Alias de tFPDF, implémente quelques fonctions de confort
 *
 * @author Pierstoval 01/06/2013
 * @see tFPDF
 */
class P_PDF extends tFPDF {

	function __construct($orientation='P', $unit='mm', $size='A4') {
		parent::__construct($orientation, $unit, $size);
	}

	/**
	 * Retourne un texte formaté avec des sauts de ligne pour chaque ligne de texte
	 *
	 * @param string $text	La chaîne de caractères à écrire
	 * @param int $x		Coordonnées X
	 * @param int $y		Coordonnées Y
	 * @param array $font	Tableau contenant les attributs de la police de caractère à utiliser
	 * @param int $size		Taille du texte en pixels à l'instar de GD2
	 * @param int $width	Largeur maximale de la textbox
	 * @author Pierstoval	06/06/2013
	 */
	function multiple_lines($text, $x, $y, $font, $size, $width, $lines, $line_offset) {
		$text = str_replace("\r", '', $text);
		$text = str_replace("\n", ' ', $text);
		$str = '';
		$arr = preg_split("#\s| #isUu", $text);
		$i = 0;
		// 		$widths = array();
		foreach ($arr as $word) {
			$teststring = $str.' '.$word;
			$testbox = imagettfbbox($size, 0, $font['file'], $teststring);
			if ($testbox[2] > $width){
				$str .= ($str == "" ? "" : "\n").$word;
				// 				$widths[] = $testbox[2];
			} else {
				$str .= ($str == "" ? "" : ' ').$word;
				// 				$widths[] = $testbox[2];
			}
		}
		// 		$last_width = array_pop($widths);
		$str = explode("\n", $str);
		$i = 0;
		foreach($str as $v) {
			if ($i < ($lines - 1)) {
				$this->textbox($v, $x, $y + ($i * $line_offset), $font, $size, $width);
			} elseif ($i == ($lines - 1) && $lines !== 1) {
				$this->textline($v.'(...)', $x, $y + ($i * $line_offset), $font, $size);
				//$this->textbox('(...)', $x + $last_width + $size, $y + (($i-1) * $line_offset), $font, $size, $width);
			} else {
				break;
			}
			$i++;
		}
	}

	/**
	 * Imprime du texte sur un pdf à la manière d'un script utilisant imagettfbbox et GD
	 *
	 * @param string $text	La chaîne de caractères à écrire
	 * @param int $x		Coordonnées X
	 * @param int $y		Coordonnées Y
	 * @param array $font	Tableau contenant les attributs de la police de caractère à utiliser
	 * @param int $size		Taille du texte en pixels à l'instar de GD2
	 * @param int $width	Largeur maximale de la textbox
	 * @author Pierstoval	06/06/2013
	 */
	function textbox($text, $x, $y, $font, $size, $width) {
		$text = str_replace("\n", '', $text);
		$text = str_replace("\r", '', $text);
		$str = '';
		$arr = str_split($text);
		$too_big = false;
		foreach ($arr as $letter) {
			$teststring = $str.$letter;
			$testbox = imagettfbbox($size, 0, $font['file'], $teststring);
			if ( $testbox[2] <= $width ){
				$str .= $letter;
			} else {
				$too_big = true;
				//$str .= "\n";
			}
		}
		if ($too_big === true) {
			$str .= '(...)';
		}
		$this->textline($str, $x, $y, $font, $size);
	}

	/**
	 * Imprime du texte sur un pdf à la manière d'un script utilisant imagettfbbox et GD
	 *
	 * @param string $text	La chaîne de caractères à écrire
	 * @param int $x		Coordonnées X
	 * @param int $y		Coordonnées Y
	 * @param array $font	Tableau contenant les attributs de la police de caractère à utiliser
	 * @param int $size		Taille du texte en pixels à l'instar de GD2
	 * @author Pierstoval	06/06/2013
	 */
	function textline($text, $x, $y, $font, $size) {
		$text = str_replace("\n", '', $text);
		$text = str_replace("\r", '', $text);
		// 		$ratio = 4.3291351805206; //Ceci est le ratio entre la police de texte de GD2 et celle de FPDF, cela permet la même taille de texte entre GD2 et FPDF pour la même valeur
		// 		$ratio = 1.28;
		$ratio = 1;
		$text = (string) $text;
		$text = str_replace("\n", '', $text);
		$this->SetFont($font['name'],'',$size*$ratio);
		// 		$this->SetX($x);
		// 		$this->SetY($y);
		// 		$x *= 0.7601351351351;
		// 		$y *= 0.7601351351351;
		$x *= 0.75;
		$y *= 0.75;
		$this->Text($x, $y, $text);
	}
}