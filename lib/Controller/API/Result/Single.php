<?php
/**
 * Return One Lab Result, Inflated
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace App\Controller\API\Result;

use App\Lab_Result;

class Single extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$dbc = $this->_container->DBC_Main;

		// Get Result
		$LR = new Lab_Result($dbc, $ARG['id']);
		if (empty($LR['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Not Found [ARS-021]' ],
			], 404);
		}

		$ret = [];
		$ret = json_decode($LR['meta'], true);

		if (empty($rec['coa'])) {
			$x = sprintf('%s/var/%s/coa.pdf', APP_ROOT, $LR['id']);
			if (is_file($x)) {
				$ret['Result']['coa_link'] = sprintf('https://%s/pub/%s.pdf', $_SERVER['SERVER_NAME'], $LR['id']);
			}
		}

		// $Result = $QAR->_Result['Result']; // wtf?
		// $Result = $this->_map_metric($Result);
		// //_exit_json($Result);

		// $ret = array(
		// 	'Company' => $QAR->_Company,
		// 	'License' => array(
		// 		'id' => $QAR->_License['licensenum'],
		// 		'name' => $QAR->_License['name'],
		// 	),
		// 	'Inventory' => array(
		// 		'id' => $QAR->_Inventory['guid'],
		// 		'type' => array(
		// 			'id' => $QAR->_Inventory['inventorytype'],
		// 			'name' => $QAR->_Inventory['inventorytype_name'],
		// 		),
		// 		'variety' => $QAR->_Inventory['variety'],
		// 		'product' => $QAR->_Inventory['product'],
		// 		'created_at' => $QAR->_Inventory['created_at'],
		// 	),
		// 	'Laboratory' => ($Lab1 ? $Lab1 : $Lab0),
		// 	//'_Laboratory0' => $Lab0,
		// 	//'_Laboratory1' => $Lab1,
		// 	'Sample' => $Sample,
		// 	'Result' => $Result,
		// );

		// $LR->save();

		unset($ret['Page']);
		unset($ret['Site']);

		return $RES->withJSON($ret, 200, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

	}

	/**
	 * Remap LeafData metric names to our metric table
	 * @param array $R Result data array
	 * @return array $R
	 */
	function _map_metric($R)
	{
		$tab = array();

		$res_metric = $this->_container->DBC_Main->fetchAll('SELECT * FROM lab_metric');
		foreach ($res_metric as $m) {

			$m = array_merge($m, json_decode($m['meta'], true));
			//var_dump($m);

			$p = $m['cre']['leafdata_path'];

			if (!empty($p)) {
				$tab[ $m['id'] ] = array(
					'type' => $m['type'],
					'name' => $m['name'],
					'uom' => $m['uom'],
					'qom' => $R[$p],
				);
				unset($R[$p]);
			}
		}

		$R['metric_list'] = $tab;

		return $R;

	}
}
