<?php

use App\PHPMailer;

/**
 * Envoie un mail structuré en utilisant la fonction mail() de PHP
 *
 * @param mixed $dest Tableau ou chaîne de caractères avec les infos du destinataire
 * @param string $subj Le titre ou sujet du message
 * @param string $message Le contenu en HTML du message
 * @param int $mail_id Un identifiant correspondant au type de mail reçu, dans la table est_mails
 * @param array $from L'entête "From" pour savoir à qui répondre
 * @param array $add_headers Un tableau où l'association clé=>valeur correspond à "en-tête"=>"valeur"
 * @return true si le mail est envoyé, false sinon
 * @author Pierstoval 17/06/2013
 */
function send_mail($dest = array(), $subj = '', $message = '', $mail_id = 0, $from = array()) {
	global $db;

	if (is_string($dest)) {
		$dest = array(
			'mail' => $dest,
			'name' => $dest,
		);
	}

	if (is_string($from)) {
		$from = array(
			'name' => $from,
			'mail' => $from,
		);
	} elseif ($from === array()) {
		$from = array(
			'name' => P_MAIL_DEFAULT_FROM_NAME,
			'mail' => P_MAIL_DEFAULT_FROM_MAIL,
		);
	}

	$message = '
	<html>
		<head>
			<title>Corahn Rin - '.$subj.'</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		</head>
		<body>
			<p style="text-align: center;font-size:19px;"><span style="font-weight:bold;">Corahn-Rin</span><br />La plateforme de jeu des Ombres d\'Esteren</p>
			<div style="font-size: 14px">'.$message.'</div>
			<p style="font-size: 10px; text-align: center;">
				Corahn Rin - Plateforme de jeu des Ombres d\'Esteren - Pierstoval - contact@pierstoval.com - 2012-2013<br />
				Tous droits réservés - Pierstoval 2012-2013<br />
				Tous les contenus sont générés par l\'auteur du site, mais proviennent des livres des Ombres d\'Esteren, et appartiennent au collectif Forgesonges, et édité par Agate Editions.
			</p>
		</body>
	</html>';

	$alt_message = wordwrap('
		Corahn Rin - '.$subj.'
		La plateforme de jeu des Ombres d\'Esteren
		(Attention, ce message est une alternative pour les clients mail ne lisant pas le HTML, il est possible que sa structure comprenne des erreurs.)

		'.wordwrap(strip_tags($message), 70, "\r\n").'

		Corahn Rin - Plateforme de jeu des Ombres d\'Esteren - Pierstoval - pierstoval@protonmail.com - 2012-'.date('Y').'
		Tous droits réservés - Pierstoval 2012-'.date('Y').'
		Tous les contenus sont générés par l\'auteur du site, mais proviennent des livres des Ombres d\'Esteren, et appartiennent au collectif Forgesonges, et édité par Agate Editions.
	', 70, "\r\n");

	$mail = new PHPMailer(true);

// 	$mail->SMTPDebug = 2;
// 	$mail->Debugoutput = 'html';

	//$mail->AddCustomHeader('MIME-Version', '1.0');

	$mail->SetLanguage(P_LANG);

    $mail->IsSMTP();//Active le SMTP

    if (P_MAIL_SMTP_USER) {
        $mail->SMTPAuth = true;//Authentification SMTP requise
        $mail->Username = P_MAIL_SMTP_USER;
        $mail->Password = P_MAIL_SMTP_PASSWORD;
    }
    $mail->SMTPKeepAlive = true;//Permet de ne pas fermer la connexion après chaque mail envoyé

    $mail->Host = P_MAIL_SMTP_HOST;
    if (P_MAIL_SMTP_SECURE) {
        $mail->SMTPSecure = P_MAIL_SMTP_SECURE;
    }
    $mail->Port = P_MAIL_SMTP_PORT;

	$mail->SetFrom($from['mail'], $from['name']);
	$mail->AddReplyTo($from['mail'], $from['name']);

	$mail->AddAddress($dest['mail'], $dest['name']);

	$mail->WordWrap = 70;
	$mail->IsHTML();

	$mail->Subject = $subj;

	$mail->CharSet = 'UTF-8';
	$mail->MsgHTML($message);
	$mail->AltBody = $alt_message;

	if ($mail->Send()) {
		$mail->ClearAddresses();
		$mail->ClearAttachments();
		unset($mail);
		if ($mail_id) {
			$datas = array(
				'mail_dest' => json_encode($dest),
				'mail_id' => $mail_id,
				'mail_subj' => $subj,
				'mail_content' => $message,
				'mail_date' => time(),
			);
			return $db->noRes('INSERT INTO %%mails_sent SET %%%fields', $datas);
		}
		return true;
	} else {
		return false;
	}
}
