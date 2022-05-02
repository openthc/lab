<?php
/**
 * Create a Sample
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace App\Controller\Sample;

use Edoceo\Radix\Session;

class Create extends \App\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->loadSiteData();
		$data['Page']['title'] = 'Sample :: Create';

		$dbc = $this->_container->DBC_User;

		// Source Input?
		$data['Source_Lot'] = [];
		if ( ! empty($_GET['source'])) {
			$data['Source_Lot'] = $dbc->fetchRow('SELECT * FROM inventory WHERE id = :i0', [ ':i0' => $_GET['source'] ]);
			if ( ! empty($data['Source_Lot']['meta'])) {
				$data['Source_Lot']['meta'] = json_decode($data['Source_Lot']['meta'], true);
			}
			if ( ! empty($data['Source_Lot']['product_id'])) {
				$data['Source_Product'] = $dbc->fetchRow('SELECT * FROM product WHERE id = :p0', [ ':p0' => $data['Source_Lot']['product_id'] ]);
			}
			if ( ! empty($data['Source_Lot']['variety_id'])) {
				$data['Source_Variety'] = $dbc->fetchRow('SELECT * FROM variety WHERE id = :v0', [ ':v0' => $data['Source_Lot']['variety_id'] ]);
			}
			if ( ! empty($data['Source_Lot']['license_id_source'])) {
				$data['Source_License'] = $dbc->fetchRow('SELECT * FROM license WHERE id = :l0', [ ':l0' => $data['Source_Lot']['license_id_source'] ]);
			}
			// __exit_text($data);
		}

		// POST Handler
		switch ($_POST['a']) {
			case 'create-sample':

				$_POST['product'] = trim($_POST['product']);
				$_POST['variety-name'] = trim($_POST['variety-name']);

				$dbc->query('BEGIN');

				$ls = new \App\Lab_Sample($dbc);
				$ls['id'] = _ulid();
				$ls['name'] = $_POST['sample-name'];
				$ls['lot_id'] = $_POST['source-lot-id'];
				$ls['license_id'] = $_SESSION['License']['id'];
				$ls['license_id_source'] = $_POST['license-id'];
				$ls['qty'] = floatval($_POST['qty']);

				// $P1 = new Product()
				$P1 = $dbc->fetchRow('SELECT * FROM product WHERE license_id = :l0 AND id = :p0', [
					':l0' => $_SESSION['License']['id'],
					':p0' => $_POST['product-id'],
				]);
				if (empty($P1['id'])) {
					// $P1 = [
					// 	'id' => _ulid(),
					// 	'license_id' => $_SESSION['License']['id'],
					// 	'product_type_id' => $_POST['product-type'],
					// 	'name' => $_POST['product'],
					// 	'stub' => _text_stub($_POST['product'])
					// ];
					// $P1['guid'] = $P1['id'];
					// $dbc->insert('product', $P1);
				}
				// $ls['product_id'] = $P1['id'];

				$S1 = $dbc->fetchRow('SELECT * FROM variety WHERE license_id = :l0 AND id = :v0', [
					':l0' => $_SESSION['License']['id'],
					':v0' => $_POST['variety-id'],
				]);
				if (empty($S1['id'])) {
					// $S1 = [
					// 	'id' => _ulid(),
					// 	'license_id' => $_SESSION['License']['id'],
					// 	'name' => $_POST['variety-name'],
					// ];
					// $S1['guid'] = $S1['id'];
					// $dbc->insert('variety', $S1);
				}

				if (empty($ls['lot_id'])) {
					$ls['lot_id'] = $dbc->insert('inventory', [
						// 'id' => $ls['id'],
						'license_id' => $_SESSION['License']['id'],
						'license_id_source' => $_POST['license-id'],
						'product_id' => '018NY6XC00PR0DUCT000000000', // $P1['id'],
						'variety_id' => '018NY6XC00VAR1ETY000000000', // $S1['id'],
						'section_id' => '018NY6XC00SECT10N000000000',
						'guid' => $ls['id'],
						'flag' => (0x00000100 | 0x00000200), // From app/lib/Inventory.php
						'stat' => 410, // Stat GONE
						'qty' => 0,
					]);
				}

				$ls->save();

				$dbc->query('COMMIT');

				return $RES->withRedirect('/sample/' . $ls['id']);

			break;
		}

		$sql = 'SELECT id, name FROM product_type WHERE stat = 200 ORDER BY name';
		$data['product_type'] = $dbc->fetchMix($sql);

		return $RES->write( $this->render('sample/create.php', $data) );

	}
}
