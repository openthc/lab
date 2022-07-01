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

		$dbc = $this->_container->DBC_User;

		$sql = 'SELECT * FROM lab_report WHERE license_id = :l0 ORDER BY id LIMIT 100';
		$arg = [ ':l0' => $_SESSION['License']['id'] ];

		$res = $dbc->fetchAll($sql, $arg);

		$data = $this->loadSiteData();
		$data['Page'] = [ 'title' => 'Lab Reports' ];
		$data['report_list'] = $res;

		return $RES->write( $this->render('report/main.php', $data) );

	}

}
