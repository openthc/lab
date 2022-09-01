<?php
/**
 * Only works if you OWN the Sample
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Sample;

use Edoceo\Radix\Session;

use OpenTHC\Lab\Lab_Result;
use OpenTHC\Lab\Lab_Sample;


class View extends \OpenTHC\Lab\Controller\Base
{
	private $cre;

	function __invoke($REQ, $RES, $ARG)
	{
		if (empty($ARG['id'])) {
			_exit_html_fail('Invalid Request', 400);
		}

		$dbc = $this->_container->DBC_User;

		$Lab_Sample = new Lab_Sample($dbc, $ARG['id']);
		if (empty($Lab_Sample['id'])) {
			_exit_html_fail('Invalid Lab Sample [CSV-026]', 400);
		}

		switch ($_POST['a']) {
			case 'done':
				return $this->_finishSample($RES, $ARG, $Lab_Sample);
				break;
			case 'drop':
				// need to return the $RES object from these methods to do anything
				return $this->_dropSample($RES, $ARG, $Lab_Sample);
				break;
			case 'accept-sample':
				return $this->_accept($RES, $Lab_Sample);
				break;
			case 'lab-report-create':
				return $this->_createReport($RES, $Lab_Sample);
				break;
			case 'save':
				return $this->_saveSample($RES, $Lab_Sample);
				break;
			case 'void':
				return $this->_voidSample($RES, $ARG, $Lab_Sample);
				break;
		}

		$Lot = $dbc->fetchRow('SELECT * FROM inventory WHERE id = ?', [ $Lab_Sample['lot_id'] ]);
		$Product = $dbc->fetchRow('SELECT * FROM product WHERE id = ?', [ $Lot['product_id'] ]);
		$ProductType = $dbc->fetchRow('SELECT * FROM product_type WHERE id = ?', [ $Product['product_type_id'] ]);
		$Variety = $dbc->fetchRow('SELECT * FROM variety WHERE id = ?', [ $Lot['variety_id'] ]);

		$Lab_Result_list = $dbc->fetchAll('SELECT * FROM lab_result WHERE lab_sample_id = :ls0', [
			':ls0' => $Lab_Sample['id']
		]);

		$Lab_Report_list = $dbc->fetchAll('SELECT * FROM lab_report WHERE lab_sample_id = :ls0', [
			':ls0' => $Lab_Sample['id']
		]);

		$Lab_Sample_Meta = json_decode($Lab_Sample['meta'], true);

		// Get Fresh Lot Data?
		// $res = $this->cre->get('/lot/' . $Lab_Sample['id']);

		//$res = $this->cre->get('/config/product/' . $QAS['global_inventory_type_id']);
		//$P = $res['result'];
		//var_dump($P);

		// Find Laboratory License
		//$res = $this->cre->get('/config/license/' . $QAS['global_created_by_mme_id']);
		//$L_Lab = $res['result'];

		// Find Owner License
		$L_Source = $dbc->fetchRow('SELECT id, name, code, guid, email, phone FROM license WHERE id = :l0', [
			':l0' => $Lab_Sample['license_id_source']
		]);

		$data = $this->loadSiteData([
			'Page' => array('title' => 'Sample :: View'),
			'Lab_Sample' => $Lab_Sample->toArray(),
			'Lab_Sample_meta' => $Lab_Sample_Meta,
			'Lab_Result_list' => $Lab_Result_list,
			'Lab_Report_list' => $Lab_Report_list,
			'Lot' => $Lot,
			'Product' => $Product,
			'ProductType' => $ProductType,
			'Variety' => $Variety,
			'Source_License' => $L_Source,
			'License_Source' => $L_Source,
		]);

		// Nicely Formatted ID
		$data['Lab_Sample']['id_nice'] = $data['Lab_Sample']['name'] ?: $data['Lab_Sample']['id'];
		if (empty($data['Product']['uom'])) {
			$data['Product']['uom'] = 'g';
		}

		// Sample Image
		$data['Lab_Sample']['img_list'] = $Lab_Sample->getFiles();
		if (count($data['Lab_Sample']['img_list'])) {
			$img = $data['Lab_Sample']['img_list'][0];
			$ext = (preg_match('/\.(\w+)$/', $img, $m) ? $m[1] : 'jpeg');
			$data['Lab_Sample']['img_link'] = sprintf('/sample/%s.%s', $Lab_Sample['id'], $ext);
		}

		return $RES->write( $this->render('sample/single.php', $data) );

	}

	/**
	 *
	 */
	function image($REQ, $RES, $ARG)
	{
		if (empty($ARG['id'])) {
			_exit_html_fail('Invalid Request [CSV-124]', 400);
		}

		$dbc = $this->_container->DBC_User;

		$Lab_Sample = new Lab_Sample($dbc, $ARG['id']);
		if (empty($Lab_Sample['id'])) {
			__exit_text('Invalid Lab Sample [CSV-131]', 404);
		}

		$img_list = $Lab_Sample->getFiles();
		if (empty($img_list)) {
			__exit_text('Lab Sample Image Not Found [CSV-136]', 404);
		}

		$img = $img_list[0];
		$ext = (preg_match('/\.(\w+)$/', $img, $m) ? $m[1] : 'jpeg');

		switch ($ext) {
			case 'jpeg':
				header('content-type: image/jpeg');
				break;
			case 'png':
				header('content-type: image/png');
				break;
		}

		readfile($img);

		exit(0);

	}

	/**
	 *
	 */
	function _accept($RES, $Lab_Sample)
	{
		$dbc = $this->_container->DBC_User;

		$cfg = $dbc->fetchOne("SELECT val FROM base_option WHERE key = 'lab-sample-seq-format'");
		$cfg = json_decode($cfg);

		if (!empty($cfg)) {

			$dt0 = new \DateTime();

			$dtz = $dbc->fetchOne("SELECT val FROM base_option WHERE key = 'timezone'");
			if ($dtz) {
				// Assign Sequence
				$dt0->setTimeZone(new \DateTimeZone($dtz));
			}

			$cfg = str_replace('{YYYY}', $dt0->format('Y'), $cfg);
			$cfg = str_replace('{YY}', $dt0->format('y'), $cfg);
			$cfg = str_replace('{MM}', $dt0->format('m'), $cfg);
			$cfg = str_replace('{MA}', chr(64 + $dt0->format('m')), $cfg);
			$cfg = str_replace('{DDD}', sprintf('%03d', $dt0->format('z') + 1), $cfg);
			$cfg = str_replace('{DD}', $dt0->format('d'), $cfg);
			$cfg = str_replace('{HH}', $dt0->format('H'), $cfg);
			$cfg = str_replace('{II}', $dt0->format('i'), $cfg);
			$cfg = str_replace('{SS}', $dt0->format('s'), $cfg);

			if (preg_match_all('/(\{SEQ\w+?\})/', $cfg, $m)) {
				$seq_list = $m[1];
				foreach ($seq_list as $seq) {

					$fmt = '%d';

					$len = preg_match('/(\d+)\}$/', $seq, $m) ? $m[1] : 0;
					if (!empty($len)) {
						$fmt = sprintf('%%0%dd', $len);
					}

					$seq_mode = preg_match('/_(G|Y|Q|M|D)/', $seq, $m) ? $m[1] : 'G';

					$seq_name = sprintf('seq_%s_%s', $_SESSION['Company']['id'], $seq_mode);
					$seq_name = strtolower($seq_name);
					$val = $dbc->fetchOne('SELECT nextval(:s)', [':s' => $seq_name ]);

					$rep = sprintf($fmt, $val);

					$cfg = str_replace($seq, $rep, $cfg);
				}
			}

			$Lab_Sample['name'] = $cfg;

		}

		$Lab_Sample['stat'] = Lab_Sample::STAT_LIVE;
		$Lab_Sample->save();

		Session::flash('Sample Accepted, Internal ID Assigned');

		return $RES->withRedirect(sprintf('/sample/%s', $Lab_Sample['id']));

	}

	/**
	 * Create a Report from 1+ Result
	 */
	function _createReport($RES, $Lab_Sample)
	{
		$dbc = $this->_container->DBC_User;

		// Collect All the Metrics
		$lab_metric_list = [];
		$lab_metric_type_list = [];
		foreach ($_POST['lab-result'] as $x) {

			$LR0 = new Lab_Result($dbc, $x);
			$lrm_list = $LR0->getMetrics();

			$sql = <<<SQL
			SELECT lab_result_metric.*
			  , lab_metric.lab_metric_type_id
			  , lab_metric.sort
			FROM lab_result_metric
			JOIN lab_metric ON lab_result_metric.lab_metric_id = lab_metric.id
			WHERE lab_result_metric.lab_result_id = :lr0
			SQL;
			$res = $dbc->fetchAll($sql, [
				':lr0' => $x
			]);
			foreach ($res as $lrm) {
				$lab_metric_list[ $lrm['lab_metric_id'] ] = $lrm;
				// $lab_metric_type_list[ $lrm['lab_metric_type_id'] ][]
			}
		}

		// Create a Lab Report
		$lr1 = [];
		$lr1['id'] = _ulid();
		$lr1['lab_sample_id'] = $Lab_Sample['id'];
		// $lr1['contact_id'] = $_SESSION['Contact']['id'];
		$lr1['license_id'] = $_SESSION['License']['id'];
		$lr1['approved_at'] = $LR0['approved_at'];
		$lr1['expires_at'] = $LR0['expires_at'];
		$lr1['license_id_source'] = $_SESSION['License']['id'];
		$lr1['license_id_client'] = $Lab_Sample['license_id_source'];
		$lr1['name'] = sprintf('Lab Report for Sample %s', $Lab_Sample['name']);
		$lr1['meta'] = json_encode([
			'lab_report_list' => $_POST['lab-result'], // @deprecated
			'lab_result_list' => $_POST['lab-result'],
			'lab_metric_list' => $lab_metric_list, // @deprecated
			'lab_result_metric_list' => $lab_metric_list,
		]);

		$dbc->insert('lab_report', $lr1);

		// If QBench Source (or other external source)
		// _qbench_sample_report_import($dbc, $qbc, $rec);

		return $RES->withRedirect(sprintf('/report/%s', $lr1['id']));

	}

	/**
	 *
	 */
	function _finishSample($RES, $ARG)
	{
		$dbc = $this->_container->DBC_User;

		// $sql = 'SELECT * from lab_sample where id = :pk';
		// $res = $dbc->fetchAll($sql, [
		// 	':pk' => $ARG['id'],
		// ]);

		$sql = 'UPDATE lab_sample SET stat = :s1, flag = flag | :f1 WHERE license_id = :l0 AND id = :pk';
		$arg = array(
			':pk' => $ARG['id'],
			':l0' => $_SESSION['License']['id'],
			':s1' => Lab_Sample::STAT_DONE,
			':f1' => Lab_Sample::FLAG_DONE,
		);
		$res = $dbc->query($sql, $arg);

		return $RES->withRedirect('/sample');
	}

	/**
	 *
	 */
	function _dropSample($RES, $ARG)
	{
		\session_write_close();

		// $res = $this->cre->get('/lot?source=true');
		// $res = $this->cre->delete('/lot/' . $ARG['id']);
		$dbc_user = $this->_container->DBC_User;

		// $sql = 'SELECT * from lab_sample where id = :pk';
		// $res = $dbc_user->fetchAll($sql, [
		// 	':pk' => $ARG['id'],
		// ]);

		$sql = 'DELETE FROM lab_sample WHERE id = :pk';
		$res = $dbc_user->query($sql, [
			':pk' => $ARG['id'],
		]);

		// $sql = 'UPDATE lab_sample SET stat = :s1, flag = flag | :f1 WHERE license_id = :l0 AND id = :pk';
		// $arg = array(
		// 	':pk' => $ARG['id'],
		// 	':l0' => $_SESSION['License']['id'],
		// 	':s1' => Lab_Sample::STAT_VOID,
		// 	':f1' => Lab_Sample::FLAG_DEAD,
		// );
		// $res = $dbc_user->query($sql, $arg);

		// $sql = 'DELETE FROM lab_sample WHERE id = :pk';
		// $res = $dbc_user->query($sql, [ ':pk' => $ARG['id'] ]);

		return $RES->withRedirect('/sample');
	}

	/**
	 *
	 */
	function _saveSample($RES, $Lab_Sample)
	{
		$dbc = $this->_container->DBC_User;

		$_POST['product-name'] = trim($_POST['product-name']);
		$_POST['variety-name'] = trim($_POST['variety-name']);

		// Product Change?
		// if ($_POST['product-id'] != $Lab_Sample['product_id']) {
		// 	if (!empty($_POST['product-name'])) {
		// 		$arg = [];
		// 		$arg[':p0'] = $_POST['product-name'];
		// 		$PR = $dbc->fetchRow('SELECT id FROM product WHERE name = :p0', $arg);
		// 		if (!empty($PR['id'])) {
		// 			$Lab_Sample['product_id'] = $PR['id'];
		// 		} else {
		// 			$PR = [];
		// 			$PR['id'] = _ulid();
		// 			$PR['guid'] = $PR['id'];
		// 			$PR['license_id'] = $Lab_Sample['license_id'];
		// 			$PR['product_type_id'] = 117; // Flower
		// 			$PR['name'] = $_POST['product-name'];
		// 			$PR['stub'] = _text_stub($PR['name']);
		// 			$dbc->insert('product', $PR);
		// 			$Lab_Sample['product_id'] = $PR['id'];
		// 		}
		// 	}
		// }

		// Variety Change?
		// if (!empty($_POST['variety-id'])) {
		// 	$arg = [];
		// 	$arg[':v0'] = $_POST['variety-id'];
		// 	$VT = $dbc->fetchRow('SELECT id FROM variety WHERE id = :v0', $arg);
		// 	if (empty($VT['id'])) {
		// 		$VT = [
		// 			'id' => $_POST['variety-id'],
		// 			'license_id' => $_SESSION['License']['id'],
		// 			'guid' => $_POST['variety-id'],
		// 			'name' => $_POST['variety-name'],
		// 		];
		// 		$dbc->insert('variety', $VT);
		// 	}
		// 	if (!empty($VT['id'])) {
		// 		$Lab_Sample['variety_id'] = $VT['id'];
		// 	}
		// }

		if ( ! empty($_POST['sample-qty'])) {
			$Lab_Sample['qty'] = floatval($_POST['sample-qty']);
		}

		if ( ! empty($_POST['source-lot-id'])) {
			$m = json_decode($Lab_Sample['meta'], true);
			$m['Lot_Source']['id'] = $_POST['source-lot-id'];
			$Lab_Sample['meta'] = json_encode($m);
		}

		if ( ! empty($_POST['source-license-id'])) {
			$Lab_Sample['license_id_source'] = $_POST['source-license-id'];
		}

		// Check for copy of this license record
		$L0 = $dbc->fetchRow('SELECT id, name FROM license WHERE id = :l0', [
			':l0' => $Lab_Sample['license_id_source']
		]);
		// If not found, get from Directory and add
		if (empty($L0['id'])) {

			$dir = new \OpenTHC\Service\OpenTHC('dir');
			$res = $dir->get(sprintf('/api/license/%s', $Lab_Sample['license_id_source']));

			$dbc->insert('license', [
				'id' => $res['data']['id'],
				'name' => $res['data']['name'],
				'code' => $res['data']['code'],
				'guid' => $res['data']['guid'],
				'type' => $res['data']['type'],
				'hash' => '-',
			]);

		}

		$Lab_Sample['name'] = trim($_POST['lab-sample-name']);
		$Lab_Sample->save();

		if ( ! empty($_FILES['sample-file'])) {
			$f0 = $_FILES['sample-file'];
			if (!empty($f0['size']) && !empty($f0['tmp_name']) && (0 == $f0['error']) ) {
				switch ($f0['type']) {
					case 'image/jpeg':
					case 'image/png':
						$output_type = basename($f0['type']);
						$output_file = sprintf('%s/var/%s/sample/%s.%s', APP_ROOT, $_SESSION['Company']['id'], $Lab_Sample['id'], $output_type);
						$output_path = dirname($output_file);
						if ( ! is_dir($output_path)) {
							mkdir($output_path, 0755, true);
						}
						move_uploaded_file($f0['tmp_name'], $output_file);
						break;
					default:
						// Session::flash();
				}
			} else {
				// Error
				// print_r($f0);
				// throw new \Exception(sprintf('File Upload Error "%d"', $f0['error']));
				// Session::flash();
			}

		}

		return $RES->withRedirect(sprintf('/sample/%s', $Lab_Sample['id']));

	}

	/**
	 * Voids the Sample
	 */
	function _voidSample($RES, $ARG)
	{
		$dbc = $this->_container->DBC_User;

		$sql = 'UPDATE lab_sample SET stat = :s1, flag = flag | :f1 WHERE license_id = :l0 AND id = :pk';
		$arg = array(
			':pk' => $ARG['id'],
			':l0' => $_SESSION['License']['id'],
			':s1' => Lab_Sample::STAT_VOID,
			':f1' => Lab_Sample::FLAG_VOID,
		);
		$res = $dbc->query($sql, $arg);

		return $RES->withRedirect('/sample');
	}

}
