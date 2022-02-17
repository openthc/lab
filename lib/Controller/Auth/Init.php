<?php
/**
 * Initialise an Authenticated Session
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace App\Controller\Auth;

use OpenTHC\License;

class Init extends \OpenTHC\Controller\Auth\oAuth2
{
	function __invoke($REQ, $RES, $ARG)
	{

		// Maybe?
		// $this->connectCRE();

		unset($_SESSION['cre']);
		unset($_SESSION['cre-auth']);

		if (empty($_SESSION['License'])) {

			$dbc = $this->_container->DBC_User;
			$sql = 'SELECT * FROM license WHERE flag & :f0 = 0 AND flag & :f1 != 0';
			$arg = array(
				':f0' => License::FLAG_DEAD,
				':f1' => License::FLAG_MINE
			);
			$chk = $dbc->fetchRow($sql, $arg);
			if ( ! empty($chk)) {
				$_SESSION['License'] = $chk;
			}

		}

		$ret = $_GET['r'];
		if (empty($ret)) {
			$ret = '/dashboard';
		}

		return $RES->withRedirect($ret);

	}
}
