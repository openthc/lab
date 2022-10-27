<?php
/**
 * Create a Lab Result
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Result;

use Edoceo\Radix\Session;

use OpenTHC\Lab\Lab_Result;
use OpenTHC\Lab\Lab_Sample;

class Create extends \OpenTHC\Lab\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->loadSiteData();
		$data['Page'] = array('title' => 'Result :: Create');

		$dbc = $this->_container->DBC_User;

		// @todo should be License ID
		$sql = 'SELECT * FROM lab_sample WHERE license_id = :l0 AND id = :g0';
		$arg = [
			':l0' => $_SESSION['License']['id'],
			':g0' => $_GET['sample_id'],
		];
		$chk = $dbc->fetchRow($sql, $arg);
		if (empty($chk['id'])) {
			__exit_text('Invalid Sample [CRC-022]', 400);
		}

		$meta = \json_decode($chk['meta'], true);

		$data['Lab_Sample'] = $chk;
		$data['License_Source'] = $dbc->fetchRow('SELECT * FROM license WHERE id = :ls0', [ $chk['license_id_source'] ]);
		$data['Lot'] = $dbc->fetchRow('SELECT * FROM inventory WHERE id = :i', [ ':i' => $chk['lot_id'] ]);
		$data['Lot_Meta'] = json_decode($data['Lot']['meta'], true);
		$data['Product'] = $dbc->fetchRow('SELECT * FROM product WHERE id = :p', [ ':p' => $data['Lot']['product_id'] ]); // $meta['Product'];
		$data['Product_Type'] = $dbc->fetchRow('SELECT * FROM product_type WHERE id = :p', [ ':p' => $data['Product']['product_type_id'] ]);
		$data['Variety'] = $dbc->fetchRow('SELECT * FROM variety WHERE id = :v', [ ':v' => $data['Lot']['variety_id'] ]);

		$data['lab_metric_list'] = $dbc->fetchAll('SELECT * FROM lab_metric WHERE stat = 200 ORDER BY sort, type, name');
		$data['lab_metric_section_list'] = $dbc->fetchAll('SELECT * FROM lab_metric_type ORDER BY sort, stub');


		// Get authoriative lab metrics
		$sql = 'SELECT * FROM lab_metric WHERE stat = 200 ORDER BY sort, type, name';
		$metricTab = $dbc->fetchAll($sql);

		$MetricList = array(); // This list is organized by the metric's type. I need it to make render the view eaiser.
		// I could have made it type-flat and made the view branch on the incorrect type. I think this would have made
		// it more difficult to refactor this for other RCEs.
		foreach ($data['lab_metric_list'] as $metric) {

			$metric['meta'] = json_decode($metric['meta'], true);

			$type = $metric['type'];
			$key = $metric['id'];

			if (empty($metric['meta']['uom'])) {
				$metric['meta']['uom'] = 'pct';
			}

			// Add metric to it's type list, in the Metric List
			if (empty($MetricList[$type])) $MetricList[$type] = array();

			$MetricList[$type][$key] = $metric;
		}

		$data['Lab_Result_Metric_list'] = $MetricList;

		return $RES->write( $this->render('result/create.php', $data) );

	}

	/**
	 * [save description]
	 * @param [type] $REQ [description]
	 * @param [type] $RES [description]
	 * @param [type] $ARG [description]
	 * @return [type] [description]
	 */
	function save($REQ, $RES, $ARG)
	{
		switch ($_POST['a']) {
		case 'commit':
			// return $this->_commit($REQ, $RES, $ARG);
			// require_once(__DIR__ . '/Create_LeafData.php');
			// $x = new \OpenTHC\Lab\Controller\Result\Create_LeafData($this->_container);
			// return $x->_commit($REQ, $RES, $ARG);
		case 'save':
		case 'lab-result-save':
		case 'lab-result-save-and-commit':
			return $this->_save($REQ, $RES, $ARG);
		default:
			return $RES->withStatus(400);
		}
	}

}
