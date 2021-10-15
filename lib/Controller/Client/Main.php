<?php
/**
 * (c) 2018 OpenTHC, Inc.
 * This file is part of OpenTHC Lab Portal released under GPL-3.0 License
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * View a Client
 */

namespace App\Controller\Client;

class Main extends \App\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->loadSiteData();
		$data['Page'] = [ 'title' => 'Client' ];

		$dbc = $this->_container->DBC_User;

		$sql = <<<SQL
SELECT * FROM license
SQL;
		// SELECT * FROM license WWHERE id IN
		$data['license_list'] = $dbc->fetchAll($sql);

		return $RES->write( $this->render('client/main.php', $data) );
	}

}
