<?php
/**
 * Report Viewer
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Report;

use Edoceo\Radix\Session;

use App\Lab_Sample;
use OpenTHC\Lab\Lab_Report;

class Single extends \App\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		// Get Result
		$dbc_user = $this->_container->DBC_User;
		$Lab_Report = new Lab_Report($dbc_user, $ARG['id']);
		if (empty($Lab_Report['id'])) {
			_exit_html_warn('<h1>Lab Report Not Found [CRS-030]', 404);
		}

		$data = $this->_load_data($dbc_user, $Lab_Report);
		$data['Page'] = [ 'title' => $Lab_Report['name'] ];

		return $RES->write( $this->render('report/single.php', $data) );
	}

	/**
	 *
	 */
	function _load_data($dbc_user, $Lab_Report)
	{
		$this->loadSiteData();
		$data['Lab_Report'] = $Lab_Report->toArray();
		$data['Lab_Report']['meta'] = __json_decode($Lab_Report['meta']);

		$Lab_Sample = new Lab_Sample($dbc_user, $data['Lab_Report']['lab_sample_id']);
		$data['Lab_Sample'] = $Lab_Sample->toArray();

		$Lot = $dbc_user->fetchRow('SELECT id, product_id, variety_id FROM inventory WHERE id = :i0', [ ':i0' => $Lab_Sample['inventory_id'] ?: $Lab_Sample['lot_id'] ]);
		// $data['Lot'] = $dbc->fetchRow('SELECT * FROM inventory WHERE id = :i0', [
		// 	':i0' => $data['Lab_Sample']['lot_id']
		// ]);
		$data['Product'] = $dbc_user->fetchRow('SELECT * FROM product WHERE id = ?', [ $Lot['product_id'] ]);
		$data['Product_Type'] = $dbc_user->fetchRow('SELECT * FROM product_type WHERE id = ?', [ $Product['product_type_id'] ]);
		$data['Variety'] = $dbc_user->fetchRow('SELECT * FROM variety WHERE id = ?', [ $Lot['variety_id'] ]);

		$data['License_Laboratory'] = $dbc_user->fetchRow('SELECT * FROM license WHERE id = :l0', [
			':l0' => $data['Lab_Report']['license_id']
		]);

		$data['License_Source'] = $dbc_user->fetchRow('SELECT * FROM license WHERE id = :l0', [
			':l0' => $data['Lab_Sample']['license_id_source']
		]);

		// Metric Types
		$res = $dbc_user->fetchAll('SELECT id, name, sort FROM lab_metric_type ORDER BY sort');
		foreach ($res as $rec) {
			$data['lab_metric_type_list'][ $rec['id'] ] = $rec;
		}

		// Metrics
		$res = $dbc_user->fetchAll("SELECT id, name, meta->>'uom' AS uom, sort FROM lab_metric ORDER BY sort");
		foreach ($res as $rec) {
			$data['lab_metric_list'][ $rec['id'] ] = $rec;
		}

		// Another Name for the THing
		// $data['Lab_Result_Section_Metric_list'] = $Lab_Result->getMetrics_Grouped();

		// Another Name for the Stuff
		// $data['Lab_Result_Metric_Type_list'] = $Lab_Result->getMetricListGrouped();

		return $data;
	}

}
