<?php
/**
 * Update a Lab Result
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace App\Controller\Result;

use Edoceo\Radix\Session;

use App\Lab_Sample;
use App\Lab_Result;

class Update extends \App\Controller\Result\View
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

		// $data = $this->load_lab_result_full($id);

		// $chk = $dbc_user->fetchRow('SELECT * FROM lab_result WHERE (id = :lr0 OR guid = :lr0)', [ ':lr0' => $id ]);
		$Lab_Result = new Lab_Result($dbc, $id);
		if (empty($Lab_Result['id'])) {
			_exit_text('Lab Result Not Found [CRU-034]', 400);
		}

		$Lab_Sample = new Lab_Sample($dbc, $Lab_Result['lab_sample_id']);
		if (empty($Lab_Sample['id'])) {
			// _exit_text('WSHERE is SAMPLE');
		}

		$data = $this->loadSiteData($data);
		$data['Page'] = [
			'title' => 'Result :: Update'
		];

		$data['Lab_Sample'] = $Lab_Sample->toArray();
		$data['Lab_Result'] = $Lab_Result->toArray();

		$data['Lab_Result']['coa_file'] = $Lab_Result->getCOAFile();
		if (!is_file($data['Lab_Result']['coa_file'])) {
			$data['Lab_Result']['coa_file'] = null;
		}

		$data['Result_Metric_Group_list'] = $Lab_Result->getMetrics_Grouped();

		$data['Lot'] = $dbc->fetchRow('SELECT * FROM inventory WHERE id = :i0', [ ':i0' => $Lab_Sample['lot_id'] ]);
		$data['Product'] = $dbc->fetchRow('SELECT * FROM product WHERE id = ?', [ $data['Lot']['product_id'] ]);
		$data['Product_Type'] = $dbc->fetchRow('SELECT * FROM product_type WHERE id = ?', [ $data['Product']['product_type_id'] ]);
		$data['Variety'] = $dbc->fetchRow('SELECT * FROM variety WHERE id = ?', [ $data['Lot']['variety_id'] ]);

		return $RES->write( $this->render('result/update.php', $data) );

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		$dbc = $this->_container->DBC_User;

		$LR = new Lab_Result($dbc, $ARG['id']);
		if (empty($LR['id'])) {
			_exit_text('Invalid Lab Result [CRU-075]', 400);
		}

		switch ($_POST['a']) {
			case 'lab-result-delete':

				$arg = [
					':lr0' => $LR['id']
				];

				$dbc->query('BEGIN');

				$dbc->query('DELETE FROM inventory_lab_result WHERE lab_result_id = :lr0', $arg);
				$dbc->query('DELETE FROM lab_result_metric WHERE lab_result_id = :lr0', $arg);
				$dbc->query('DELETE FROM lab_result WHERE id = :lr0', $arg);

				// Reset Flags on Sample
				$LS = new Lab_Sample($dbc, $LR['lab_sample_id']);

				// Reset Flags on Inventory?
				// $IY = new Inventory($dbc, $LS['inventory_id']);

				// Alert

				$dbc->query('COMMIT');

				return $RES->withRedirect('/result');

				break;

			case 'lab-result-save':

				$sql = "SELECT *, meta->>'uom' AS uom FROM lab_metric";
				$res_lab_metric = $dbc->fetchAll($sql);

				$cbd_list = [];
				$thc_list = [];

				$dbc->query('BEGIN');

				foreach ($res_lab_metric as $m) {

					// $lrm = new Lab_Result_Metric($dbc, [
					// 	'lab_result_id' => $LR['id'],
					// 	'lab_metric_id' => $m['id']
					// ]);

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

					$sql = <<<SQL
					INSERT INTO lab_result_metric (lab_result_id, lab_metric_id, qom, uom)
					VALUES (:lr0, :lm0, :q0, :u0)
					ON CONFLICT (lab_result_id, lab_metric_id)
					DO UPDATE SET qom = :q0, uom = :u0
					SQL;
					// 					   WHERE lab_result_id = :lr0 AND lab_metric_id = :lm0
					$dbc->query($sql, [
						// 'id' => _ulid(),
						':lr0' => $LR['id'],
						':lm0' => $m['id'],
						':q0' => floatval($qom),
						':u0' => $m['uom'],
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
							break;
						case '018NY6XC00DEEZ41QBXR2E3T97': // total-cbd
						case '018NY6XC00LMK7KHD3HPW0Y90N': // cbd
						case '018NY6XC00LMENDHEH2Y32X903': // cbda
							$cbd_list[] = $lrm1['qom'];
							break;
					}
				}

				$LR['cbd'] = floatval(max($cbd_list));
				$LR['thc'] = floatval(max($thc_list));
				$LR['note'] = trim($_POST['terp-note']);
				$LR->save('Lab/Result/Update by User');

				$dbc->query('COMMIT');

				return $RES->withRedirect(sprintf('/result/%s', $LR['id']));

		}

	}
}
