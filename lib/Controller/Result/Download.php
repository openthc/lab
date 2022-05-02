<?php
/**
 * Generate Downloadable Types for the Files?
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace App\Controller\Result;

class Download extends \App\Controller\Result\View
{
	private $_fh;
	private $_eol = "\r\n";
	private $_sep = ',';

	/**
	 *
	 */
	function __construct($c)
	{
		parent::__construct($c);

		if (!empty($_GET['eol'])) {
			switch ($_GET['eol']) {
			case '\n';
				$this->_eol = "\n";
				break;
			case '\r\n';
				$this->_eol = "\r\n";
				break;
			case '<br>';
				$this->_eol = '<br>';
				break;
			}
		}

		if (!empty($_GET['sep'])) {
			switch ($_GET['sep']) {
			case '\t';
				$this->_sep = "\t";
				break;
			case ',';
				$this->_sep = ',';
				break;
			}
		}

	}

	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		switch ($_GET['f']) {
			case 'csv':
				return $this->csv($RES, $ARG);
			case 'csv+ccrs':
			case 'csv ccrs':
				return $this->csv_ccrs($RES, $ARG);
			case 'json':
				return $this->json($RES, $ARG);
			case 'json+wica':
			case 'json wica':
				return $this->json_wcia($RES, $ARG);
			case 'pdf':
				return $this->pdf($RES, $ARG);
			case 'png':
				if ($_GET['d'] == 'coa') {
					return $this->png_coa($RES, $ARG);
				}
				return $this->png($RES, $ARG);

		}

		var_dump($_GET);

		exit;

		// $cre = new \OpenTHC\CRE($_SESSION['pipe-token']);
		// $res = $cre->get('/qa');
		// $res = $res['result'];

		// // Headers
		// //header('Cache-Control: must-revalidate');
		// //header('Content-Description: Data Download');
		// //header(sprintf('Content-Disposition: attachment; filename="%s"', $name));
		// //header('Content-Transfer-Encoding: binary');
		// //header('Content-Type: application/octet-stream');
		// //header('Content-Type: text/csv', true);
		// header('Content-Type: text/plain');

		// $this->_fh = tmpfile();
		// $this->_output_header($res[0]);

		// foreach ($res as $rec) {
		// 	$this->_output_record($rec);
		// }

		// fseek($this->_fh, 0, SEEK_SET);
		// fpassthru($this->_fh);

		// exit(0);
	}

	/**
	 *
	 */
	function csv($RES, $ARG)
	{
		__exit_text('Dump as CSV', 501);
	}

	/**
	 * Dump as CCRS CSV File
	 */
	function csv_ccrs($RES, $ARG)
	{
		$dbc = $this->_container->DBC_User;

		$Lab_Result = new Lab_Result($dbc, $ARG['id']);
		if (empty($Lab_Result['id'])) {
			_exit_html_fail('<h1>Lab Result Not Found [CRD-123]</h1>', 400);
		}

		$Lab_Sample = new Lab_Sample($dbc, $Lab_Result['lab_sample_id']);
		if (empty($Lab_Sample['id'])) {
		}

		$data = $this->load_lab_result_full($Lab_Result['id']);

		require_once(APP_ROOT . '/view/pub/ccrs.php');

		// __exit_text('Dump as CSV/CCRS', 501);
		exit(0);
	}

	function json($RES, $ARG)
	{
		__exit_text('Dump as JSON', 501);
	}

	/**
	 * Generate a PDF Linking to this Page
	 */
	function pdf($RES, $ARG)
	{
		__exit_text('Generate COA?, Download Existing?', 501);
	}

	/**
	 * Generate a PDF Linking to this Page
	 */
	function png($RES, $ARG)
	{
		$qrCode = new \Endroid\QrCode\QrCode(sprintf('https://%s/pub/%s.html', $_SERVER['SERVER_NAME'], $ARG['id']));

		$img_name = sprintf('%s.png', $ARG['id']);

		// Generate a QR Code pointing to this page
		$RES = $RES->withHeader('content-disposition', sprintf('inline; filename="%s"', $img_name));
		$RES = $RES->withHeader('content-transfer-encoding', 'binary');
		$RES = $RES->withHeader('content-type: image/png');

		$RES = $RES->write( $qrCode->writeString() );

		return $RES;

	}

		/**
	 * Generate a PDF Linking to this Page
	 */
	function png_coa($RES, $ARG)
	{
		$qrCode = new \Endroid\QrCode\QrCode(sprintf('https://%s/pub/%s.pdf', $_SERVER['SERVER_NAME'], $ARG['id']));

		$img_name = sprintf('%s.png', $ARG['id']);

		// Generate a QR Code pointing to this page
		header(sprintf('content-disposition: inline; filename="%s"', $img_name));
		header('content-transfer-encoding: binary');
		header('content-type: image/png');

		echo $qrCode->writeString();

	}


//	function _ouptut_tofile()
//	{
//		$fh = tmpfile();
//
//	// Header
//	fputcsv($fh, array_values($col_spec));
//	fseek($fh, -1, SEEK_CUR);
//	fwrite($fh, "\r\n");
//
//	foreach ($res as $rec) {
//
//		$out = array();
//
//		foreach ($col_spec as $k => $x) {
//			$out[] = $rec[$k];
//		}
//
//		fputcsv($fh, array_values($out));
//
//		// Replace \n with \r\n
//		fseek($fh, -1, SEEK_CUR);
//		fwrite($fh, "\r\n");
//	}
//
//		fseek($fh, 0, SEEK_SET);
//
//		fpassthru($fh);
//	}

	function _output($row)
	{
		fputcsv($this->_fh, array_values($row), $this->_sep);

		// Replace \n with EOL
		fseek($this->_fh, -1, SEEK_CUR);
		fwrite($this->_fh, $this->_eol);

	}

	function _output_header($src)
	{
		$out = array();

		//$src = $rec;

		$out[] = 'global_id';
		$out[] = 'batch_type';
		$out[] = 'status';

		unset($src['global_id']);
		unset($src['batch_type']);
		unset($src['status']);

		$key_list = array_keys($src);
		sort($key_list);

		foreach ($key_list as $k) {
			$out[] = $k;
		}

		$this->_output($out);

	}

	function _output_record($rec)
	{
		$out = array();

		$out['global_id'] = $rec['global_id'];
		$out['batch_type'] = $rec['batch_type'];
		$out['status'] = $rec['status'];

		unset($rec['global_id']);
		unset($rec['batch_type']);
		unset($rec['status']);

		$key_list = array_keys($rec);
		sort($key_list);

		foreach ($key_list as $k) {

			$v = trim($rec[$k]);

			if (0 == strlen($v)) {
				$v = '-';
			}

			if (strpos($v, "\n") !== false) {
				die("k:$k");
			}

			$out[$k] = $v;

		}

		$this->_output($out);

	}

}
