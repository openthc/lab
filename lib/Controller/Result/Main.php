<?php
/**
 * Show Result List
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Result;

use OpenTHC\Lab\Lab_Result;
use OpenTHC\Lab\UI\Pager;

class Main extends \OpenTHC\Lab\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$dbc = $this->_container->DBC_User;
		if (empty($dbc)) {
			_exit_text('Invalid Session [CRH-014]', 500);
		}

		$data = array(
			'Page' => array('title' => 'Lab Results'),
			'result_list' => array(),
		);
		$data = $this->loadSiteData($data);
		$data = $this->loadSearchPageData($data);

		$sql_limit = 100;
		$sql_offset = 0;

		if (!empty($_GET['p'])) {

			$p = intval($_GET['p']) - 1;
			if ('ALL' == $_GET['p']) $p = 0;
			$sql_offset = $p * 100;
		}


		// Stuff my Company is linked to?
		$sql = <<<SQL
		SELECT lab_result.*
			, lab_sample.id AS lab_sample_id
			, lab_sample.name AS lab_sample_guid
			, inventory.id AS inventory_id
			, inventory.guid AS inventory_guid
		FROM lab_result
		JOIN lab_sample ON lab_result.lab_sample_id = lab_sample.id
		JOIN inventory ON lab_sample.lot_id = inventory.id
		WHERE {WHERE}
		ORDER BY {SORTBY}
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

		if ('ALL' === $_GET['p']) {
			$sql = preg_replace('/OFFSET \d+/', '', $sql);
			$sql = preg_replace('/LIMIT \d+/', '', $sql);
		}

		$sql_filter = [];
		$sql_filter[] = [
			'sql' => 'lab_result.license_id = :l0',
			'arg' => [
				':l0' => $_SESSION['License']['id']
			]
		];

		if ( ! empty($_GET['q'])) {
			$_GET['q'] = trim($_GET['q']);
			$sql_filter[] = [
				'sql' => '(lab_result.id LIKE :q73 OR lab_result.name LIKE :q73 OR lab_sample.id LIKE :q73 OR lab_sample.name LIKE :q73)',
				'arg' => [
					':q73' => sprintf('%%%s%%', $_GET['q'])
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
		$sql = str_replace('{SORTBY}', $tmp, $sql);

		// Query
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
			case 400:
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
			case 200:
			case 'completed/passed':
				$stat[] = '<i class="far fa-check-square" style="color: var(--bs-green);"></i>';
				break;
			case 100:
			case 102:
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

		// Get Matching Record Counts
		$sql_count = preg_replace('/SELECT.+FROM /ms', 'SELECT count(*) FROM ', $sql);
		$sql_count = preg_replace('/LIMIT.+$/ms', null, $sql_count);
		$sql_count = preg_replace('/OFFSET.+$/ms', null, $sql_count);
		$sql_count = preg_replace('/ORDER BY.+$/ms', null, $sql_count);

		$res = $dbc->fetchOne($sql_count, $arg);
		$Pager = new Pager($res, $sql_limit, $_GET['p']);

		$data['page_list_html'] = $Pager->getHTML();

		return $RES->write( $this->render('result/main.php', $data) );

	}
}
