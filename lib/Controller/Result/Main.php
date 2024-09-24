<?php
/**
 * Show Result List
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Result;

use OpenTHC\Lab\Lab_Result;

class Main extends \OpenTHC\Lab\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = array(
			'Page' => array('title' => 'Lab Results'),
			'result_list' => array(),
		);
		$data = $this->loadSiteData($data);
		$data = $this->loadSearchPageData($data);
		$data = $this->get_result_list($data);

		return $RES->write( $this->render('result/main.php', $data) );

	}

	/**
	 *
	 */
	function get_result_list($data)
	{
		$dbc = $this->_container->DBC_User;

		$sql_limit = 100;
		$sql_offset = $this->getPageOffset($sql_limit);

		// Base Query
		$sql = <<<SQL
		SELECT lab_result.*
			, lab_sample.id AS lab_sample_id
			, lab_sample.name AS lab_sample_guid
			, inventory.id AS inventory_id
			, inventory.guid AS inventory_guid
		FROM lab_result
		JOIN lab_sample ON lab_result.lab_sample_id = lab_sample.id
		JOIN inventory ON lab_sample.lot_id = inventory.id
		{WHERE}
		{ORDER_BY}
		OFFSET $sql_offset
		LIMIT $sql_limit
		SQL;

		// Paging by ID not OFFSET?
		// if ( ! empty($_GET['gt'])) {
		//      $sql_where[][
		//              'sql' => sprintf(' id > :id1'),
		//              'arg' => $_GET['gt']
		//      ];
		// }

		// License Filter
		$sql_filter = [];
		$sql_filter[] = [
			'sql' => 'lab_result.license_id = :l0',
			'arg' => [
				':l0' => $_SESSION['License']['id']
			]
		];

		// A General Search Query
		if ( ! empty($_GET['q'])) {
			$_GET['q'] = trim($_GET['q']);
			$sql_filter[] = [
				'sql' => <<<SQL
				(
					lab_result.id LIKE :q73
					OR lab_result.name LIKE :q73
					OR lab_sample.id LIKE :q73
					OR lab_sample.name LIKE :q73
					OR inventory.guid LIKE :q73
				)
				SQL,
				'arg' => [
					':q73' => sprintf('%%%s%%', $_GET['q'])
				]
			];
		}

		// Where Filter Merge
		$arg = [];
		$tmp = [];
		foreach ($sql_filter as $i => $f) {
			$tmp[] = $f['sql'];
			$arg = array_merge($arg, $f['arg']);
		}
		$tmp = implode(' AND ', $tmp);
		$sql = str_replace('{WHERE}', sprintf('WHERE %s', $tmp), $sql);

		// Sorting
		$sql_sortby = [
			'lab_result.created_at DESC',
			'lab_result.id'
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
		$sql = str_replace('{ORDER_BY}', sprintf('ORDER BY %s', $tmp), $sql);

		// If ALL then No OFFSET or LIMIT
		if ('ALL' === $_GET['p']) {
			$sql = preg_replace('/OFFSET \d+/', '', $sql);
			$sql = preg_replace('/LIMIT \d+/', '', $sql);
		}

		// Execute
		$res = $dbc->fetchAll($sql, $arg);

		foreach ($res as $rec) {

			$rec['meta'] = \json_decode($rec['meta'], true);
			if ( empty($rec['lab_sample_guid'])) {
				$rec['lab_sample_guid'] = sprintf('Sample: %s', $rec['lab_sample_id']);
			}

			// @todo this should be a FLAG on the Lab_Result object
			// @todo the coa_file should be a property on the lab_result data-model
			$QAR = new Lab_Result(null, $rec);
			$rec['coa_file'] = $QAR->getCOAFile();

			// Try to Read first from META -- our preferred data
			$rec['created_at'] = _date('m/d/y', $rec['created_at']);
			$rec['sum'] = $rec['meta']['sum'] ?: '-';

			$t = array();

			// @deprecated
			$x = $rec['meta']['batch_type'];
			$t[] = $x;

			$x = $rec['meta']['type'];
			$t[] = $x;

			// @deprecated
			$x = $rec['meta']['intermediate_type'];
			$t[] = $x;
			$rec['type'] = trim(implode('/', $t), '/');
			$rec['type_nice'] = $rec['meta']['Product']['type_nice'];
			if (empty($rec['type_nice'])) {
				$rec['type_nice'] = $rec['meta']['type_nice'];
			}
			if (empty($rec['type_nice'])) {
				$rec['type_nice'] = $rec['type'];
			}

			$stat = array();
			if (!empty($rec['coa_file'])) {
				if (is_file($rec['coa_file'])) {
					$stat[] = ' <i class="far fa-file-pdf text-success"></i>';
				} else {
					$stat[] = ' <i class="fas fa-file-pdf text-danger"></i>';
				}
			}

			$x = $rec['stat'];
			switch ($x) {
			case -1:
			case Lab_Result::STAT_FAIL:
			case '/failed':
			case 'completed/failed':
				$stat[] = '<i class="fas fa-check-square" style="color: var(--bs-red);"></i>';
				break;
			case 0:
			case 'in_progress/failed':
				$stat[] = '<i class="fas fa-clock" style="color: var(--bs-gray);"></i>';
				$stat[] = '<i class="fas fa-check-square" style="color: var(--bs-red);"></i>';
				break;
			case 1:
			case Lab_Result::STAT_PASS:
			case 'completed/passed':
				$stat[] = '<i class="far fa-check-square" style="color: var(--bs-green);"></i>';
				break;
			case Lab_Result::STAT_OPEN:
			case Lab_Result::STAT_WAIT:
			case 'in_progress/':
				$stat[] = '<i class="fa-regular fa-hourglass" title="Processing"></i>';
				break;
			// case 'in_progress/passed':
			// 	$stat[] = '<i class="fas fa-clock"></i> <i class="fas fa-check-square" style="color: var(--bs-green);"></i>';
			// 	break;
			// case 'not_started/failed':
			// 	$stat[] = '<i class="fas fa-clock"></i>';
			// 	$stat[] = '<i class="fas fa-check-square" style="color: var(--bs-red);"></i>';
			// 	break;
			default:
				$stat[] = __h($x);
			}

			$rec['status_html'] = implode(' ', $stat);

			$data['result_list'][] = $rec;

		}

		$Pager = $this->convertSearchToPager($dbc, $sql, $arg, $_GET['p'], $sql_limit);

		$data['page_list_html'] = $Pager->getHTML();

		return $data;

	}
}
