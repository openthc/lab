<?php
/**
 * Generate Downloadable Types for the Files?
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Report;

use OpenTHC\Lab\Lab_Report;
use App\Lab_Result;
use App\Lab_Sample;

class Download extends \OpenTHC\Lab\Controller\Report\Single
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
		// Get Result
		$dbc_user = $this->_container->DBC_User;
		$Lab_Report = new Lab_Report($dbc_user, $ARG['id']);
		if (empty($Lab_Report['id'])) {
			_exit_html_warn('<h1>Lab Report Not Found [CRS-030]', 404);
		}
		$data = $this->_load_data($dbc_user, $Lab_Report);

		// Alias the data into this field
		$data['Lab_Result'] = [
			'id' => $Lab_Report['id'],
			'guid' => $data['Lab_Sample']['name'],
			'name' => $Lab_Report['name'],
			'created_at' => $Lab_Report['created_at'],
		];


		switch ($_GET['f']) {
			case 'csv':
				return $this->csv($RES, $ARG, $data);
			case 'csv+ccrs':
			case 'csv ccrs':
				return $this->csv_ccrs($RES, $ARG, $data);
			case 'json':
				return $this->json($RES, $ARG, $data);
			case 'json+wcia':
			case 'json wcia':
				return $this->json_wcia($RES, $ARG, $data);
			case 'pdf':
				return $this->pdf($RES, $ARG, $data);
			case 'png':
				return $this->png($RES, $ARG, $data);
			case 'png+coa':
			case 'png coa':
				return $this->png_coa($RES, $ARG, $data);
		}

		_exit_html_fail('Invalid Request [CRD-079]', 400);

	}

	/**
	 *
	 */
	function csv($RES, $ARG, $data)
	{
		__exit_text('Dump as CSV', 501);
	}

	/**
	 * Dump as CCRS CSV File
	 */
	function csv_ccrs($RES, $ARG, $data)
	{
		$dbc = $this->_container->DBC_User;

		$Lab_Report = new Lab_Report($dbc, $ARG['id']);
		if (empty($Lab_Report['id'])) {
			_exit_html_fail('<h1>Lab Report Not Found [CRD-123]</h1>', 400);
		}

		$Lab_Sample = new Lab_Sample($dbc, $Lab_Report['lab_sample_id']);
		if (empty($Lab_Sample['id'])) {
			$Lab_Sample = [
				'id' => '',
				'guid' => '-notset-',
				'name' => '-notset-',
			];
		}

		$data = [];

		// Have to Make it look like the way the 'result' outputter wants it to be.
		$data['Lab_Result'] = [
			'id' => $Lab_Report['id'],
			'guid' => $Lab_Report['guid'],
			'name' => $Lab_Report['guid'],
			'created_at' => $Lab_Report['created_at'],
		];
		$data['Lab_Sample'] = $Lab_Sample->toArray();

		$m = $Lab_Report->getMeta();
		$data['Lab_Result_Metric_list'] = $m['lab_metric_list'];

		$data['Lot'] = $dbc->fetchRow('SELECT * FROM inventory WHERE id = :i0', [
			':i0' => $Lab_Sample['lot_id']
		]);
		$data['License_Laboratory'] = $dbc->fetchRow('SELECT * FROM license WHERE id = :l0', [
			':l0' => $Lab_Sample['license_id']
		]);
		$data['License_Source'] = $dbc->fetchRow('SELECT * FROM license WHERE id = :l0', [
			':l0' => $Lab_Sample['license_id_source']
		]);

		ob_start();
		require_once(APP_ROOT . '/view/result/csv-ccrs.php');
		$csv_output = ob_get_clean();

		$RES = $RES->withHeader('content-type', 'text/plain; charset=utf-8');
		return $RES->write($csv_output);

	}

	function json($RES, $ARG, $data)
	{
		__exit_text('Dump as JSON', 501);
	}

	/**
	 *
	 */
	function json_wcia($RES, $ARG, $data)
	{
		// require_once(APP_ROOT . '/view/result/json-wcia.php');
		$json = require_once(APP_ROOT . '/view/pub/json.wcia-2022-062.php');

		$RES = $RES->withHeader('content-disposition', sprintf('inline; filename="Lab_Report_%s.json"', $data['Lab_Result']['id']));
		return $RES->withJSON($json, 200, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

	}

	/**
	 * Generate a PDF Linking to this Page
	 */
	function pdf($RES, $ARG, $data)
	{
		// $sample_file = sprintf('%s/var/%s/sample/%s.*', APP_ROOT, $_SESSION['Company']['id'], $data['Lab_Sample']['id']);
		// $sample_file = glob($sample_file);
		// if ( ! empty($sample_file)) {
		// 	$data['Lab_Sample']['img_file'] = $sample_file[0];
		// }

		$dbc = $this->_container->DBC_User;
		$C0 = new \OpenTHC\Company($dbc, $_SESSON['Company']['id']);
		$data['License_Laboratory']['address_line_1'] = $C0->getOption('coa/address/line/1');
		$data['License_Laboratory']['address_line_2'] = $C0->getOption('coa/address/line/2');
		$data['License_Laboratory']['email'] =   $C0->getOption('coa/email');
		$data['License_Laboratory']['phone'] =   $C0->getOption('coa/phone');
		$data['License_Laboratory']['website'] = $C0->getOption('coa/website');
		$data['License_Laboratory']['icon'] =    $C0->getOption('coa/icon');

		// $data['License_Client'] = [];
		// $License = new License($dbc, $data['License_Source']['id']);
		// $License->getIcon();
		$url = sprintf('https://directory.openthc.com/api/license/%s', $data['License_Source']['id']);
		$req = _curl_init($url);
		$res = curl_exec($req);
		$res = json_decode($res, true);
		$res = $res['data'];

		$data['License_Source']['icon'] = sprintf('https://directory.openthc.com/img/company/%s/icon.png', $res['company']['id']);

		$data['footer_text'] = $C0->getOption('coa/footer');

		// Base the Lab Metric List
		$data['Lab_Result_Metric_list'] = [];
		foreach ($data['Lab_Report']['meta']['lab_result_metric_list'] as $x) {

			$lm = $dbc->fetchRow('SELECT * FROM lab_metric WHERE id = :lm0', [ ':lm0' => $x['lab_metric_id'] ]);
			$lm['meta'] = json_decode($lm['meta'], true);

			$x['name'] = $lm['name'];
			$x['meta']['max'] = $lm['meta']['max'];

			$data['Lab_Result_Metric_list'][ $x['lab_metric_id'] ] = $x;
		}

		$data['Lab_Result_Section_Metric_list'] = [];
		foreach ($data['lab_metric_type_list'] as $lmt) {
			$lmt['metric_list'] = [];
			$data['Lab_Result_Section_Metric_list'][ $lmt['id'] ] = $lmt;
		}

		foreach ($data['Lab_Result_Metric_list'] as $lrm) {
			$lmt_id = $lrm['lab_metric_type_id'];
			$data['Lab_Result_Section_Metric_list'][ $lmt_id ]['metric_list'][ $lrm['lab_metric_id'] ] = [
				'id' => $lrm['lab_metric_id'],
			];
		}

		if ('dump' == $_GET['_']) {
			__exit_text($data);
		}

		ob_start();
		require_once(APP_ROOT . '/view/result/coa-pdf.php');
		$pdf_body = ob_get_clean();

		$RES = $RES->withHeader('content-disposition', sprintf('inline; filename="Lab_Report_%s.pdf"', $data['Lab_Result']['id']));
		$RES = $RES->withHeader('content-transfer-encoding', 'binary');
		$RES = $RES->withHeader('content-type', 'application/pdf');

		return $RES->write($pdf_body);

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

		// exit(0);

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
