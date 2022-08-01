<?php
/**
 * Report Index/Main/Search
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Report;

use Edoceo\Radix\Session;

class Main extends \OpenTHC\Lab\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{

		$dbc = $this->_container->DBC_User;

		$sql = <<<SQL
		SELECT lab_report.id, lab_report.name, lab_report.stat, lab_report.created_at
		  , lab_sample.id AS lab_sample_id, lab_sample.name AS lab_sample_guid
		  , inventory.id AS inventory_id, inventory.guid AS inventory_guid
		  , license.id AS client_license_id, license.name AS client_license_name
		FROM lab_report
		JOIN lab_sample ON lab_report.lab_sample_id = lab_sample.id
		JOIN inventory ON lab_sample.lot_id = inventory.id
		JOIN license ON lab_report.license_id_client = license.id
		WHERE lab_report.license_id = :l0
		ORDER BY lab_report.id DESC
		LIMIT 100
		SQL;

		$arg = [ ':l0' => $_SESSION['License']['id'] ];

		$res = $dbc->fetchAll($sql, $arg);

		$data = $this->loadSiteData();
		$data['Page'] = [ 'title' => 'Lab Reports' ];
		$data['report_list'] = $res;

		return $RES->write( $this->render('report/main.php', $data) );

	}

}
