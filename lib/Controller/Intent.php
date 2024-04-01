<?php
/**
 * When Someone Has Intent
 *
 * This file is part of OpenTHC Laboratory Portal
 *
 * OpenTHC Laboratory Portal is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published by
 * the Free Software Foundation.
 *
 * OpenTHC Laboratory Portal is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OpenTHC Laboratory Portal.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace OpenTHC\Lab\Controller;

use OpenTHC\Company;
use OpenTHC\Lab\Lab_Result;

class Intent extends \OpenTHC\Lab\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		if (!empty($ARG['id'])) {
			$dbc_auth = $this->_container->DBC_Auth;
			$act = $dbc_auth->fetchRow('SELECT * FROM auth_context_ticket WHERE id = :x', [ ':x' => $ARG['id'] ]);
			// Do Stuff w/Ticket
			$ctx = json_decode($act['meta'], true);
			unset($act['meta']);
			$ctx = array_merge($ctx, $act);

			switch ($ctx['action']) {
				case 'create-sample-done':
					$data = $this->loadSiteData();
					$data['Page']['title'] = 'Done';
					$data['body'] = $ctx['message'];
					return $this->_container->view->render($RES, 'page/html.html', $data);
				break;
				case 'lab-sample-create':
					return $this->renderSampleCreate($RES, $ctx);
				break;
			}
		}

		if (!empty($_GET['_'])) {

			$arg = _decrypt($_GET['_']);
			$arg = json_decode($arg, true);

			// Expire Time
			if (!empty($arg['x'])) {
				if ($arg['x'] < $_SERVER['REQUEST_TIME']) {
					return $RES->withStatus(410);
				}
			}

			$dbc = $this->_container->DBC_Main;

			// Action
			switch ($arg['a']) {
			case 'coa-upload':

				$LR = new Lab_Result($dbc, $arg['r']);
				// var_dump($LR); exit;

				$file = 'page/intent/coa-upload.html';
				$data = array(
					'Page' => array('title' => 'COA Upload'),
					'Result' => array(
						'id' => $LR['id'],
					)
				);


				switch ($_POST['a']) {
				case 'coa-upload':
					// Whenever this triggers, fix it to use Lab_Result->getCOAFile();
					$LR->setCOAFile($_FILES['file']['tmp_name']);
					$data['alert'] = 'success';

				}

				return $this->_container->view->render($RES, $file, $data);

				break;

			case 'coa-upload-bulk':
				return $this->_coa_bulk($RES, $arg);
			}
		}


		switch ($_SESSION['intent']) {
		case 'share-all':
			unset($_SESSION['intent']);
			unset($_SESSION['intent-data']);
			$RES = $RES->withRedirect('/result');
			break;
		case 'share-one':
			$RES = $RES->withRedirect('/result/' . $_SESSION['intent-data']);
			unset($_SESSION['intent']);
			unset($_SESSION['intent-data']);
			break;
		// default:
		// 	$RES = $RES->withRedirect('/result');
		}

		return $RES;
	}

	private function _coa_bulk($RES, $arg)
	{
		// var_dump($arg);
		$Company = new Company($arg['company_id']);
		if (empty($Company['id'])) {
			return $RES->withStatus(400);
		}

		// var_dump($_POST);
		// var_dump($_FILES);

		if (1 == count($_FILES)) {
			if (0 == $_FILES['file']['error']) {

				$import_queue_path = sprintf('%s/var/import/%s', APP_ROOT, $Company['id']);
				$import_queue_file = sprintf('%s/%s', $import_queue_path, urlencode($_FILES['file']['name']));

				if (!is_dir($import_queue_path)) {
					mkdir($import_queue_path, 0755, true);
				}

				move_uploaded_file($_FILES['file']['tmp_name'], $import_queue_file);

			}

			return $RES->withStatus(201);

		}

		$data = [
			'Page' => [ 'title' => 'Result :: COA :: Upload' ],
			'Company' => $Company->toArray(),
			'x' => $arg['x'],
			'mode' => 'lab-bulk',
		];

		return $RES->write( $this->render('result/upload.php', $data) );

	}

	/**
	 * Submit Incoming Samples
	 */
	function renderSampleCreate($RES, $ctx)
	{

		$dbc_auth = $this->_container->DBC_Auth;
		$Company0 = $dbc_auth->fetchRow('SELECT id, name, dsn FROM auth_company WHERE id = :c0', [ ':c0' => $ctx['company'] ]);
		if (empty($Company0['id'])) {
			_exit_text('Invalid Intake Link [LCI#153]');
		}

		$dbc_user = new \Edoceo\Radix\DB\SQL($Company0['dsn']);

		switch ($_POST['a']) {
			case 'create-sample':

				$Lab_Sample = new Lab_Sample($dbc_user);
				$Lab_Sample['id'] = _ulid();
				$Lab_Sample['stat'] = 100;
				$Lab_Sample['license_id'] = $ctx['license'];
				$Lab_Sample['license_id_source'] = $_POST['license-id'];
				$Lab_Sample['product_id'] = '018NY6XC00PR00000000000001';
				$Lab_Sample['strain_id'] = '018NY6XC00VR00000000000001';
				$Lab_Sample['meta'] = json_encode([
					'Source_License' => [
						'id' => $_POST['license-id'],
						'name' => $_POST['license-name'],
					],
					'Source_Inventory' => [
						'id' => $_POST['lot-id-source'],
					],
					'Product' => [
						'id' => $_POST['product-id'],
						'name' => $_POST['product-name'],
					],
					'Variety' => [
						'id' => $_POST['variety-id'],
						'name' => $_POST['variety-name'],
					]
				]);
				$Lab_Sample['hash'] = $Lab_Sample->getHash();
				$Lab_Sample->save();

				// _exit_json([
				// 	'ctx' => $ctx,
				// 	'_POST' => $_POST,
				// 	'Lab_Sample' => $Lab_Sample,
				// ]);
				$dbc_auth->query('UPDATE auth_context_ticket SET meta = :m1 WHERE id = :t0', [
					':t0' => $ctx['id'],
					':m1' => json_encode([
						'action' => 'create-sample-done',
						'company' => $ctx['company'],
						'license' => $ctx['license'],
						'sample' => $Lab_Sample['id'],
						'message' => _markdown("## Success\n\nYour Sample was uploaded with ID: {$Lab_Sample['id']}"),
					])
				]);

				return $RES->withRedirect(sprintf('/intent/%s', $ctx['id']));

			break;
		}

		$data = $this->loadSiteData();
		$data['Page']['title'] = 'Submit Intake Samples';
		$data['Company'] = $Company0;
		$data['Source_License'] = [];
		$data['Source_License']['id'] = $ctx['Source_License']['id'];
		$data['Source_License']['name'] = $ctx['Source_License']['name'];
		$data['Source_License']['code'] = $ctx['Source_License']['code'];
		$data['product_type'] = $dbc_user->fetchMix('SELECT id, name FROM product_type WHERE stat = 200 ORDER BY name');

		return $RES->write( $this->render('page/intent/create-sample.php', $data) );

	}
}
