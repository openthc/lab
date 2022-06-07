<?php
/**
 * Report Index/Main/Search
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Report;

use Edoceo\Radix\Session;

class Main extends \App\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		return $RES->write( $this->render('report/main.php', $data) );
	}

}
