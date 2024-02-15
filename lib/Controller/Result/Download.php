<?php
/**
 * Generate Downloadable Types for the Files?
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Result;

use OpenTHC\Lab\Lab_Result;
use OpenTHC\Lab\Lab_Sample;

class Download extends \OpenTHC\Lab\Controller\Result\View
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
			case 'json+wcia':
			case 'json wcia':
				return $this->json_wcia($RES, $ARG);
			case 'pdf':
				return $this->pdf($RES, $ARG);
			case 'png':
				return $this->png($RES, $ARG);
			case 'png+coa':
			case 'png coa':
				return $this->png_coa($RES, $ARG);
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

		$data['Lot'] = $dbc->fetchRow('SELECT * FROM inventory WHERE id = :i0', [
			':i0' => $Lab_Sample['lot_id']
		]);
		$data['License_Laboratory'] = $dbc->fetchRow('SELECT * FROM license WHERE id = :l0', [
			':l0' => $data['Lab_Sample']['license_id']
		]);
		$data['Source_License'] = $dbc->fetchRow('SELECT * FROM license WHERE id = :l0', [
			':l0' => $data['Lab_Sample']['license_id_source']
		]);

		header('content-type: text/plain');

		require_once(APP_ROOT . '/view/pub/csv-ccrs.php');

		exit(0);

	}

	function json($RES, $ARG)
	{
		__exit_text('Dump as JSON', 501);
	}

	/**
	 *
	 */
	function json_wcia($RES, $ARG)
	{
		$data = $this->_load_data($ARG);

		// require_once(APP_ROOT . '/view/pub/json-wcia.php');
		$json = require_once(APP_ROOT . '/view/pub/json.wcia-2022-062.php');

		return $RES->withJSON($json, 200, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);


	}

	/**
	 * Generate a PDF Linking to this Page
	 */
	function pdf($RES, $ARG)
	{
		$data = $this->_load_data($ARG);

		$sample_file = sprintf('%s/var/%s/sample/%s.*', APP_ROOT, $_SESSION['Company']['id'], $data['Lab_Sample']['id']);
		$sample_file = glob($sample_file);
		if ( ! empty($sample_file)) {
			$data['Lab_Sample']['img_file'] = $sample_file[0];
		}

		$dbc = $this->_container->DBC_User;
		$C0 = new \OpenTHC\Company($dbc, $_SESSON['Company']['id']);
		$data['License_Laboratory']['address_line_1'] = $C0->getOption('coa/address/line/1');
		$data['License_Laboratory']['address_line_2'] = $C0->getOption('coa/address/line/2');
		$data['License_Laboratory']['email'] =   $C0->getOption('coa/email');
		$data['License_Laboratory']['phone'] =   $C0->getOption('coa/phone');
		$data['License_Laboratory']['website'] = $C0->getOption('coa/website');
		$data['License_Laboratory']['icon'] =    $C0->getOption('coa/icon');

		//
		// $data['Client'] = [];
		// $data['Client']['icon'] =

		$data['footer_text'] = $C0->getOption('coa/footer');

		if ('dump' == $_GET['_']) {
			__exit_text($data);
		}

		// Filter out Lab Metrics w/o Metrics
		// ['metric']

		require_once(APP_ROOT . '/view/pub/coa-pdf.php');

		exit(0);

	}

	/**
	 * Generate a PDF Linking to this Page
	 */
	function png($RES, $ARG)
	{
		// $qrCode = new \Endroid\QrCode\QrCode(sprintf('https://%s/pub/%s.html', $_SERVER['SERVER_NAME'], $ARG['id']));
		$res = \Endroid\QrCode\Builder\Builder::create()
			->writer(new \Endroid\QrCode\Writer\PngWriter())
			->writerOptions([])
			->data('Custom QR code contents')
			->encoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
			->errorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh())
			// ->size(300)
			// ->margin(10)
			// ->roundBlockSizeMode(new \Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin())
			// ->logoPath(__DIR__.'/assets/symfony.png')
			// ->labelText('This is the label')
			// ->labelFont(new \Endroid\QrCode\Label\Font\NotoSans(20))
			// ->labelAlignment(new LabelAlignmentCenter())
			->build();

		// var_dump($res); exit;

		$img_name = sprintf('%s.png', $ARG['id']);

		// Generate a QR Code pointing to this page
		$RES = $RES->withHeader('content-disposition', sprintf('inline; filename="%s"', $img_name));
		$RES = $RES->withHeader('content-transfer-encoding', 'binary');
		$RES = $RES->withHeader('content-type', 'image/png');

		$RES = $RES->write( $res->getString() );

		return $RES;

	}

	/**
	 * Generate a PDF Linking to this Page
	 */
	function png_coa($RES, $ARG)
	{
		// $qrCode = new \Endroid\QrCode\QrCode(sprintf('https://%s/pub/%s.pdf', $_SERVER['SERVER_NAME'], $ARG['id']));

		$img_name = sprintf('%s.png', $ARG['id']);

		// Generate a QR Code pointing to this page
		header(sprintf('content-disposition: inline; filename="%s"', $img_name));
		header('content-transfer-encoding: binary');
		header('content-type: image/png');

		// echo $qrCode->writeString();

	}

	/**
	 * Load the Full Lab Data
	 */
	function _load_data($ARG)
	{
		$dbc = $this->_container->DBC_User;

		$data = $this->load_lab_result_full($ARG['id']);
		// if (empty($Lab_Result['id'])) {
		// 	_exit_html_fail('<h1>Lab Result Not Found [CRD-123]</h1>', 400);
		// }
		// $Lab_Sample = new Lab_Sample($dbc, $Lab_Result['lab_sample_id']);
		// if (empty($Lab_Sample['id'])) {
		// }

		$Lab_Result = new Lab_Result($dbc, $data['Lab_Result']);

		$data['Lab_Result_Section_Metric_list'] = $Lab_Result->getMetrics_Grouped();

		$data['Lab_Result_Metric_Type_list'] = $Lab_Result->getMetricListGrouped();

		// $data['Lot'] = $dbc->fetchRow('SELECT * FROM inventory WHERE id = :i0', [
		// 	':i0' => $data['Lab_Sample']['lot_id']
		// ]);

		// $data['Product'] = $dbc->fetchRow('SELECT * FROM product WHERE id = :i0', [
		// 	':i0' => $data['Lot']['product_id']
		// ]);

		// $data['Product_Type'] = $dbc->fetchRow('SELECT * FROM product_type WHERE id = :i0', [
		// 	':i0' => $data['Product']['product_type_id']
		// ]);

		$data['License_Laboratory'] = $dbc->fetchRow('SELECT * FROM license WHERE id = :l0', [
			':l0' => $data['Lab_Result']['license_id']
		]);

		$data['Source_License'] = $dbc->fetchRow('SELECT * FROM license WHERE id = :l0', [
			':l0' => $data['Lab_Sample']['license_id_source']
		]);

		return $data;

	}

	/**
	 *
	 */
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
