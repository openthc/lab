<?php
/**
 * Process Queue of COA Uploads
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Result;

use OpenTHC\Lab\Lab_Result;
use OpenTHC\Lab\Lab_Sample;
use OpenTHC\Lab\PDF;

class Queue extends \OpenTHC\Lab\Controller\Result\Upload
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->loadSiteData();
		$data['Page'] = array('title' => 'Result :: COA :: Upload :: Queue');

		if (!empty($_POST['a'])) {
			if ('lab-result-lot-link' == $_POST['a']) {
				return $this->linkCOA($RES, $_POST['lot_id']);
			} elseif (preg_match('/^link\-(.+)$/', $_POST['a'], $m)) {
				return $this->linkCOA($RES, $m[1]);
			}
		}

		if (!empty($_GET['f'])) {
			return $this->viewCOA($RES, $data);
		}

		$import_queue_list = array();
		// New Canon
		$import_queue_path = sprintf('%s/var/import/%s', APP_ROOT, $_SESSION['Company']['id']);
		$pdf_list = glob("$import_queue_path/*.pdf");
		foreach ($pdf_list as $pdf_file) {
			$import_queue_list[] = basename($pdf_file);
		}
		// Legacy
		$import_queue_path = sprintf('%s/var/import/%s', APP_ROOT, $_SESSION['License']['id']);
		$pdf_list = glob("$import_queue_path/*.pdf");
		foreach ($pdf_list as $pdf_file) {
			$import_queue_list[] = basename($pdf_file);
		}
		sort($import_queue_list);

		$data['file_list'] = $import_queue_list;

		return $RES->write( $this->render('result/queue.php', $data) );
	}

	function linkCOA($RES, $arg)
	{
		// $dbc_main = $this->_container->DBC_Main;
		$dbc_user = $this->_container->DBC_User;

		$data = [];
		$data['lot_link'] = $arg;
		$data['pdf_file'] = $this->resolveFile($_GET['f']);
		// var_dump($data); exit;

		$LS = [];

		$Lab_Result = new Lab_Result($dbc_user, $data['lot_link']);
		if (empty($Lab_Result['id'])) {

			// Try some LeafData related bullshit
			if (preg_match('/^WA\w+\.IN\w+/', $data['lot_link'])) {
				// Sample
				$LS = new Lab_Sample($dbc_user, $data['lot_link']);
				if (empty($LS['id'])) {
					_exit_html(sprintf('Lab Sample Not Found, please <a href="/sample/%s/sync">sync this result</a>', $data['lot_link']), 404);
				}
				$LSm = json_decode($LS['meta'], true);;
				// _exit_text($LSm);
				// var_dump($LSm); exit;
				$Lab_Result = new Lab_Result($dbc_user, $LSm['Lot']['global_lab_result_id']);

			} elseif (preg_match('/WA\w+\.LR\w+/', $data['lot_link'])) {

				// Result
				$Lab_Result = new Lab_Result($dbc_user, $data['lot_link']);
				if (empty($Lab_Result['id'])) {
					_exit_html(sprintf('Lab Result Not Found, please <a href="/result/%s/sync">sync this result</a>', $data['lot_link']), 404);
				}
			} else {
				_exit_text('Unknown Link Target [CRQ#070]');
			}

		}

		if (empty($Lab_Result['id'])) {
			_exit_text('Cannot find Lab Result [CRQ-077]');
		}

		$Lab_Result->setCOAFile($data['pdf_file']);

		// $f = basename($data['pdf_file'], '.pdf');
		$f = preg_replace('/\.pdf$/', '.png', $data['pdf_file']);
		if (is_file($f)) {
			$data['unlink_png'] = $f;
			unlink($f);
		}
		$f = preg_replace('/\.pdf$/', '.txt', $data['pdf_file']);
		if (is_file($f)) {
			$data['unlink_txt'] = $f;
			unlink($f);
		}

		return $RES->withRedirect('/result/upload/queue');

	}

	function viewCOA($RES, $data)
	{
		// Single File
		$data['pdf_file'] = $_GET['f'];
		$pdf_file = $this->resolveFile($_GET['f']);

		// $data['pdf_file'] = basename($pdf_file);
		// $data['txt_file'] = basename($txt_file);

		// pdf to text
		$data['txt_data'] = PDF::extract_text($pdf_file);
		// var_dump($data); exit;

		// text to data
		$data['coa_data'] = PDF::extract_coa($data['txt_data']);

		if (preg_match_all('/(WA\w{2,7}\.(IN|LR)\w+)/', $data['txt_data'], $m)) {

			$lot_list = $m[1];
			$data['lot_list'] = array_unique($lot_list);
			foreach ($lot_list as $l) {
				// $chk = $this->_container->DBC_User->fetchRow('SELECT id FROM lab_sample WHERE id = ?');
				// $chk = $this->_container->DBC_User->fetchRow('SELECT id FROM lab_result WHERE id = ?');
				// $chk = $this->_container->DBC_User->fetchRow('SELECT id FROM lab_result WHERE meta->'Sample'->'global_id' = ?');
			}

		}

		return $RES->write( $this->render('page/result/queue-one.html', $data) );

	}


}
