<?php
/**
 * Generate Downloadable Types for the Files?
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Report;

use OpenTHC\Lab\Lab_Report;
use OpenTHC\Lab\Lab_Result;
use OpenTHC\Lab\Lab_Sample;

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

		if ( ! empty($ARG['fid'])) {

			$rec = $dbc_user->fetchRow('SELECT * FROM lab_report_file WHERE id = :lf0 AND lab_report_id = :lr0', [
					':lr0' => $Lab_Report['id'],
					':lf0' => $ARG['fid']
			]);
			if (empty($rec['id'])) {
					_exit_html_warn('<h1>Lab Report Data File Not Found [CRD-073]', 404);
			}

			header('content-transfer-encoding: binary');
			header(sprintf('content-type: %s', $rec['type']));
			header(sprintf('content-disposition: inline; filename="%s"', $rec['name']));

			fpassthru($rec['body']);

			exit;
		}

		$data = $this->_load_data($dbc_user, $Lab_Report);

		// Alias the data into this field
		$data['Lab_Result'] = $data['Lab_Report'];

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
		// Have to Make it look like the way the 'result' outputter wants it to be.
		$dbc_user = $this->_container->DBC_User;
		$data['Lot'] = $dbc_user->fetchRow('SELECT * FROM inventory WHERE id = :i0', [
			':i0' => $data['Lab_Sample']['inventory_id'] ?: $data['Lab_Sample']['lot_id']
		]);

		$dt0 = new \DateTime($data['Lab_Result']['approved_at']);

		$RES = $RES->withHeader('content-disposition', sprintf('inline; filename="labtest_%s_%s.csv"'
			, $data['License_Laboratory']['code']
			, $dt0->format('YmdHis')
		));

		$RES = $RES->withHeader('content-disposition', sprintf('inline; filename="labtest_%s_%s_%s.csv"'
			, $data['License_Laboratory']['code']
			, $dt0->format('YmdHis')
			, $data['Lab_Sample']['name']
		));


		$RES = $RES->withHeader('content-transfer-encoding', 'binary');
		$RES = $RES->withHeader('content-type', 'text/plain; charset=utf-8');

		ob_start();
		require_once(APP_ROOT . '/view/pub/csv-ccrs.php');
		$csv_output = ob_get_clean();

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
		$json = require_once(APP_ROOT . '/view/pub/json.wcia-2022-062.php');

		$RES = $RES->withHeader('content-disposition', sprintf('inline; filename="Lab_Report_%s.json"', $data['Lab_Result']['id']));
		$RES = $RES->withHeader('content-transfer-encoding', 'binary');
		$RES = $RES->withHeader('content-type', 'application/json');

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
		// $License = new License($dbc, $data['Source_License']['id']);
		// $License->getIcon();
		$url = sprintf('https://directory.openthc.com/api/license/%s', $data['Source_License']['id']);
		$req = _curl_init($url);
		$res = curl_exec($req);
		$res = json_decode($res, true);
		$res = $res['data'];

		$data['Source_License']['icon'] = sprintf('https://directory.openthc.com/img/company/%s/icon.png', $res['company']['id']);

		$data['footer_text'] = $C0->getOption('coa/footer');

		if ('dump' == $_GET['_']) {
			__exit_text($data);
		}

		ob_start();
		require_once(APP_ROOT . '/view/pub/coa-pdf.php');
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
		$req_path = [];
		$req_path[] = 'lab';
		$req_path[] = $ARG['id'];
		$req_path[] = 'coa.html';
		$url = _openthc_pub_path(implode('/', $req_path));
		$res = \Endroid\QrCode\Builder\Builder::create()
			->writer(new \Endroid\QrCode\Writer\PngWriter())
			->writerOptions([])
			->data($url)
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
