<?php
/**
 * Report Viewer
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Report;

use Edoceo\Radix\Session;

class Single extends \App\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		// Get Result
		$dbc_user = $this->_container->DBC_User;
		$chk = $dbc_user->fetchRow('SELECT * FROM lab_report WHERE id = :lr0', [ ':lr0' => $ARG['id'] ]);
		// $Lab_Report = new Lab_Report($dbc_user, $chk);
		// __exit_text($chk);
		$chk['meta'] = __json_decode($chk['meta']);

		$data = $this->loadSiteData();
		$data['Lab_Report'] = $chk;
		$res = $dbc_user->fetchAll('SELECT id, name, sort FROM lab_metric_type ORDER BY sort');
		foreach ($res as $rec) {
			$data['Lab_Metric_Type_list'][ $rec['id'] ] = $rec;
		}

		// $Lot = $dbc_user->fetchRow('SELECT id, product_id, variety_id FROM inventory WHERE id = :i0', [ ':i0' => $Lab_Sample['inventory_id'] ?: $Lab_Sample['lot_id'] ]);
		// $Product = $dbc_user->fetchRow('SELECT * FROM product WHERE id = ?', [ $Lot['product_id'] ]);
		// $ProductType = $dbc_user->fetchRow('SELECT * FROM product_type WHERE id = ?', [ $Product['product_type_id'] ]);
		// $Variety = $dbc_user->fetchRow('SELECT * FROM variety WHERE id = ?', [ $Lot['variety_id'] ]);



		return $RES->write( $this->render('report/single.php', $data) );
	}

}
