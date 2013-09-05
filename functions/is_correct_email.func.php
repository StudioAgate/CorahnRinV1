<?php
/**
 * Via diverses librairies et fonctions, cette fonction se charge de vérifier si l'email entré est correct
 * 		Il fait la vérification en premier lieu sur le nom de domaine, pour voir s'il n'est pas blacklisté
 * 		Il vérifie la présence d'un serveur MX sur le domaine
 * 		Il vérifie la conformité de l'adresse mail sur une regex complexe
 * 		Et enfin il vérifie si le nom de domaine n'a pas été blacklisté manuellement par le développeur
 * Si un seul élément s'avère non conforme, alors le mail n'est pas correct.
 *
 * @param string $email L'adresse mail à vérifier
 * @return boolean False si elle est incorrecte, true sinon
 * @author Pierstoval 18/05/2013
 */
function is_correct_email ($email) {

	$host = preg_replace('#^.*@([^@]+)$#isUu', '$1', $email);
// 	$ips = gethostbynamel($host);
	$ip_blacklisted = false;
	//foreach($ips as $ip) { if (is_blacklisted($ip)) { $ip_blacklisted = true; } }

	$has_MX = checkdnsrr($host, 'MX');

	$manual_blacklist = array('yopmail');
	$is_manual_blacklisted = false;
	foreach($manual_blacklist as $v) { if (preg_match('#'.$v.'#isUu', $host)) { $is_manual_blacklisted = true; } }

	if (preg_match('#yopmail#isUu', $host) ||
		!preg_match(P_MAIL_REGEX, $email) ||
		$ip_blacklisted === true ||
		$has_MX === false ||
		$is_manual_blacklisted === true
	) {
		if (P_DEBUG === true) {
			echo 'Mail incorrect : ';
			pr(preg_match('#yopmail#isUu', $host));
			pr(!preg_match(P_MAIL_REGEX, $email));
			pr($ip_blacklisted);
			pr($has_MX);
			pr($is_manual_blacklisted);
		}
		return false;
	} else {
		return true;
	}
}