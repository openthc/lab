<?php
/**
 * Show the Active Inventory Samples
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Sample;

use Edoceo\Radix\DB\SQL;

use OpenTHC\Lab\Lab_Sample;
use OpenTHC\Lab\UI\Pager;

class Main extends \OpenTHC\Lab\Controller\Base
{
	/**
	 *
	 */
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
		);
		$data = $this->loadSiteData($data);
		$data = $this->loadSearchPageData($data);

		$item_offset = 0;
		if (!empty($_GET['p'])) {
			$p = intval($_GET['p']) - 1;
			$item_offset = $p * 100;
		}
		$item_limit = 100;

		$sql_param = [];
		$sql_where = [];

		$sql_where[] = 'lab_sample.license_id = :l0';
		$sql_param[':l0'] = $_SESSION['License']['id'];

		$sql_select = <<<SQL
		SELECT lab_sample.*
			, license.name AS source_license_name
			, product.name AS product_name
			, variety.name AS variety_name
		FROM lab_sample
		JOIN license ON lab_sample.license_id_source = license.id
		LEFT JOIN inventory ON lab_sample.lot_id = inventory.id
		LEFT JOIN product ON inventory.product_id = product.id
		LEFT JOIN variety ON inventory.variety_id = variety.id
		{WHERE}
		{ORDER}
		OFFSET %d
		LIMIT %d
		SQL;

		// WHERE
		if ( ! empty($_GET['q'])) {
			$sql_where[] = sprintf('(lab_sample.id ILIKE :q83 OR lab_sample.name ILIKE :q83 OR license.name ILIKE :q83 OR license.code ILIKE :q83)');
			$sql_param[':q83'] = sprintf('%%%s%%', trim($_GET['q']));
		} else {
			// Status Filter
			// $stat = $_GET['stat'];
			// if (empty($stat)) {
			// 	$stat = Lab_Sample::STAT_OPEN;
			// }
			// if ('*' != $stat) {
			// 	$sql_where[] = 'lab_sample.stat = :s0';
			// 	$sql_param[':s0'] = $stat;
			// }
		}
		$sql_where = implode(' AND ', $sql_where);
		$sql = str_replace('{WHERE}', sprintf('WHERE %s', $sql_where), $sql_select);

		// ORDER
		$sql_order = '';
		switch ($_GET['sort']) {
			case 'license-origin':
				$sql_order = 'ORDER BY license.name %s, lab_sample.name ASC';
				break;
			case 'sample-id':
				$sql_order = 'ORDER BY lab_sample.name %s';
				break;
			default:
				$sql_order = 'ORDER BY lab_sample.created_at %s, lab_sample.name ASC';
		}
		$sql_order = sprintf($sql_order, ($_GET['sort-dir'] == 'desc' ? 'desc' : 'asc'));
		$sql = str_replace('{ORDER}', $sql_order, $sql);

		// Offset and Limit
		$sql = sprintf($sql, $item_offset, $item_limit);

		// Get Sample Data
		$sample_list = $dbc->fetchAll($sql, $sql_param);
		array_walk($sample_list, function(&$v, $k) {
			$v['meta'] = json_decode($v['meta'], true);
		});

		$data['sample_list'] = $sample_list;

		// Get Matching Record Counts
		$sql_count = preg_replace('/SELECT.+FROM /', 'SELECT count(*) FROM ', $sql);
		$sql_count = preg_replace('/LIMIT.+$/', null, $sql_count);
		$sql_count = preg_replace('/OFFSET.+$/', null, $sql_count);
		$sql_count = preg_replace('/ORDER BY.+$/', null, $sql_count);

		$c = $dbc->fetchOne($sql_count, $sql_param);
		$Pager = new Pager($c, 100, $_GET['p']);

		$data['page_list_html'] = $Pager->getHTML();
		$data['page_older'] = max(1, intval($_GET['p']) - 1);
		$data['page_newer'] = intval($_GET['p']) + 1;

		return $RES->write( $this->render('sample/main.php', $data) );
	}

	/**
	 *
	 */
	function loadSampleStat(array $data) : array
	{
		$data['sample_stat'] = [
			Lab_Sample::STAT_OPEN => 0,
			Lab_Sample::STAT_LIVE => 0,
			Lab_Sample::STAT_DONE => 0,
			Lab_Sample::STAT_VOID => 0,
		];

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

		return $data;

	}

}
