<?php
/**
 * Update a Lab Result
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Result;

use Edoceo\Radix\Session;

use OpenTHC\Lab\Lab_Sample;
use OpenTHC\Lab\Lab_Result;

class Update extends \OpenTHC\Lab\Controller\Result\View
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$id = $ARG['id'];
		if (empty($id)) {
			_exit_text('Invalid Request [CRU-021]', 400);
		}

		$dbc = $this->_container->DBC_User;

		// $chk = $dbc_user->fetchRow('SELECT * FROM lab_result WHERE (id = :lr0 OR guid = :lr0)', [ ':lr0' => $id ]);
		$Lab_Result = new Lab_Result($dbc, $id);
		if (empty($Lab_Result['id'])) {
			_exit_text('Lab Result Not Found [CRU-034]', 400);
		}

		$data = $this->load_lab_result_full($id);

		$data = $this->loadSiteData($data);
		$data['Page']['title'] = sprintf('Result :: %s :: Update', $data['Lab_Result']['guid']);

		$data['Lab_Result']['coa_file'] = $data['Lab_Result']->getCOAFile();
		if (!is_file($data['Lab_Result']['coa_file'])) {
			$data['Lab_Result']['coa_file'] = null;
		}

		$data['Result_Metric_Group_list'] = $data['Lab_Result']->getMetrics_Grouped();

		return $RES->write( $this->render('result/update.php', $data) );

	}

	/**
	 * Lab Result POST Handler
	 */
	function post($REQ, $RES, $ARG)
	{
		$dbc_user = $this->_container->DBC_User;

		$sql = "SELECT *, meta->>'uom' AS uom FROM lab_metric";
		$res_lab_metric = $dbc_user->fetchAll($sql);

		$dbc_user->query('BEGIN');

		$LR = new Lab_Result($dbc_user, $ARG['id']);

		switch ($_POST['a']) {
			case 'lab-result-delete':

				return $this->_delete($RES, $LR, $dbc_user);

				break;

			case 'lab-result-commit':
			case 'lab-result-save':
			case 'lab-result-save-and-commit':

				if (empty($LR['id'])) {
					$LR['id'] = _ulid();
					$LR['guid'] = substr($LR['id'], 0, 16);
					// $LR['name'] = sprintf('Lab Result for Sample Lot: %s', $Sample['id']);
					$LR['license_id'] = $_SESSION['License']['id'];
					$LR['stat'] = $_POST['lab-result-stat'];
					$LR['flag'] = 0;
					$LR['hash'] = '-';
				}

				// Assign Sample
				if (empty($LR['lab_sample_id'])) {
					// Get and validate the QA Sample
					$sql = 'SELECT * from lab_sample WHERE id = :id AND license_id = :lic';
					$arg = [
						':id' => $_POST['sample_id'],
						':lic' => $_SESSION['License']['id'],
					];
					$Sample = $dbc_user->fetchRow($sql, $arg);
					if (empty($Sample['id'])) {
						_exit_text(sprintf('Could not find Sample Lot: %s [LPC-120]', $_POST['sample_id']), 409);
					}
					$LR['lab_sample_id'] = $Sample['id'];
				}

				// Get the authorative lab metrics
				// This list is type-flat, and it's IDs the row ULID
				$sql = "SELECT *, meta->>'uom' AS uom FROM lab_metric";
				$res_lab_metric = $dbc_user->fetchAll($sql);

				$cbd_list = [];
				$thc_list = [];

				$dbc_user->query('BEGIN');

				$lr0_meta = $LR->getMeta();
				if (empty($lr0_meta['@context'])) {
					$lr0_meta['@context'] = 'https://openthc.org/v2018/post';
				}
				if (empty($lr0_meta['@version'])) {
					$lr0_meta['@version'] = '420.18.0';
				}

				// Section Status
				foreach ($_POST as $k => $v) {
					if (preg_match('/lab\-metric\-type\-(\w+)\-stat$/', $k, $m)) {
						$lmt_id = $m[1];
						$lr0_meta['lab_metric_type_list'][ $lmt_id ] = [
							'id' => $lmt_id,
							'name' => '',
							'stat' => $v,
						];
					}
				}

				// Save Lab Result
				$LR['meta'] = json_encode($lr0_meta);
				$LR->save();

				// Metric Values
				foreach ($res_lab_metric as $m) {

					// $lrm = new Lab_Result_Metric($dbc_user, [
					// 	'lab_result_id' => $LR['id'],
					// 	'lab_metric_id' => $m['id']
					// ]);

					$qom = trim($_POST[sprintf('lab-metric-%s', $m['id'])]);
					$uom = $_POST[sprintf('lab-metric-%s-uom', $m['id'])] ?: $m['uom'];

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

					$sql = <<<SQL
					INSERT INTO lab_result_metric (lab_result_id, lab_metric_id, qom, uom)
					VALUES (:lr0, :lm0, :q0, :u0)
					ON CONFLICT (lab_result_id, lab_metric_id)
					DO UPDATE SET qom = :q0, uom = :u0
					SQL;

					//
					$dbc_user->query($sql, [
						// 'id' => _ulid(),
						':lr0' => $LR['id'],
						':lm0' => $m['id'],
						':q0' => floatval($qom),
						':u0' => $uom,
						// 'lod' => $m['meta']['lod'],
						// 'loq' => $m['meta']['loq'],
					]);

					// Special Case Lab Metrics for Up-Scaling to the Lab Result
					switch ($m['id']) {
						case '018NY6XC00PXG4PH0TXS014VVW': // total-thc
						case '018NY6XC00LM49CV7QP9KM9QH9': // d9-thc
						case '018NY6XC00LM877GAKMFPK7BMC': // d8-thc
						case '018NY6XC00LMB0JPRM2SF8F9F2': // thca
							$thc_list[] = $qom;
							$thc_uom = $uom;
							break;
						case '018NY6XC00DEEZ41QBXR2E3T97': // total-cbd
						case '018NY6XC00LMK7KHD3HPW0Y90N': // cbd
						case '018NY6XC00LMENDHEH2Y32X903': // cbda
							$cbd_list[] = $qom;
							break;
					}
				}

				$LR['cbd'] = floatval(max($cbd_list));
				$LR['thc'] = floatval(max($thc_list));
				$LR['note'] = trim($_POST['terp-note']);
				$LR['stat'] = $_POST['lab-result-stat'];
				$LR['meta'] = json_encode($lr0_meta);

				// File Upload?
				if ( ! empty($_FILES['file'])) {
					if (0 == $_FILES['file']['error']) {
						$LR->setCOAFile($_FILES['file']['tmp_name']);
					}
				}

				// And LOCK?
				switch ($_POST['a']) {
					case 'lab-result-commit':
					case 'lab-result-save-and-commit':
						// Only Allow Commit on Specific Status?
						switch ($LR['stat']) {
							case Lab_Result::STAT_PASS:
							case Lab_Result::STAT_FAIL:
								break;
						}
						// Should LOCK promote status to DONE/PASS/FAIL?
						// $LR['stat'] = Lab_Result::STAT_PASS;
						$LR->setFlag(Lab_Result::FLAG_LOCK);
				}
				$LR['hash'] = $LR->getHash();
				// $LR->save('Lab/Result/Create by User');
				$LR->save('Lab/Result/Update by User');

				// Find Sample
				// $LS = new Lab_Sample($LR['lab_sample_id']);
				// $I = new Inventory($LS['inventory_id']);
				// $LR->bindToInventory($I);
				// $I->bindToLabResult($LR);

				// Update Lab Sample?
				$LS = new Lab_Sample($dbc_user, $LR['lab_sample_id']);
				// $LS->delFlag();
				// $LS->setFlag();
				// $LS['qty'] = 0;
				$LS['stat'] = Lab_Sample::STAT_DONE;
				// $LS['qty'] = 0; // Configure to Zero-Out on Complete?
				$LS->save('Lab_Sample/Update');
				// $LS->setFlag();
				// $LS['stat'] =
				// $LS->save('Lab_Sample/Create by User');

				$this->_save_update_inventory($dbc_user, $LS, $LR, $thc_uom);

				$dbc_user->query('COMMIT');

				// Publish
				$subC = new \OpenTHC\Lab\Controller\API\Pub($this->_container);
				$subR = $subC->_lab_result_publish($RES, $dbc_user, $LR);

				return $RES->withRedirect(sprintf('/result/%s', $LR['id']));

			case 'sync':
			case 'lab-result-sync':

				$dbc_user->query('UPDATE lab_result SET hash = :h1 WHERE id = :lr0', [
					':h1' => 'SYNC',
					':lr0' => $LR['id'],
				]);

				$dbc_user->query('COMMIT');

				Session::flash('info', _('Lab Result has been flagged for resynchronisation'));
				return $RES->withRedirect($_SERVER['HTTP_REFERER']);

				break;

		}

		__exit_text('Invalid Request [CRU-266]', 400);

	}

	/**
	 *
	 */
	function _save_update_inventory($dbc, $LS0, $LR0, $thc_uom)
	{
		$cbd = $thc = '';

		switch ($thc_uom) {
		case 'pct':
			$cbd = sprintf('%0.2F%%', $LR0['cbd']);
			$thc = sprintf('%0.2F%%', $LR0['thc']);
			break;
		default:
			$cbd = sprintf('%0.2F %s', $LR0['cbd'], $cbd_uom);
			$thc = sprintf('%0.2F %s', $LR0['thc'], $thc_uom);
		}

		// Link
		// v1 lab_result_inventory
		$sql = 'INSERT INTO lab_result_inventory (inventory_id, lab_result_id) VALUES (:i0, :lr0) ON CONFLICT DO NOTHING';
		$res = $dbc->query($sql, [
			':i0' => $LS0['lot_id'],
			':lr0' => $LR0['id']
		]);

		// v0 inventory_lab_result
		$sql = 'INSERT INTO inventory_lab_result (lot_id, lab_result_id) VALUES (:i0, :lr0) ON CONFLICT DO NOTHING';
		$res = $dbc->query($sql, [
			':i0' => $LS0['lot_id'],
			':lr0' => $LR0['id']
		]);

		$sql = 'UPDATE inventory SET flag = flag | :f1, qa_cbd = :c1, qa_thc = :t1 WHERE id = :i0';
		$arg = [
			':i0' => $LS0['lot_id'],
			':lr1' => $LR0['id'], // v1 inventory.lab_result_id
			':f1' => 0x00000400, // Inventory::FLAG_QA_PASS,
			':c1' => $LR0['cbd'],
			':t1' => $LR0['thc'],
		];

	}

	/**
	 * Delete a Lab Result
	 */
	function _delete($RES, $LR, $dbc)
	{

		$arg = [
			':lr0' => $LR['id']
		];

		$dbc->query('BEGIN');

		// v1 lab_result_inventory
		$dbc->query('DELETE FROM lab_result_inventory WHERE lab_result_id = :lr0', $arg);
		// v0 inventory_lab_result
		$dbc->query('DELETE FROM inventory_lab_result WHERE lab_result_id = :lr0', $arg);
		$dbc->query('DELETE FROM lab_result_metric WHERE lab_result_id = :lr0', $arg);
		$dbc->query('DELETE FROM lab_result WHERE id = :lr0', $arg);

		// Reset Flags on Sample
		$LS = new Lab_Sample($dbc, $LR['lab_sample_id']);

		// Reset Flags on Inventory?
		// $IY = new Inventory($dbc_user, $LS['inventory_id']);

		// Alert

		$dbc->query('COMMIT');

		$url = '/result';
		if ( ! empty($LR['lab_sample_id'])) {
			$url = sprintf('/sample/%s', $LR['lab_sample_id']);
		}

		return $RES->withRedirect($url);

	}
}
