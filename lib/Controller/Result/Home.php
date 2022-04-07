<?php
/**
 * Show Result List
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace App\Controller\Result;

class Home extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$dbc = $this->_container->DBC_User;
		if (empty($dbc)) {
			_exit_text('Invalid Session [CRH-014]', 500);
		}

		$_GET['p'] = max(1, intval($_GET['p']));

		$data = array(
			'Page' => array('title' => 'Lab Results'),
			'result_list' => array(),
			'result_page' => [
				'older' => (intval($_GET['p']) - 1),
				'newer' => (intval($_GET['p']) + 1),
			],
			'result_stat' => [
				'100' => 0,
				'200' => 0,
				'400' => 0,
			]
		);
		$data = $this->loadSiteData($data);


		$sql_limit = 100;
		$sql_offset = 0;

		if (!empty($_GET['p'])) {

			$p = intval($_GET['p']) - 1;
			if ('ALL' == $_GET['p']) $p = 0;
			$sql_offset = $p * 100;
		}

		$sql = <<<SQL
SELECT count(id) AS c, stat
FROM lab_result
WHERE license_id = :l0
GROUP BY stat
ORDER BY stat
SQL;
		$arg = [];
		$arg[':l0'] = $_SESSION['License']['id'];
		$res = $dbc->fetchAll($sql, $arg);
		foreach ($res as $rec) {
			$data['result_stat'][ $rec['stat'] ] = $rec['c'];
		}


		// Stuff my Company is linked to?
		$sql = <<<SQL
SELECT lab_result.*
  , lab_sample.id AS lab_sample_id
  , lab_sample.name AS lab_sample_guid
FROM lab_result
JOIN lab_sample ON lab_result.lab_sample_id = lab_sample.id
WHERE lab_result.license_id = :l0
ORDER BY created_at DESC, lab_result.id
OFFSET $sql_offset
LIMIT $sql_limit
SQL;

		if ('ALL' === $_GET['p']) {
			$sql = preg_replace('/OFFSET \d+/', '', $sql);
			$sql = preg_replace('/LIMIT \d+/', '', $sql);
		}

		$arg = [ ':l0' => $_SESSION['License']['id'] ];
		$res = $dbc->fetchAll($sql, $arg);

		foreach ($res as $rec) {

			$rec['meta'] = \json_decode($rec['meta'], true);
			if ( empty($rec['lab_sample_guid'])) {
				$rec['lab_sample_guid'] = sprintf('Sample: %s', $rec['lab_sample_id']);
			}

			// @todo this should be a FLAG on the Lab_Result object
			// @todo the coa_file should be a property on the lab_result data-model
			$QAR = new \App\Lab_Result(null, $rec);
			$rec['coa_file'] = $QAR->getCOAFile();

			// Try to Read first from META -- our preferred data
			$rec['created_at'] = _date('m/d/y', $rec['created_at']);
			$rec['sum'] = $rec['meta']['sum'] ?: '-';
			$rec['testing_status'] = $rec['meta']['testing_status'];
			$rec['status'] = $rec['meta']['status'];

			$t = array();
			$x = $rec['meta']['batch_type'];
			$t[] = $x;

			$x = $rec['meta']['type'];
			$t[] = $x;

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
					$stat[] = ' <i class="far fa-file-pdf"></i>';
				} else {
					$stat[] = ' <i class="far fa-file-pdf text-danger"></i>';
				}
			}

			$x = sprintf('%s/%s', $rec['testing_status'], $rec['status']);
			switch ($x) {
			case '/failed':
			case 'completed/failed':
				$stat[] = '<i class="fas fa-check-square" style="color: var(--red);"></i>';
				break;
			case 'completed/passed':
				$stat[] = '<i class="fas fa-check-square" style="color: var(--green);"></i>';
				break;
			case 'in_progress/failed':
				$stat[] = '<i class="fas fa-clock" style="color: var(--gray);"></i>';
				$stat[] = '<i class="fas fa-check-square" style="color: var(--red);"></i>';
				break;
			case 'in_progress/passed':
				$stat[] = '<i class="fas fa-clock"></i> <i class="fas fa-check-square" style="color: var(--green);"></i>';
				break;
			case 'not_started/failed':
				$stat[] = '<i class="fas fa-clock"></i>';
				$stat[] = '<i class="fas fa-check-square" style="color: var(--red);"></i>';
				break;
			default:
				$stat[] = h($x);
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
		$Pager = new \App\UI\Pager($res, $sql_limit, $_GET['p']);

		$data['page_list_html'] = $Pager->getHTML();

		return $RES->write( $this->render('result/main.php', $data) );

	}
}
