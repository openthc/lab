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
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		$dbc_user = $this->_container->DBC_User;

		$LR = new Lab_Result($dbc_user, $ARG['id']);
		if (empty($LR['id'])) {
			_exit_text('Invalid Lab Result [CRU-075]', 400);
		}

		switch ($_POST['a']) {
			case 'lab-result-delete':

				$arg = [
					':lr0' => $LR['id']
				];

				$dbc_user->query('BEGIN');

				$dbc_user->query('DELETE FROM inventory_lab_result WHERE lab_result_id = :lr0', $arg);
				$dbc_user->query('DELETE FROM lab_result_metric WHERE lab_result_id = :lr0', $arg);
				$dbc_user->query('DELETE FROM lab_result WHERE id = :lr0', $arg);

				// Reset Flags on Sample
				$LS = new Lab_Sample($dbc_user, $LR['lab_sample_id']);

				// Reset Flags on Inventory?
				// $IY = new Inventory($dbc_user, $LS['inventory_id']);

				// Alert

				$dbc_user->query('COMMIT');

				return $RES->withRedirect('/result');

				break;

			case 'lab-result-commit':
			case 'lab-result-save':

				$sql = "SELECT *, meta->>'uom' AS uom FROM lab_metric";
				$res_lab_metric = $dbc_user->fetchAll($sql);

				$cbd_list = [];
				$thc_list = [];

				$dbc_user->query('BEGIN');

				foreach ($res_lab_metric as $m) {

					// $lrm = new Lab_Result_Metric($dbc_user, [
					// 	'lab_result_id' => $LR['id'],
					// 	'lab_metric_id' => $m['id']
					// ]);

					$k = sprintf('lab-metric-%s', $m['id']);

					$qom = trim($_POST[$k]);
					if (strlen($qom) == 0) {
						continue;
					}

					$uom = $_POST[sprintf('lab-metric-%s-uom', $m['id'])] ?: $m['uom'];

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

				if ( ! empty($_FILES['file'])) {
					if (0 == $_FILES['file']['error']) {
						$LR->setCOAFile($_FILES['file']['tmp_name']);
					}
				}

				if ('lab-result-commit' == $_POST['a']) {
					$LR->setFlag(Lab_Result::FLAG_LOCK);
				}

				$LR->save('Lab/Result/Update by User');

				// Find Sample
				// $LS = new Lab_Sample($LR['lab_sample_id']);
				// $I = new Inventory($LS['inventory_id']);
				// $LR->bindToInventory($I);
				// $I->bindToLabResult($LR);

				$dbc_user->query('COMMIT');

				// Publish
				$subC = new \App\Controller\API\Pub($this->_container);
				$subR = $subC->_lab_result_publish($RES, $dbc_user, $LR);

				return $RES->withRedirect(sprintf('/result/%s', $LR['id']));

		}

	}
}
