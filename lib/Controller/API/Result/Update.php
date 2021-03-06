<?php
/**
 * Update a Lab Result
 */

namespace App\Controller\API\Result;

use App\Lab_Result;

class Update extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->_inputData();
		if (empty($data)) {
			return $RES->withJSON([
				'type' => 'https://api.openthc.org/e/400-invalid-content',
				'data' => null,
				'meta' => [ 'detail' => 'Request Error [ARU#017]' ],
			], 400);
		}

		if (empty($data['id'])) {
			return $RES->withJSON([
				'type' => 'https://api.openthc.org/e/400-missing-parameter',
				'data' => null,
				'meta' => [ 'detail' => 'Request Error [ARU#025]' ],
			], 400);
		}
		if (empty($data['license_id'])) {
			return $RES->withJSON([
				'type' => 'https://api.openthc.org/e/400-missing-parameter',
				'data' => null,
				'meta' => [ 'detail' => 'Request Error [ARU#032]' ],
			], 400);
		}


		$dbc = $this->_container->DB;

		// Check Data
		$chk = $dbc->fetchRow('SELECT id, license_id FROM lab_result WHERE id = :pk AND license_id = :l0', [
			':pk' => $data['id'],
			':l0' => $data['license_id'],
		]);
		if (empty($chk['id'])) {
			return $RES->withJSON([
				'type' => 'https://api.openthc.org/e/404',
				'data' => null,
				'meta' => [ 'detail' => 'Request Error [ARU#048]' ],
			], 404);
		}


		// Update Record
		$mod = [
			'type' => $data['type'],
			'name' => $data['name'],
			'meta' => json_encode($data['meta'])
		];
		$where = [
			'id' => $data['id'],
			'license_id' => $data['license_id'],
		];
		$res = $dbc->update('lab_result', $mod, $where);

		$LR = new Lab_Result(null, [
			'id' => $data['id'],
		]);
		$LR->importCOA($data['meta']['Result']['meta']['pdf_path']);

		// @todo Update METRICs

		return $RES->withJSON([
			'data' => $data,
			'meta' => $res,
		], 200);

	}

	function _inputData()
	{
		$data = null;

		$type = strtok($_SERVER['CONTENT_TYPE'], ';');
		$type = strtolower($type);

		switch ($type) {
			case 'application/json':
				$json = stream_get_contents('php://input');
				$data = json_decode($json, true);
				return $RES->withJSON([
					'data' => $json,
				]);
			break;
			case 'application/x-www-form-urlencoded':
				$data = $_POST;
			break;
		}

		return $data;
	}

}
