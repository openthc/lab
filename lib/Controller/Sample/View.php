<?php
/**
 * Only works if you OWN the Sample
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace App\Controller\Sample;

use Edoceo\Radix\Session;

use App\Lab_Sample;

class View extends \App\Controller\Base
{
	private $cre;

	function __invoke($REQ, $RES, $ARG)
	{
		if (empty($ARG['id'])) {
			_exit_text('Invalid Request', 400);
		}

		$dbc = $this->_container->DBC_User;

		$Lab_Sample = new \App\Lab_Sample($dbc, $ARG['id']);
		if (empty($Lab_Sample['id'])) {
			_exit_text('Invalid Lab Sample [CSV-026]', 400);
		}

		switch ($_POST['a']) {
			case 'done':
				return $this->_finishSample($RES, $ARG, $Lab_Sample);
			break;
			case 'drop':
				// need to return the $RES object from these methods to do anything
				return $this->_dropSample($RES, $ARG, $Lab_Sample);
			break;
			case 'accept-sample':
				return $this->_accept($RES, $Lab_Sample);
			case 'save':
				return $this->_saveSample($RES, $Lab_Sample);
			break;
			case 'void':
				return $this->_voidSample($RES, $ARG, $Lab_Sample);
			break;
		}

		$Lot = $dbc->fetchRow('SELECT * FROM inventory WHERE id = ?', [ $Lab_Sample['lot_id'] ]);
		$Product = $dbc->fetchRow('SELECT * FROM product WHERE id = ?', [ $Lot['product_id'] ]);
		$ProductType = $dbc->fetchRow('SELECT * FROM product_type WHERE id = ?', [ $Product['product_type_id'] ]);
		$Variety = $dbc->fetchRow('SELECT * FROM variety WHERE id = ?', [ $Lot['variety_id'] ]);

		$Lab_Result_list = $dbc->fetchAll('SELECT * FROM lab_result WHERE lab_sample_id = :ls0', [
			':ls0' => $Lab_Sample['id']
		]);

		$Lab_Sample_Meta = json_decode($Lab_Sample['meta'], true);

		// Get Fresh Lot Data?
		// $res = $this->cre->get('/lot/' . $Lab_Sample['id']);

		//$res = $this->cre->get('/config/product/' . $QAS['global_inventory_type_id']);
		//$P = $res['result'];
		//var_dump($P);

		// Find Laboratory License
		//$res = $this->cre->get('/config/license/' . $QAS['global_created_by_mme_id']);
		//$L_Lab = $res['result'];

		// Find Owner License
		// $dbc_main = $this->_container->DBC_Main;
		// $L_Source = new \OpenTHC\License($dbc_main, $Lab_Sample['license_id_source']);
		$L_Source = [];

		$data = $this->loadSiteData([
			'Page' => array('title' => 'Sample :: View'),
			'Lab_Sample' => $Lab_Sample->toArray(),
			'Lab_Sample_meta' => $Lab_Sample_Meta,
			'Lot' => $Lot,
			'Product' => $Product,
			'ProductType' => $ProductType,
			'Variety' => $Variety,
			'Lab_Result_list' => $Lab_Result_list,
			'License_Source' => $L_Source,
		]);

		// Nicely Formatted ID
		$data['Lab_Sample']['id_nice'] = _nice_id($data['Lab_Sample']['id'], $data['Lab_Sample']['guid']);
		if (empty($data['Product']['uom'])) {
			$data['Product']['uom'] = 'g';
		}

		return $RES->write( $this->render('sample/single.php', $data) );

	}

	function _accept($RES, $Lab_Sample)
	{
		$dbc = $this->_container->DBC_User;

		$cfg = $dbc->fetchOne("SELECT val FROM base_option WHERE key = 'lab-sample-seq-format'");
		$cfg = json_decode($cfg);

		if (!empty($cfg)) {

			$dtz = $dbc->fetchOne("SELECT val FROM base_option WHERE key = 'timezone'");
			if ($dtz) {
				// Assign Sequence
				$dt0 = new \DateTime();
				$dt0->setTimeZone(new \DateTimeZone($dtz));
			}

			$cfg = str_replace('{YYYY}', $dt0->format('Y'), $cfg);
			$cfg = str_replace('{YY}', $dt0->format('y'), $cfg);
			$cfg = str_replace('{MM}', $dt0->format('m'), $cfg);
			$cfg = str_replace('{MA}', chr(64 + $dt0->format('m')), $cfg);
			$cfg = str_replace('{DDD}', sprintf('%03d', $dt0->format('z') + 1), $cfg);
			$cfg = str_replace('{DD}', $dt0->format('d'), $cfg);
			$cfg = str_replace('{HH}', $dt0->format('H'), $cfg);
			$cfg = str_replace('{II}', $dt0->format('i'), $cfg);
			$cfg = str_replace('{SS}', $dt0->format('s'), $cfg);

			if (preg_match_all('/(\{SEQ\w+?\})/', $cfg, $m)) {
				$seq_list = $m[1];
				foreach ($seq_list as $seq) {

					$fmt = '%d';

					$len = preg_match('/(\d+)\}$/', $seq, $m) ? $m[1] : 0;
					if (!empty($len)) {
						$fmt = sprintf('%%0%dd', $len);
					}

					$seq_mode = preg_match('/_(G|Y|Q|M)/', $seq, $m) ? $m[1] : 'G';

					$seq_name = sprintf('seq_%s_%s', $_SESSION['Company']['id'], $seq_mode);
					$seq_name = strtolower($seq_name);
					$val = $dbc->fetchOne('SELECT nextval(:s)', [':s' => $seq_name ]);

					$rep = sprintf($fmt, $val);

					$cfg = str_replace($seq, $rep, $cfg);
				}
			}

			$Lab_Sample['name'] = $cfg;

		}

		$Lab_Sample['stat'] = Lab_Sample::STAT_LIVE;
		$Lab_Sample->save();

		Session::flash('Sample Accepted, Internal ID Assigned');

		return $RES->withRedirect(sprintf('/sample/%s', $Lab_Sample['id']));

	}

	function _finishSample($RES, $ARG)
	{
		$dbc = $this->_container->DBC_User;

		// $sql = 'SELECT * from lab_sample where id = :pk';
		// $res = $dbc->fetchAll($sql, [
		// 	':pk' => $ARG['id'],
		// ]);

		$sql = 'UPDATE lab_sample SET stat = :s1, flag = flag | :f1 WHERE license_id = :l0 AND id = :pk';
		$arg = array(
			':pk' => $ARG['id'],
			':l0' => $_SESSION['License']['id'],
			':s1' => \App\Lab_Sample::STAT_DONE,
			':f1' => \App\Lab_Sample::FLAG_DONE,
		);
		$res = $dbc->query($sql, $arg);

		return $RES->withRedirect('/sample');
	}

	function _dropSample($RES, $ARG)
	{
		\session_write_close();

		// $res = $this->cre->get('/lot?source=true');
		// $res = $this->cre->delete('/lot/' . $ARG['id']);
		$dbc_user = $this->_container->DBC_User;

		$sql = 'SELECT * from lab_sample where id = :pk';
		$res = $dbc_user->fetchAll($sql, [
			':pk' => $ARG['id'],
		]);

		$sql = 'UPDATE lab_sample SET stat = :s1, flag = flag | :f1 WHERE license_id = :l0 AND id = :pk';
		$arg = array(
			':pk' => $ARG['id'],
			':l0' => $_SESSION['License']['id'],
			':s1' => \App\Lab_Sample::STAT_VOID,
			':f1' => \App\Lab_Sample::FLAG_DEAD,
		);
		$res = $dbc_user->query($sql, $arg);

		return $RES->withRedirect('/sample');
	}

	function _saveSample($RES, $Lab_Sample)
	{
		$dbc = $this->_container->DBC_User;

		$_POST['product-name'] = trim($_POST['product-name']);
		$_POST['variety-name'] = trim($_POST['variety-name']);

		// Product Change?
		if ($_POST['product-id'] != $Lab_Sample['product_id']) {
			if (!empty($_POST['product-name'])) {
				$arg = [];
				$arg[':p0'] = $_POST['product-name'];
				$PR = $dbc->fetchRow('SELECT id FROM product WHERE name = :p0', $arg);
				if (!empty($PR['id'])) {
					$Lab_Sample['product_id'] = $PR['id'];
				} else {
					$PR = [];
					$PR['id'] = _ulid();
					$PR['guid'] = $PR['id'];
					$PR['license_id'] = $Lab_Sample['license_id'];
					$PR['product_type_id'] = 117; // Flower
					$PR['name'] = $_POST['product-name'];
					$PR['stub'] = _text_stub($PR['name']);
					$dbc->insert('product', $PR);
					$Lab_Sample['product_id'] = $PR['id'];
				}
			}
		}

		// Variety Change?
		if (!empty($_POST['variety-id'])) {
			$arg = [];
			$arg[':v0'] = $_POST['variety-id'];
			$VT = $dbc->fetchRow('SELECT id FROM variety WHERE id = :v0', $arg);
			if (empty($VT['id'])) {
				$VT = [
					'id' => $_POST['variety-id'],
					'license_id' => $_SESSION['License']['id'],
					'guid' => $_POST['variety-id'],
					'name' => $_POST['variety-name'],
				];
				$dbc->insert('variety', $VT);
			}
			if (!empty($VT['id'])) {
				$Lab_Sample['variety_id'] = $VT['id'];
			}
		}

		if (!empty($_POST['sample-qty'])) {
			$Lab_Sample['qty'] = floatval($_POST['sample-qty']);
		}

		if (!empty($_POST['lot-id-source'])) {
			$m = json_decode($Lab_Sample['meta'], true);
			$m['Lot_Source']['id'] = $_POST['lot-id-source'];
			$Lab_Sample['meta'] = json_encode($m);
		}

		$Lab_Sample['license_id_source'] = $_POST['license-id-source'];
		$Lab_Sample->save();
		return $RES->withRedirect('/sample');
	}

	function _voidSample($RES, $ARG)
	{
		$dbc = $this->_container->DBC_User;

		$sql = 'UPDATE lab_sample SET stat = :s1, flag = flag | :f1 WHERE license_id = :l0 AND id = :pk';
		$arg = array(
			':pk' => $ARG['id'],
			':l0' => $_SESSION['License']['id'],
			':s1' => \App\Lab_Sample::STAT_VOID,
			':f1' => \App\Lab_Sample::FLAG_VOID,
		);
		$res = $dbc->query($sql, $arg);

		return $RES->withRedirect('/sample');
	}

}
