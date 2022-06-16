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

		$meta = [];
		$meta = json_decode($LR['meta'], true);

		switch ($meta['@context']) {
			case 'http://leafdatazone.com/2017':
				// Garbage
				// $meta['Lab_Result']
				break;
		}

		$coa_file = $meta['Lab_Result']['coa_file'];
		if ( ! empty($coa_file) && ! is_file($meta['Lab_Result']['coa_file'])) {
			$coa_file = null;
		}
		if (empty($$coa_file)) {
			$coa_file = $LR->getCOAFile();
			if ( ! is_file($meta['Lab_Result']['coa_file'])) {
				$coa_file = null;
			}
		}

		if ( ! empty($coa_file)) {
			$meta['Lab_Result']['coa'] = sprintf('https://%s/pub/%s.pdf', $_SERVER['SERVER_NAME'], $LR['id']);
			$meta['Lab_Result']['coa_link'] = sprintf('https://%s/pub/%s.pdf', $_SERVER['SERVER_NAME'], $LR['id']);
		}

		return $RES->withJSON($meta, 200, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

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
