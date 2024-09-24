<?php
/**
 * Show the Active Inventory Samples
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Sample;

use Edoceo\Radix\DB\SQL;

use OpenTHC\Lab\Lab_Sample;

class Main extends \OpenTHC\Lab\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = array(
			'Page' => [ 'title' => 'Lab Samples' ],
			'sample_list' => [],
		);
		$data = $this->loadSiteData($data);
		$data = $this->loadSearchPageData($data);
		$data = $this->get_sample_list($data);

		return $RES->write( $this->render('sample/main.php', $data) );

	}

	/**
	 *
	 */
	function get_sample_list($data)
	{
		$dbc = $this->_container->DBC_User;
		if (empty($dbc)) {
			_exit_text('Invalid Session [CSH-019]', 500);
		}

		// Calculate Offset
		$sql_limit = 100;
		$sql_offset = $this->getPageOffset($sql_limit);

		// Base Query
		$sql = <<<SQL
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
		{ORDER_BY}
		OFFSET $sql_offset
		LIMIT $sql_limit
		SQL;

		// License Filter
		$sql_filter = [];
		$sql_filter[] = [
			'sql' => 'lab_sample.license_id = :l0',
			'arg' => [
				':l0' => $_SESSION['License']['id']
			]
		] ;

		// A General Search Query
		if ( ! empty($_GET['q'])) {
			$_GET['q'] = trim($_GET['q']);
			// OR license.name ILIKE :q83 OR license.code ILIKE :q83)');
			$sql_filter[] = [
				'sql' => <<<SQL
				(
					lab_sample.id LIKE :q83
					OR lab_sample.name LIKE :q83
					OR lab_sample.id LIKE :q83
					OR lab_sample.name LIKE :q83
					OR inventory.guid LIKE :q83
					OR product.name LIKE :q83
					OR variety.name LIKE :q83
				)
				SQL,
				'arg' => [
					':q83' => sprintf('%%%s%%', $_GET['q'])
				]
			];
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

		// Where Filter Merge
		$arg = [];
		$tmp = [];
		foreach ($sql_filter as $i => $f) {
			$tmp[] = $f['sql'];
			$arg = array_merge($arg, $f['arg']);
		}
		$tmp = implode(' AND ', $tmp);
		$sql = str_replace('{WHERE}', sprintf('WHERE %s', $tmp), $sql);

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
			if (empty($_GET['sort-dir'])) {
				$_GET['sort-dir'] = 'desc';
			}
			break;
		}
		$sql_order = sprintf($sql_order, ($_GET['sort-dir'] == 'desc' ? 'desc' : 'asc'));
		$sql = str_replace('{ORDER_BY}', $sql_order, $sql);

		// Execute
		$sample_list = $dbc->fetchAll($sql, $arg);
		array_walk($sample_list, function(&$v, $k) {
			$v['meta'] = json_decode($v['meta'], true);
		});

		$data['sample_list'] = $sample_list;

		// Get Matching Record Counts
		$Pager = $this->convertSearchToPager($dbc, $sql, $arg, $_GET['p'], $sql_limit);

		$data['page_list_html'] = $Pager->getHTML();
		$data['page_older'] = max(1, intval($_GET['p']) - 1);
		$data['page_newer'] = intval($_GET['p']) + 1;

		return $data;
	}

	/**
	 *
	 */
	protected function loadSampleStat(array $data) : array
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
