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

	/**
	 * Save the Lab Result Data
	 */
	private function _save($REQ, $RES, $ARG)
	{
		$dbc = $this->_container->DBC_User;

		// Get and validate the QA Sample
		$sql = 'SELECT * from lab_sample WHERE id = :id AND license_id = :lic';
		$arg = [
			':id' => $_POST['sample_id'],
			':lic' => $_SESSION['License']['id'],
		];
		$Sample = $dbc->fetchRow($sql, $arg);
		if (empty($Sample['id'])) {
			_exit_text(sprintf('Could not find Sample Lot: %s [LPC-120]', $_POST['sample_id']), 409);
		}

		// Get the authorative lab metrics
		// This list is type-flat, and it's IDs the row ULID
		$sql = "SELECT *, meta->>'uom' AS uom FROM lab_metric";
		$res_lab_metric = $dbc->fetchAll($sql);

		$dbc->query('BEGIN');

		$LR = new Lab_Result($dbc);
		$LR['id'] = _ulid();
		$LR['guid'] = substr($LR['id'], 0, 16);
		$LR['license_id'] = $_SESSION['License']['id'];
		$LR['lab_sample_id'] = $Sample['id'];
		$LR['stat'] = $_POST['lab-result-stat'];
		$LR['flag'] = 0;
		// $LR['type'] = 'unknown';
		// $LR['name'] = sprintf('Lab Result for Sample Lot: %s', $Sample['id']);
		$LR['uom'] = 'g';
		$lr_meta = [
			'@context' => 'https://openthc.org/v2018/post',
			'@version' => '',
			'@source' => $_POST,
			'lab_metric_type_list' => [],
		];
		foreach ($_POST as $k => $v) {
			if (preg_match('/lab\-metric\-type\-(\w+)\-stat$/', $k, $m)) {
				$lmt_id = $m[1];
				$lr_meta['lab_metric_type_list'][ $lmt_id ] = [
					'id' => $lmt_id,
					'name' => '',
					'stat' => $v,
				];
			}
		}
		$LR['meta'] = json_encode($lr_meta);
		$LR['hash'] = $LR->getHash();
		if ('lab-result-save-and-commit' == $_POST['a']) {
			$LR->setFlag(Lab_Result::FLAG_LOCK);
		}
		$LR->save('Lab_Result/Create');

		// Save Metrics
		foreach ($res_lab_metric as $m) {

			$k = sprintf('lab-metric-%s', $m['id']);

			$qom = trim($_POST[$k]);
			if (strlen($qom) == 0) {
				continue;
			}

			switch ($qom) {
				case 'N/A':
					$qom = -1;
					break;
				case 'N/D':
					$qom = -2;
					break;
				case 'N/T':
					$qom = -3;
					break;
			}

			$dbc->insert('lab_result_metric', [
				'id' => _ulid(),
				'lab_result_id' => $LR['id'],
				'lab_metric_id' => $m['id'],
				'qom' => floatval($qom),
				'uom' => $m['uom'],
				// 'lod' => $m['meta']['lod'],
				// 'loq' => $m['meta']['loq'],
			]);


			// Special Case Lab Metrics for Up-Scaling to the Lab Result
			switch ($m['id']) {
				case '018NY6XC00PXG4PH0TXS014VVW': // total-thc
				case '018NY6XC00LM49CV7QP9KM9QH9': // d9-thc
				case '018NY6XC00LM877GAKMFPK7BMC': // d8-thc
				case '018NY6XC00LMB0JPRM2SF8F9F2': // thca
					$thc_list[] = $lrm1['qom'];
					$thc_uom = $m['uom'];
					break;
				case '018NY6XC00DEEZ41QBXR2E3T97': // total-cbd
				case '018NY6XC00LMK7KHD3HPW0Y90N': // cbd
				case '018NY6XC00LMENDHEH2Y32X903': // cbda
					$cbd_list[] = $lrm1['qom'];
					$cbd_uom = $m['uom'];
					break;
			}

		}

		$LR['cbd'] = max($cbd_list);
		$LR['thc'] = max($thc_list);
		$LR->save();

		// Update Lab Sample?
		$LS = new Lab_Sample($dbc, $LR['lab_sample_id']);
		// $LS->delFlag();
		// $LS->setFlag();
		// $LS['qty'] = 0;
		$LS['stat'] = Lab_Sample::STAT_DONE;
		// $LS['qty'] = 0; // Configure to Zero-Out on Complete?
		$LS->save('Lab_Sample/Update');
		// $LS->setFlag();
		// $LS['stat'] =
		// $LS->save('Lab_Sample/Create by User');

		// Link
		// v1 lab_result_inventory
		$sql = 'INSERT INTO lab_result_inventory (inventory_id, lab_result_id) VALUES (:i0, :lr0) ON CONFLICT DO NOTHING';
		$res = $dbc->query($sql, [
			':i0' => $Sample['lot_id'],
			':lr0' => $LR['id']
		]);

		// v0 inventory_lab_result
		$sql = 'INSERT INTO inventory_lab_result (lot_id, lab_result_id) VALUES (:i0, :lr0) ON CONFLICT DO NOTHING';
		$res = $dbc->query($sql, [
			':i0' => $Sample['lot_id'],
			':lr0' => $LR['id']
		]);


		// $IL = $Sample['lot_id'];
		// $IL->delFlag();
		// $IL->setFlag();
		// $IL->save();

		// $cbd_uom?
		switch ($thc_uom) {
		case 'pct':
			$cbd = sprintf('%0.2F%%', $LR['cbd']);
			$thc = sprintf('%0.2F%%', $LR['thc']);
			break;
		default:
			$cbd = sprintf('%0.2F %s', $LR['cbd'], $cbd_uom);
			$thc = sprintf('%0.2F %s', $LR['thc'], $thc_uom);
		}

		// Link
		// v1 lab_result_inventory
		$sql = 'INSERT INTO lab_result_inventory (inventory_id, lab_result_id) VALUES (:i0, :lr0) ON CONFLICT DO NOTHING';
		$res = $dbc->query($sql, [
			':i0' => $Sample['lot_id'],
			':lr0' => $LR['id']
		]);

		// v0 inventory_lab_result
		$sql = 'INSERT INTO inventory_lab_result (lot_id, lab_result_id) VALUES (:i0, :lr0) ON CONFLICT DO NOTHING';
		$res = $dbc->query($sql, [
			':i0' => $Sample['lot_id'],
			':lr0' => $LR['id']
		]);

		$sql = 'UPDATE inventory SET flag = flag | :f1, qa_cbd = :c1, qa_thc = :t1 WHERE id = :i0';
		$arg = [
			':i0' => $Sample['lot_id'],
			':lr1' => $LR['id'], // v1 inventory.lab_result_id
			':f1' => 0x00000400, // Inventory::FLAG_QA_PASS,
			':c1' => $cbd,
			':t1' => $thc,
		];

		// $I->save('Lot/Lab_Result/Link');

		$dbc->query('COMMIT');

		return $RES->withRedirect(sprintf('/result/%s', $LR['id']));

	}

}
