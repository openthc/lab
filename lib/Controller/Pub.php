<?php
/**
 * Public View
 */

namespace App\Controller;

use \App\Lab_Metric;
use \App\Lab_Result;

class Pub extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		// Discover Preferred Output Format
		if (preg_match('/^(.+)\.(html|json|pdf|png)$/', $ARG['id'], $m)) { // EXT in URL
			$ARG['id'] = $m[1];
			$ext = $m[2];
		} elseif (preg_match('/^(html|json|pdf|png)$/', $_GET['of'], $m)) { // v1
			$ext = trim($m[1]);
		} elseif (preg_match('/^(html|json|pdf|png)$/', $_GET['f'], $m)) { // v0
			$ext = trim($m[1]);
		}


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

		$meta = json_decode($LR['meta'], true);

		$data = $this->loadSiteData();
		$data['menu0'] = 'hide';

		$data = array_merge($data, $meta);

		if ($_SESSION['License']['id'] == $LR['license_id']) {
			// I'm the Owner
			$data['mine'] = true;
		}

		// @deprecated should be on Result Create
		// if (empty($data['Result']['sum'])) {
		// 	$data['Result']['sum'] = $data['Result']['thc'] + $data['Result']['cbd'];
		// }
		$data['Result']['thc'] = sprintf('%0.2f', $data['Result']['thc']);
		$data['Result']['cbd'] = sprintf('%0.2f', $data['Result']['cbd']);
		$data['Result']['sum'] = sprintf('%0.2f', $data['Result']['sum']);

		$lm0 = new Lab_Metric($dbc_main);
		$metric_type_list = $lm0->getTypes();

		if (empty($data['MetricList'])) {
			$data = $LR->getMetricsOpenTHC($data);
		}

		$coa_file = $LR->getCOAFile();
		if (!empty($coa_file) && is_file($coa_file) && is_readable($coa_file)) {
			$data['Result']['coa_file'] = $coa_file;
		}

		$data['Sample'] = $meta['Sample'];
		if (empty($data['Sample']['id'])) {
			$data['Sample']['id'] = '- Not Found -';
			$data['Sample']['id'] = $data['Result']['global_for_inventory_id'];
		}

		$chk = $dbc_main->fetchRow('SELECT * FROM license WHERE id = :l0', [ ':l0' => $data['License_Source']['id'] ]);
		$data['License_Source'] = [
			'id' => $chk['id'],
			'name' => $chk['name'],
			'code' => $chk['code'],
			'guid' => $chk['guid'],
		];

		$data['Product'] = $meta['Product'];
		if (empty($data['Product']['name'])) {
			$data['Product']['name'] = '- Not Found -';
		}

		$data['Variety']  = $meta['Variety'];

		switch ($ext) {
		case '':
		case 'html':
			// Nothing
			break;
		case 'json':
			return $RES->withJSON($this->cleanData($data), 200, JSON_PRETTY_PRINT);
		case 'pdf':

			// If PDF is gone
			// Redirect to the same page, HTML version
			if ( ! empty($data['Result']['coa_file'])) {

				$coa_name = sprintf('COA-%s.pdf', $QAR['id']);

				header(sprintf('content-disposition: inline; filename="%s"', $coa_name));
				header('content-transfer-encoding: binary');
				header('content-type: application/pdf');

				readfile($data['Result']['coa_file']);

				exit(0);

			}

			// If the PDF is not found we have to redirect
			// And clear some parameters (or else it would loop)
			unset($_GET['of']);
			unset($_GET['f']);
			$url = sprintf('/pub/%s?%s', $ARG['id'], http_build_query($_GET));
			$url = rtrim($url, '?');
			return $RES->withRedirect($url);

			break;

		case 'png':

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

		$file = 'pub.php';

		return $RES->write( $this->render($file, $data) );

	}

	/**
	 *
	 */
	private function cleanData($data)
	{
		// _ksort_r($data);

		$ret = [];
		$ret['License'] = [];
		$ret['License']['id'] = $data['License']['id'];
		$ret['License']['code'] = $data['License']['code'];
		$ret['License']['guid'] = $data['License']['guid'];
		$ret['License']['name'] = $data['License']['name'];

		$ret['Lot'] = [
			'id' => $data['Sample']['id'],
			'guid' => $data['Sample']['guid'],
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

		$ret['Result'] = [
			'id' => $data['Result']['id'],
			'metric_list' => [], //  $data['MetricList'],
		];

		foreach ($data['metric_list'] as $m) {
			if (null === $m['qom']) {
				continue;
			}
			$ret['Result']['metric_list'][ $m['id'] ] = [
				'id' => $m['id'],
				'name' => $m['name'],
				'type' => $m['type'],
				'qom' => $m['qom'],
				'uom' => $m['uom'],
			];
		}

		_ksort_r($ret);

		return $ret;
	}

}
