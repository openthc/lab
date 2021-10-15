<?php
/**
 * Show the Active Inventory Samples
 */

namespace App\Controller\Sample;

use App\Lab_Sample;

class Sync extends \OpenTHC\Controller\Base
{
	private $cre;

	private $insert_count = 0;

	private $update_count = 0;

	/**
	 * Do the SYNC
	 */
	function __invoke($REQ, $RES, $ARG)
	{

		$_GET['a'] = 'force'; // Always Force
		if (!empty($_GET['a'])) {
			if ('force' == $_GET['a']) {
				unset($_SESSION['sync-sample-time']);
			}
		}
		if (!empty($_SESSION['sync-sample-time'])) {
			$span = $_SERVER['REQUEST_TIME'] - $_SESSION['sync-sample-time'];
			if ($span <= 900) {
				return $RES->withJSON(array(
					'status' => 'success',
					'detail' => 'Cached',
					'result' => 0,
				));
			}
		}

		$_SESSION['sync-sample-time'] = $_SERVER['REQUEST_TIME'];

		session_write_close();

		$this->cre = new \OpenTHC\CRE($_SESSION['pipe-token']);

		if (!empty($ARG['id'])) {
			return $this->syncOne($REQ, $RES, $ARG);
		}

		return $this->syncAll($REQ, $RES, $ARG);

	}

	/**
	 * Sync All Samples
	 */
	function syncAll($REQ, $RES, $ARG)
	{
		// Only want to get the QA Sample Lots -- so maybe, only Lots when we are a Laboratory?
		$res = $this->cre->get('/lot?source=true');

		if ('success' != $res['status']) {
			return $RES->withJSON([
				'meta' => [ 'detail' => $this->cre->formatError($res) ]
			]);
		}

		$dbc = $this->_container->DBC_User;

		// Import Lots (only once!)
		foreach ($res['result'] as $rec) {
			$this->_sync_one($rec['_source']);
		}

		return $RES->withJSON([
			'meta' => [ 'detail' => sprintf('%d Insert, %d Update', $this->insert_count, $this->update_count) ]
		]);

	}

	/**
	 * Sync One Object
	 */
	function syncOne($REQ, $RES, $ARG)
	{
		$cre = new \OpenTHC\CRE($_SESSION['pipe-token']);
		$res = $cre->get('/lot/' . $ARG['id']);

		$obj = $res['result'];
		$this->_sync_one($obj);

		if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return $RES->withStatus(204);
		}

		return $RES->withRedirect('/sample/' . $ARG['id']);

	}

	/**
	 * Internal INSERT or UPDATE
	 */
	protected function _sync_one($obj)
	{
		$obj['guid'] = $obj['global_id'];

		$dbc = $this->_container->DBC_User;

		$sql = 'SELECT id, stat, meta FROM lab_sample WHERE license_id = ? AND id = ?';
		$arg = array($_SESSION['License']['id'], $obj['guid']);
		$chk = $dbc->fetchRow($sql, $arg);

		if (empty($chk['id'])) {

			// Create It

			$x = $this->cre->get('/config/product/' . $obj['global_inventory_type_id']);
			$Product = $x['result'];

			$Strain = null;
			if (!empty($obj['global_strain_id'])) {
				$x = $this->cre->get('/config/strain/' . $obj['global_strain_id']);
				$Strain = $x['result'];
			}

			$add = array(
				'id' => $obj['guid'],
				'license_id' => $_SESSION['License']['id'],
				'meta' => json_encode(array(
					'Lot' => $obj,
					'Product' => $Product,
					'Strain' => $Strain,
				)),
			);

			// has lab result
			if (!empty($obj['global_lab_result_id'])) {
				$add['stat'] = Lab_Sample::STAT_DONE;
			}

			$dbc->insert('lab_sample', $add);
			$this->insert_count++;

		} else {

			// Update the Meta
			$m = json_decode($chk['meta'], true);
			$m['Lot'] = $obj;

			$x = $this->cre->get('/config/product/' . $obj['global_inventory_type_id']);
			if (!empty($x['result'])) {
				$m['Product'] = $x['result'];
			}

			if (empty($obj['global_strain_id'])) {
				$m['Strain'] = [
					'id' => null,
					'name' => '- Not Set -',
				];
			} else {
				$x = $this->cre->get('/config/strain/' . $obj['global_strain_id']);
				if (!empty($x['result'])) {
					$m['Strain'] = $x['result'];
				}
			}

			// has lab result
			if (!empty($obj['global_lab_result_id'])) {
				$chk['stat'] = Lab_Sample::STAT_DONE;
			}

			$arg = [
				':pk' => $chk['id'],
				':s1' => $chk['stat'],
				':t1' => trim(sprintf('%s/%s', $m['Product']['type'], $m['Product']['intermediate_type'])),
				':m0' => \json_encode($m)
			];
			$dbc->query('UPDATE lab_sample SET stat = :s1, product_type = :t1, meta = :m0 WHERE id = :pk', $arg);

			$this->update_count++;

		}

	}

}
