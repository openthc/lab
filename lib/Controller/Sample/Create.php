<?php
/**
 * Create a Sample
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Sample;

use Edoceo\Radix\Session;

use \OpenTHC\Lab\Lab_Sample;

class Create extends \OpenTHC\Lab\Controller\Base
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
		}

		// POST Handler
		switch ($_POST['a']) {
			case 'create-sample':

				$_POST['product-name'] = trim($_POST['product-name']);
				$_POST['variety-name'] = trim($_POST['variety-name']);

				if (empty($_POST['license-id'])) {
					__exit_text('Please Provide a Source License', 400);
				}

				$Source_License0 = [];
				$Source_License1 = [];

				$dir = new \OpenTHC\Service\OpenTHC('dir');
				$res = $dir->get(sprintf('/api/license/%s', $_POST['license-id']));
				if ( ! empty($res['data']['id'])) {
					$Source_License0 = $res['data'];
				}

				$Source_License1 = $dbc->fetchRow('SELECT id, name FROM license WHERE id = :l0', [
					':l0' => $_POST['license-id']
				]);
				if (empty($Source_License1['id'])) {
					$Source_License1 = [
						'id' => $Source_License0['id'],
						'code' => $Source_License0['code'],
						'guid' => $Source_License0['guid'],
						'name' => $Source_License0['name'],
						'type' => $Source_License0['type'],
						'hash' => '-', // $Source_License0['hash']
					];
					$dbc->insert('license', $Source_License1);
				}

				$dbc->query('BEGIN');

				$ls = new Lab_Sample($dbc);
				$ls['id'] = _ulid();
				$ls['name'] = $_POST['sample-name'];
				$ls['lot_id'] = $_POST['source-lot-id'];
				$ls['license_id'] = $_SESSION['License']['id'];
				$ls['license_id_source'] = $Source_License1['id'];
				$ls['qty'] = floatval($_POST['qty']);

				$P1 = $dbc->fetchRow('SELECT * FROM product WHERE license_id = :l0 AND id = :p0', [
					':l0' => $_SESSION['License']['id'],
					':p0' => $_POST['product-id'],
				]);
				if (empty($P1['id'])) {
					// Find By Name?
					$P1 = $dbc->fetchRow('SELECT * FROM product WHERE license_id = :l0 AND name = :p0', [
						':l0' => $_SESSION['License']['id'],
						':p0' => $_POST['product-name'],
					]);
				}
				if (empty($P1['id'])) {
					// __exit_text('Create Product', 500);
					$P1 = [
						'id' => _ulid(),
						'license_id' => $_SESSION['License']['id'],
						'product_type_id' => $_POST['product-type'],
						'name' => $_POST['product-name'],
						'stub' => _text_stub($_POST['product-name'])
					];
					$P1['guid'] = $P1['id'];
					$dbc->insert('product', $P1);
				}
				// $ls['product_id'] = $P1['id'];

				$V1 = $dbc->fetchRow('SELECT * FROM variety WHERE license_id = :l0 AND id = :v0', [
					':l0' => $_SESSION['License']['id'],
					':v0' => $_POST['variety-id'],
				]);
				if (empty($V1['id'])) {
					$V1 = $dbc->fetchRow('SELECT id, name FROM variety WHERE license_id = :l0 AND name = :p0', [
						':l0' => $_SESSION['License']['id'],
						':p0' => $_POST['variety-name']
					]);
				}
				if (empty($V1['id'])) {
					$V1 = [
						'id' => _ulid(),
						'license_id' => $_SESSION['License']['id'],
						'name' => $_POST['variety-name'],
					];
					$V1['guid'] = $V1['id'];
					$dbc->insert('variety', $V1);
				}
				// $ls['variety_id'] = $V1['id'];

				if (empty($ls['lot_id'])) {
					$ls['lot_id'] = $ls['id'];
					$dbc->insert('inventory', [
						'id' => $ls['lot_id'],
						'license_id' => $_SESSION['License']['id'],
						'license_id_source' => $Source_License1['id'],
						'product_id' => $P1['id'],
						'variety_id' => $V1['id'],
						'section_id' => '018NY6XC00SECT10N000000000',
						'guid' => $ls['name'],
						'flag' => (0x00000100 | 0x00000200), // From app/lib/Inventory.php
						'stat' => 410, // Stat GONE
						'qty' => 0,
					]);
				} else {
					// Decrement Lot QTY
					$dbc->query('UPDATE inventory SET qty = qty - :q1 WHERE id = :i0', [
						':i0' => $ls['lot_id'],
						':q1' => $ls['qty'],
					]);
				}

				$ls->save('Lab_Sample/Create by User');

				$dbc->query('COMMIT');

				return $RES->withRedirect('/sample/' . $ls['id']);

			break;
		}

		$sql = 'SELECT id, name FROM product_type WHERE stat = 200 ORDER BY name';
		$data['product_type'] = $dbc->fetchMix($sql);

		return $RES->write( $this->render('sample/create.php', $data) );

	}
}
