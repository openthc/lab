<?php
/**
 * Show the Active Inventory Samples
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace App\Controller\Sample;

use Edoceo\Radix\DB\SQL;

use App\Lab_Sample;

class Home extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		// Check Connect
		$dbc = $this->_container->DBC_User;
		if (empty($dbc)) {
			_exit_text('Invalid Session [CSH-019]', 500);
		}

		$data = array(
			'Page' => [ 'title' => 'Lab Samples' ],
			'sample_list' => [],
			'sample_stat' => [
				Lab_Sample::STAT_OPEN => 0,
				Lab_Sample::STAT_LIVE => 0,
				Lab_Sample::STAT_DONE => 0,
				Lab_Sample::STAT_VOID => 0,
			]
		);
		$data = $this->loadSiteData($data);

		$item_offset = 0;
		if (!empty($_GET['p'])) {
			$p = intval($_GET['p']) - 1;
			$item_offset = $p * 100;
		}
		$item_limit = 100;

		$sql = <<<SQL
SELECT count(id) AS c, stat FROM lab_sample WHERE license_id = :l0 GROUP BY stat
SQL;
		$arg = [
			':l0' => $_SESSION['License']['id'],
		];
		$res = $dbc->fetchAll($sql, $arg);
		foreach ($res as $rec) {
			$data['sample_stat'][ $rec['stat'] ] = $rec['c'];
		}

		$arg = [];
		$arg[':l0'] = $_SESSION['License']['id'];

		// Status
		$stat = $_GET['stat'];
		if (empty($stat)) {
			$stat = Lab_Sample::STAT_OPEN;
		}

		$sql_select = <<<SQL
SELECT lab_sample.*
, product.name AS product_name
, variety.name AS variety_name
FROM lab_sample
LEFT JOIN inventory ON lab_sample.lot_id = inventory.id::text
LEFT JOIN product ON inventory.product_id = product.id
LEFT JOIN variety ON inventory.variety_id = variety.id
SQL;

		if ('*' == $stat) {
			$sql = $sql_select . ' WHERE lab_sample.license_id = :l0 ORDER BY lab_sample.created_at DESC OFFSET %d LIMIT %d';
		} else {
			$sql = $sql_select . ' WHERE lab_sample.license_id = :l0 AND lab_sample.stat = :s0 ORDER BY lab_sample.created_at DESC OFFSET %d LIMIT %d';
			$arg[':s0'] = $stat;
		}

		$sql = sprintf($sql, $item_offset, $item_limit);
		// 	$sql = 'SELECT id, stat, meta FROM lab_sample WHERE license_id = :l0 AND flag & :f0 = 0 ORDER BY created_at DESC OFFSET %d LIMIT %d ';
		// 	$sql = sprintf($sql, $item_offset, $item_limit);
		// 	$arg = array(
		// 		':l0' => $_SESSION['License']['id'],
		// 		':f0' => \App\Lab_Sample::FLAG_DEAD
		// 	);
		// }

		// Get Sample Data
		$sample_list = $dbc->fetchAll($sql, $arg);
		array_walk($sample_list, function(&$v, $k) {
			$v['meta'] = json_decode($v['meta'], true);
		});

		$data['sample_list'] = $sample_list;

		// Get Matching Record Counts
		$sql_count = preg_replace('/SELECT.+FROM /', 'SELECT count(*) FROM ', $sql);
		$sql_count = preg_replace('/LIMIT.+$/', null, $sql_count);
		$sql_count = preg_replace('/OFFSET.+$/', null, $sql_count);
		$sql_count = preg_replace('/ORDER BY.+$/', null, $sql_count);

		$c = $dbc->fetchOne($sql_count, $arg);
		$Pager = new \App\UI\Pager($c, 100, $_GET['p']);

		$data['page_list_html'] = $Pager->getHTML();

		return $RES->write( $this->render('sample/main.php', $data) );
	}

}
