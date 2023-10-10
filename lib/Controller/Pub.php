<?php
/**
 * Public View
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller;

use OpenTHC\Lab\Lab_Metric;
use OpenTHC\Lab\Lab_Result;

class Pub extends \OpenTHC\Lab\Controller\Base
{
	protected $type_want = 'text/html';

	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$this->type_want = $this->_get_type_want($ARG);

		$dbc_main = $this->_container->DBC_Main;

		// Get Result
		$LR = new Lab_Result($dbc_main, $ARG['id']);
		if (empty($LR['id'])) {
			$data = array(
				'Page' => array('title' => 'Not Found [CRS-030]'),
			);
			$RES = $RES->withStatus(404);
			return $RES->write( $this->render('result/404.php', $data) );
		}

		$data = $this->loadSiteData();
		$data['menu0'] = 'hide';

		// Load Database Data
		$meta = json_decode($LR['meta'], true);
		switch ($meta['@context']) {
			case 'http://openthc.org/lab/2022':
				// My Published Way
				$data['Lab_Sample'] = $meta['lab_sample'];
				$data['Lab_Result'] = $meta['lab_result'];
				$data['Product'] = $meta['product'];
				$data['Product_Type'] = $meta['product_type'];
				$data['Variety'] = $meta['variety'];
				$data['Lab_Result_Metric_list'] = []; // $meta['lab_result_metric'];
				$data['Lab_Result_Section_Metric_list'] = [];

				foreach ($meta['lab_result_metric'] as $lrm) {

					// var_dump($lrm); exit;

					$lmt_id = $lrm['lab_metric_type_id'];

					if (empty($data['Lab_Result_Section_Metric_list'][ $lmt_id ])) {
						$data['Lab_Result_Section_Metric_list'][ $lmt_id ] = [
							'id' => $lrm['lab_metric_type_id'],
							'name' => $lrm['lab_metric_type_name'],
							'sort' => $lrm['lab_metric_type_sort'],
							'metric_list' => [],
						];
					}

					// Metric List
					$data['Lab_Result_Section_Metric_list'][ $lmt_id ]['metric_list'][] = [
						'id' => $lrm['lab_metric_id'],
						'sort' => $lrm['lab_metric_sort'],
						'name' => $lrm['lab_metric_name'],
					];

					if (empty($data['Lab_Result_Metric_list'][ $lrm['lab_metric_id'] ])) {
						$data['Lab_Result_Metric_list'][ $lrm['lab_metric_id'] ] = [
							// 'id' => $lrm['']
							'stat' => $lrm['stat'],
							'qom' => $lrm['qom'],
							'uom' => $lrm['uom'],
						];
					}

				}

				break;

			default:

				// Patch Result
				if (is_string($meta['Lab_Result']['meta'])) {
					$meta['Lab_Result']['meta'] = json_decode($meta['Lab_Result']['meta'], true);
				}
				// $meta['Lab_Result']['id'] = $LR['id'];
				$meta['Lab_Result']['id_nice'] = _nice_id($meta['Lab_Result']['id'], $meta['Lab_Result']['guid']);
				// $meta['Lab_Result']['thc'] = sprintf('%0.2f', $meta['Lab_Result']['thc']);
				// $meta['Lab_Result']['cbd'] = sprintf('%0.2f', $meta['Lab_Result']['cbd']);
				// $meta['Lab_Result']['sum'] = sprintf('%0.2f', $meta['Lab_Result']['sum']);
				if (empty($meta['Lab_Result']['approved_at'])) {
					$meta['Lab_Result']['approved_at'] = $meta['Lab_Report']['approved_at'];
				}
				if (empty($meta['Lab_Result']['expires_at'])) {
					$meta['Lab_Result']['expires_at'] = $meta['Lab_Report']['expires_at'];
				}

				// Patch Sample
				if (is_string($meta['Lab_Sample']['meta'])) {
					$meta['Lab_Sample']['meta'] = json_decode($meta['Lab_Sample']['meta'], true);
				}
				$meta['Lab_Sample']['id_nice'] = _nice_id($meta['Lab_Sample']['id'], $meta['Lab_Sample']['guid']);

				// Which Type v2015, v2018, v2021-WCIA
				if (empty($meta['Lab_Result_Metric_list'])) {
					$meta['Lab_Result_Metric_list'] = [];
				}

		}

		$data = array_merge($data, $meta);

		$key_list = array_keys($data['Lab_Result_Metric_list']);
		$chk = $key_list[0];
		if (preg_match('/^0[0-9A-Z]{25}$/', $chk)) { // v2018
			// OK, do Nothing
		} else {
			// throw new \Exception('@deprecated lab-result-metric-list [LCP-073]');
			// @todo see if anyone still has this v2022-WCIA style
			// Yes, many folk still have this in their MAIN.lab_result dataset /djb 2022-259
			// It's a Nested List, Un Flatten, it's Grouped
			$lab_result_metric = [];
			foreach ($data['Lab_Result_Metric_list'] as $lab_group_name => $lab_group_data) {
				foreach ($lab_group_data as $lm_id => $lrm) {
					$lab_result_metric[ $lm_id ] = $lrm;
				}
			}
			$data['Lab_Result_Metric_list'] = $lab_result_metric;
		}

		if ($_SESSION['License']['id'] == $LR['license_id']) {
			// I'm the Owner
			$data['mine'] = true;
		}
		// @hack Cake Brands app#1368
		// @hack Cake / Delta Distribution app#1801
		// @todo Something else /mbw 2023-073
		if ('01EZ8C7095PB9SAQ1FEY9QCEXT' == $LR['license_id']) {
			if ('pdf' !== $this->type_want) {
				$RES->withRedirect($_SERVER['REQUEST_URI'].'.pdf');
			}
			/*
			return $RES->withRedirect('https://cakebrand.com/');
			*/
		}

		// Load COA File
		// Should be Pointing to the Lab Portal when LR has Public Flags Public
		// @todo this should already be set
		$coa_file = $LR->getCOAFile();
		if ( ! empty($coa_file) && is_file($coa_file) && is_readable($coa_file) && filesize($coa_file) > 0) {
			$data['Lab_Result']['coa_file'] = $coa_file;
		} else {
			$data['Lab_Result']['coa_file'] = null;
		}

		if (empty($data['License_Source']['id'])) {
			$chk = $dbc_main->fetchRow('SELECT id, name, code, guid FROM license WHERE id = :l0', [ ':l0' => $data['Lab_Sample']['license_id_source'] ]);
			$data['License_Source'] = [
				'id' => $chk['id'],
				'name' => $chk['name'],
				'code' => $chk['code'],
				'guid' => $chk['guid'],
			];
		}

		if (empty($data['Product']['name'])) {
			$data['Product']['name'] = '- Not Found -';
		}

		switch ($this->type_want) {
		case 'text/html':
		case 'html':
			break;
		case 'text/csv+ccrs':
			// No Session but needs Session Data
			// $chk = $dbc_main->fetchRow('SELECT * FROM license WHERE id = :l0', [ ':l0' => $data['Lab_Sample']['license_id']]);
			// $data['License_Laboratory'] = [
			// 	'id' => $chk['id'],
			// 	'code' => $chk['guid'],
			// 	'name' => $chk['name'],
			// ];
			require_once(APP_ROOT . '/view/pub/csv-ccrs.php');
			exit(0);
			break;
		case 'text/plain':
			require_once(APP_ROOT . '/view/pub/text.php');
			exit(0);
			break;
		case 'application/json':

			$output_data = [];

			if ('wcia' == $_GET['f']) {
				switch ($data['@context']) {
					case 'http://openthc.org/lab/2021':
						$output_data = require_once(APP_ROOT . '/view/pub/json.wcia-2022-062.php');
						break;
					default:
						$output_data = require_once(APP_ROOT . '/view/pub/json.wcia.php');
				}
			} else {
				$output_data = $this->cleanData($data);
			}

			return $RES->withJSON($output_data, 200, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

			break;

		case 'application/pdf':

			// @see https://stackoverflow.com/questions/50570900/js-fetch-not-getting-headers-on-response
			// @see https://stackoverflow.com/questions/43344819/reading-response-headers-with-fetch-api
			header('access-control-allow-origin: *');
			header('access-control-expose-headers: content-disposition');
			header('access-control-max-age: 600');

			$download_file_name = sprintf('%s-COA.pdf', $data['Lab_Result']['guid']);
			if ( ! empty($_GET['n'])) {
				if (preg_match('/^[\w\-]+\.pdf$/', $_GET['n'])) {
					$download_file_name = $_GET['n'];
				}
			}
			// header(sprintf('content-disposition: attachment; filename="%s"', $name));

			switch ($data['@context']) {
				case 'http://openthc.org/lab/2022':
				case 'http://openthc.org/lab/2021':

					if ( ! empty($_GET['v']) && ('2022-065' == $_GET['v'])) {
						// return $RES->write(
						require_once(APP_ROOT . '/view/pub/pdf.php');
						exit(0);
					}

					if ( ! empty($data['Lab_Result']['coa_file'])
						&& is_file($data['Lab_Result']['coa_file'])) {

						header(sprintf('content-disposition: inline; filename="%s"', $download_file_name));
						header('content-transfer-encoding: binary');
						header('content-type: application/pdf');

						readfile($data['Lab_Result']['coa_file']);

						exit(0);

					}

					break;
				default:
					// $output_data = require_once(APP_ROOT . '/view/pub/json.wcia.php');
					if ( ! empty($data['Lab_Result']['coa_file'])
						&& is_file($data['Lab_Result']['coa_file'])) {

						header(sprintf('content-disposition: inline; filename="%s"', $download_file_name));
						header('content-transfer-encoding: binary');
						header('content-type: application/pdf');

						readfile($data['Lab_Result']['coa_file']);

						exit(0);

					}

			}

			// If PDF is gone
			// Redirect to the same page, HTML version

			// If the PDF is not found we have to redirect
			// And clear some parameters (or else it would loop)
			unset($_GET['of']);
			unset($_GET['f']);
			$_GET['e'] = 'lcp-224';
			$url = sprintf('/pub/%s.html?%s', $ARG['id'], http_build_query($_GET));
			$url = rtrim($url, '?');

			return $RES->withRedirect($url);

			break;

		case 'image/png':

			$qrCode = new \Endroid\QrCode\QrCode(sprintf('https://%s/pub/%s.html', $_SERVER['SERVER_NAME'], $ARG['id']));

			$coa_name = sprintf('%s.png', $ARG['id']);

			// Generate a QR Code pointing to this page
			header(sprintf('content-disposition: inline; filename="%s"', $coa_name));
			header('content-transfer-encoding: binary');
			header('content-type: image/png');

			echo $qrCode->writeString();

			exit(0);

			break;

		}

		$data['Page'] = array('title' => sprintf('Result :: %s', $LR['id']));
		$data['License_Current'] = $_SESSION['License'];

		if ('dump' == $_GET['_dump']) {
			__exit_text($data);
		}

		switch ($data['@context']) {
			case 'http://openthc.org/lab/2022':
			case 'http://openthc.org/lab/2021':
				return $RES->write( $this->render('pub/html-2022-062.php', $data) );
				break;
		}

		// Legacy Default
		return $RES->write( $this->render('pub/html.php', $data) );

	}

	/**
	 *
	 */
	private function cleanData($data)
	{
		$ret = [];
		$ret['License'] = [];
		$ret['License']['id'] = $data['License']['id'];
		$ret['License']['code'] = $data['License']['code'];
		$ret['License']['guid'] = $data['License']['guid'];
		$ret['License']['name'] = $data['License']['name'];

		$ret['Lot'] = [
			'id' => $data['Lab_Sample']['id'],
			'guid' => $data['Lab_Sample']['guid'],
		];
		$ret['Product'] = [
			'id' => $data['Product']['id'],
			'guid' => $data['Product']['guid'],
			'name' => $data['Product']['name'],
			'package' => [
				'type' => $data['Product']['package_type'],
				'uom' => $data['Product']['package_unit_uom'],
			],
		];

		$ret['Variety'] = [
			'id' => $data['Variety']['id'],
			'guid' => $data['Variety']['guid'],
			'name' => $data['Variety']['name'],
		];

		$ret['Laboratory'] = [];

		$ret['Lab_Sample'] = [
			'id' => $data['Lab_Sample']['id'],
			'guid' => $data['Lab_Sample']['guid'],
			'name' => $data['Lab_Sample']['name'],
			'qty' => $data['Lab_Sample']['qty'],
		];

		$ret['Lab_Result'] = [
			'id' => $data['Lab_Result']['id'],
			'metric_list' => $data['Lab_Result_Metric_list'],
		];

		if ( ! empty($ret['Lab_Result']['metric_list'])) {

			$lrm_list = [];
			$key_list = array_keys($ret['Lab_Result']['metric_list']);
			foreach ($key_list as $key) {

				$m = $ret['Lab_Result']['metric_list'][$key];

				if (null === $m['qom']) {
					continue;
				}

				$lrm_list[ $m['id'] ] = [
					'id' => $m['id'],
					'name' => $m['name'],
					'type' => $m['type'],
					'sort' => intval($m['sort']),
					'qom' => $m['qom'],
					'uom' => $m['uom'],
				];
			}

			uasort($lrm_list, function($a, $b) {
				return ($a['sort'] > $b['sort']);
			});

			$ret['Lab_Result']['metric_list'] = $lrm_list;
		}

		return $ret;

	}

	/**
	 *
	 */
	protected function _get_type_want($ARG)
	{
		$ret = $this->type_want;

		$x = $_SERVER['HTTP_ACCEPT'];
		$x = explode(',', $x);
		$ret = trim($x[0]);

		// Discover Preferred Output Format
		$ext = $ARG['type'];
		if (preg_match('/^(.+)\.(html|json|pdf|png|txt)$/', $ARG['id'], $m)) { // EXT in URL
			$ext = $m[2];
		} elseif (preg_match('/^(html|json|pdf|png|txt)$/', $_GET['of'], $m)) { // v1
			$ext = trim($m[1]);
		} elseif (preg_match('/^(html|json|pdf|png|txt)$/', $_GET['f'], $m)) { // v0
			$ext = trim($m[1]);
		}

		switch ($ext) {
			case 'html':
				$ret = 'text/html';
				break;
			case 'json':
				$ret = 'application/json';
				break;
			case 'pdf':
				$ret = 'application/pdf';
				break;
			case 'png':
				$ret = 'image/png';
				break;
			case 'txt':
				$ret = 'text/plain';
				break;
			case 'ccrs.txt':
				$ret = 'text/csv+ccrs';
				break;
			case 'wcia.json':
				$ret = 'application/json';
				$_GET['f'] = 'wcia';
		}

		switch ($ret) {
			case 'application/pdf':
			case 'application/json':
			case 'image/png':
			case 'text/csv+ccrs':
			case 'text/html':
			case 'text/plain':
				// OK
				break;
			default:
				$ret = 'text/html';
		}

		return $ret;

	}

}
