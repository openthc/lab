<?php
/**
 * Create a Sample
 */

namespace App\Controller\Sample;

use Edoceo\Radix\Session;

class Create extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->loadSiteData();
		$data['Page']['title'] = 'Sample :: Create';

		switch ($_POST['a']) {
			case 'create-sample':

				$_POST['product'] = trim($_POST['product']);
				$_POST['variety-name'] = trim($_POST['variety-name']);

				$dbc = $this->_container->DBC_User;

				$ls = new \App\Lab_Sample($dbc);
				$ls['id'] = _ulid();
				$ls['license_id'] = $_SESSION['License']['id'];
				$ls['license_id_source'] = $_POST['license-id'];
				$ls['qty'] = floatval($_POST['qty']);
				$ls['meta'] = json_encode([
					'Lot_Source' => [
						'id' => $_POST['lot-id-source']
					],
				]);

				// $P1 = new Product()
				$P1 = $dbc->fetchRow('SELECT * FROM product WHERE license_id = :l0 AND name = :n0', [
					':l0' => $_SESSION['License']['id'],
					':n0' => $_POST['product'],
				]);
				if (empty($P1['id'])) {
					$P1 = [
						'id' => _ulid(),
						'license_id' => $_SESSION['License']['id'],
						'product_type_id' => $_POST['product-type'],
						'name' => $_POST['product'],
						'stub' => _text_stub($_POST['product'])
					];
					$P1['guid'] = $P1['id'];
					$dbc->insert('product', $P1);
				}
				// $ls['product_id'] = $P1['id'];

				$S1 = $dbc->fetchRow('SELECT * FROM strain WHERE license_id = :l0 AND name = :n0', [
					':l0' => $_SESSION['License']['id'],
					':n0' => $_POST['variety-name'],
				]);
				if (empty($S1['id'])) {
					$S1 = [
						'id' => _ulid(),
						'license_id' => $_SESSION['License']['id'],
						'name' => $_POST['variety-name'],
					];
					$S1['guid'] = $S1['id'];
					$dbc->insert('strain', $S1);
				}
				// $ls['strain_id'] = $S1['id'];

				$l0['id'] = $dbc->insert('inventory', [
					// 'id' => $ls['id'],
					'license_id' => $_SESSION['License']['id'],
					'product_id' => $P1['id'],
					'strain_id' => $S1['id'],
					'section_id' => '018NY6XC00SECT10N000000000',
					'guid' => $ls['id'],
					'stat' => 200,
					'qty' => $ls['qty'],
				]);
				$ls['lot_id'] = $l0['id'];
				$ls->save();

				return $RES->withRedirect('/sample/' . $ls['id']);

			break;
		}

		$sql = 'SELECT id, name FROM product_type WHERE stat = 200 ORDER BY name';
		$data['product_type'] = $this->_container->DBC_User->fetchMix($sql);

		return $RES->write( $this->render('sample/create.php', $data) );
	}
}
