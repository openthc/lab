<?php
/**
 * Initialise an Authenticated Session
 */

namespace App\Controller\Auth;

class Init extends \OpenTHC\Controller\Auth\oAuth2
{
	function __invoke($REQ, $RES, $ARG)
	{
		unset($_SESSION['cre']);
		unset($_SESSION['cre-auth']);

		$ret = $_GET['r'];
		if (empty($ret)) {
			$ret = '/dashboard';
		}

		return $RES->withRedirect($ret);

	}
}
