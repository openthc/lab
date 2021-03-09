<?php
/**
 * Initialize The Session
 */

namespace App\Controller\Auth;

class Init extends \OpenTHC\Controller\Auth\oAuth2
{
	function __invoke($REQ, $RES, $ARG)
	{
		unset($_SESSION['cre']);
		unset($_SESSION['cre-auth']);
		unset($_SESSION['cre-base']);

		$r = $_GET['r'];
		if (empty($r)) {
			$r = '/home';
		}

		return $RES->withRedirect($r);

	}
}
