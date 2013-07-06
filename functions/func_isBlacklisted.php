<?php
/**
 * Librairie récupérée sur http://php.net/
 * Cette fonction se charge de vérifier par ip inversée si l'ip passée en paramètre est blacklistée
 *
 * @param string $ip L'adresse IP à vérifier
 * @return array $result La liste des résultats
 * @author Pierstoval 18/05/2013
 */
function is_blacklisted ($ip) {
	// written by satmd, do what you want with it, but keep the author please
	$result = array();
	$dnsbl_check = array("bl.spamcop.net", "list.dsbl.org", "sbl.spamhaus.org");
	if ($ip) {
		$quads = explode(".",$ip);
		$rip = $quads[3].".".$quads[2].".".$quads[1].".".$quads[0];
		$count = count($dnsbl_check);
		for ($i = 0; $i < $count; $i++) {
			if (checkdnsrr($rip.".".$dnsbl_check[$i].".","A")) {
				$result[] = array($dnsbl_check[$i],$rip.".".$dnsbl_check[$i]);
			}
		}
	}
	return $result;
}