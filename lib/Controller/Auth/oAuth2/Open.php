<?php
/**
 * oAuth2 Starts Here, redirects to oAuth provider
 */

namespace App\Controller\Auth\oAuth2;

class Open extends \OpenTHC\Controller\Auth\oAuth2
{
	function __invoke($REQ, $RES, $ARG)
	{
		// Clear Session
		$key_list = array_keys($_SESSION);
		foreach ($key_list as $k) {
			unset($_SESSION[$k]);
		}

		$r = $_GET['r'];
		//if (empty($r)) {
		//	//$r = $_SERVER['HTTP_REFERER'];
		//}

		$p = $this->getProvider($r);

		$arg = array(
			'scope' => 'profile lab',
		);
		$url = $p->getAuthorizationUrl($arg);

		$_SESSION['oauth2-state'] = $p->getState();

		return $RES->withRedirect($url);

	}

}
