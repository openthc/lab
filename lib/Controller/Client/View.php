<?php
/**
 * (c) 2018 OpenTHC, Inc.
 * This file is part of OpenTHC Lab Portal released under GPL-3.0 License
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * View a Client
 */

namespace OpenTHC\Lab\Controller\Client;

class View extends \OpenTHC\Lab\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->loadSiteData();
		$data['Page'] = [ 'title' => 'Client' ];

		$dbc = $this->_container->DBC_User;

		// Get Result
		$Client = new \OpenTHC\License($dbc, $ARG['id']);
		if (empty($Client['id'])) {
			_exit_html('Client Not Found', 404);
		}
		$data['Client'] = $Client->toArray();

		$sql = <<<SQL
SELECT lab_result.*
FROM lab_result
JOIN lab_sample ON lab_result.lab_sample_id = lab_sample.id
WHERE lab_result.license_id = :l0
AND lab_sample.license_id_source = :l1
ORDER BY lab_result.created_at DESC, lab_result.id
SQL;

		$arg = [
			':l0' => $_SESSION['License']['id'],
			':l1' => $Client['id'],
		];

		$data['lab_result_list'] = $dbc->fetchAll($sql, $arg);

		return $RES->write( $this->render('client/single.php', $data) );

	}
}
