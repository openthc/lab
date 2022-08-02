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

		$data = $this->loadSiteData();
		$data['Page'] = [ 'title' => 'Lab Reports' ];
		$data['report_list'] = $this->get_report_list();

		return $RES->write( $this->render('report/main.php', $data) );

	}

	/**
	 *
	 */
	function get_report_list()
	{
		$dbc = $this->_container->DBC_User;

		$sql_limit  = 100;
		$sql_offset = 0;

		if (!empty($_GET['p'])) {
			$p = intval($_GET['p']) - 1;
			if ('ALL' == $_GET['p']) $p = 0;
			$sql_offset = $p * $sql_limit;
		}

		$sql = <<<SQL
		SELECT lab_report.id, lab_report.name, lab_report.flag, lab_report.stat, lab_report.created_at
		  , lab_sample.id AS lab_sample_id, lab_sample.name AS lab_sample_guid
		  , inventory.id AS inventory_id, inventory.guid AS inventory_guid
		  , license.id AS client_license_id, license.name AS client_license_name
		FROM lab_report
		JOIN lab_sample ON lab_report.lab_sample_id = lab_sample.id
		JOIN inventory ON lab_sample.lot_id = inventory.id
		JOIN license ON lab_report.license_id_client = license.id
		WHERE {WHERE}
		ORDER BY {ORDER_BY}
		LIMIT 100
		SQL;

		$sql_filter = [];
		$sql_filter[] = [
			'sql' => 'lab_report.license_id = :l0',
			'arg' => [
				':l0' => $_SESSION['License']['id']
			]
		];

		if ( ! empty($_GET['q'])) {
			$_GET['q'] = trim($_GET['q']);
			$sql_filter[] = [
				'sql' => '(lab_report.id LIKE :q69 OR lab_report.name LIKE :q69 OR lab_sample.id LIKE :q69 OR lab_sample.name LIKE :q69)',
				'arg' => [
					':q69' => sprintf('%%%s%%', $_GET['q'])
				]
			];
		}

		$arg = [];
		$tmp = [];
		foreach ($sql_filter as $i => $f) {
			$tmp[] = $f['sql'];
			$arg = array_merge($arg, $f['arg']);
		}
		$tmp = implode(' AND ', $tmp);
		$sql = str_replace('{WHERE}', $tmp, $sql);

		// Sorting
		$sql_sortby = [
			'lab_report.created_at DESC',
			'lab_report.id'
		];
		if ( ! empty($_GET['sort'])) {
			switch ($_GET['sort']) {
				case 'created-at':
					// DEFAULT
					break;
				case 'result-id':
				case 'sample-id':
			}
		}
		$tmp = implode(', ', $sql_sortby);
		$sql = str_replace('{ORDER_BY}', $tmp, $sql);

		// Undo Limits when ALL
		if ('ALL' === $_GET['p']) {
			$sql = preg_replace('/OFFSET \d+/', '', $sql);
			$sql = preg_replace('/LIMIT \d+/', '', $sql);
		}

		// Query
		$res = $dbc->fetchAll($sql, $arg);

		return $res;
	}

}
