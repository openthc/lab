<?php
/**
 * View a Result
 */

namespace App\Controller\Result;

use \App\Lab_Result;

class Share extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		if (preg_match('/^(.+)\.(html|json|pdf|png)$/', $ARG['id'], $m)) {
			$ARG['id'] = $m[1];
			$ext = trim($m[2], '.');
		}

		$dbc_main = $this->_container->DBC_Main;

		// Get Result
		$LR = new Lab_Result($dbc_main, $ARG['id']);
		if (empty($LR['id'])) {
			$data = array(
				'Page' => array('title' => 'Not Found [CRS-025]'),
				'lab_result_id' => null,
			);
			$RES = $RES->withStatus(404);
			return $this->_container->view->render($RES, 'page/result/404.html', $data);
		}

		$data = $this->loadSiteData();
		$meta = json_decode($LR['meta'], true);
		$data = array_merge($data, $meta);

		if ($_SESSION['License']['id'] == $LR['license_id']) {
			// I'm the Owner
			$data['mine'] = true;
		}

		// @deprecated should be on Result Create
		if (empty($data['Result']['sum'])) {
			$data['Result']['sum'] = $data['Result']['thc'] + $data['Result']['cbd'];
		}
		$data['Result']['thc'] = sprintf('%0.2f', $data['Result']['thc']);
		$data['Result']['cbd'] = sprintf('%0.2f', $data['Result']['cbd']);
		$data['Result']['sum'] = sprintf('%0.2f', $data['Result']['sum']);

		if (empty($data['MetricList'])) {
			if (!empty($data['Result']['meta']['for_inventory'])) {
				$data = $this->_map_leafdata($data);
				// _exit_text($data);
			}
		}

		$coa_file = $LR->getCOAFile();
		if (!empty($coa_file) && is_file($coa_file) && is_readable($coa_file)) {
			$data['Result']['coa_file'] = $coa_file;
		}

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

		if (empty($data['Product']['name'])) {
			$data['Product']['name'] = '- Not Found -';
		}

		// Output Type
		switch ($ext) {
		case '':
		case 'html':
			// Nothing
			break;
		case 'json':
			unset($data['Page']);
			$data = $this->_clean_data($data);
			_ksort_r($data);
			return $RES->withJSON($data, 200, JSON_PRETTY_PRINT);
		case 'pdf':

			if (empty($data['Result']['coa_file'])) {
				_exit_text('PDF Copy of COA Not Found, please contact the supplier or laboratory', 404);
			}

			$coa_name = sprintf('COA-%s.pdf', $QAR['id']);

			header(sprintf('content-disposition: inline; filename="%s"', $coa_name));
			header('content-transfer-encoding: binary');
			header('content-type: application/pdf');

			readfile($data['Result']['coa_file']);

			exit(0);

			break;

		case 'png':

			$qrCode = new \Endroid\QrCode\QrCode(sprintf('https://%s/share/%s.html', $_SERVER['SERVER_NAME'], $ARG['id']));

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

		// return $this->_container->view->render($RES, 'coa/default.html', $data);
		return $this->_container->view->render($RES, 'page/result/share.html', $data);

	}

	/**
	 * Clean the Data for JSON
	 */
	function _clean_data($data)
	{
		unset($data['Result']['cbd']);
		unset($data['Result']['coa_file']);
		unset($data['Result']['external_id']);
		unset($data['Result']['for_inventory_id']);
		unset($data['Result']['global_user_id']);
		unset($data['Result']['sum']);
		unset($data['Result']['thc']);

		unset($data['Sample']['additives']);
		unset($data['Sample']['cost']);
		unset($data['Sample']['external_id']);
		unset($data['Sample']['global_user_id']);
		unset($data['Sample']['is_initial_inventory']);
		unset($data['Sample']['lab_result_file_path']);
		unset($data['Sample']['legacy_id']);
		unset($data['Sample']['serving_num']);
		unset($data['Sample']['serving_size']);
		unset($data['Sample']['total_marijuana_in_grams']);
		unset($data['Sample']['value']);

		unset($data['Product']['allergens']);
		unset($data['Product']['contains']);
		unset($data['Product']['cost']);
		unset($data['Product']['external_id']);
		unset($data['Product']['global_strain_id']);
		unset($data['Product']['global_user_id']);
		unset($data['Product']['ingredients']);
		unset($data['Product']['serving_num']);
		unset($data['Product']['serving_size']);
		unset($data['Product']['storage_instructions']);
		unset($data['Product']['total_marijuana_in_grams']);
		unset($data['Product']['value']);

		unset($data['Strain']);

		unset($data['Variety']['external_id']);
		unset($data['Variety']['meta']);

		unset($data['OpenTHC']);
		unset($data['Site']);
		unset($data['mine']);
		return $data;
	}

	/**
	 * Manually remap some shit from LeafData
	 */
	function _map_leafdata($data)
	{
		$type_list = [ 'Cannabinoid', 'General', 'Metal', 'Microbe', 'Mycotoxin', 'Pesticide', 'Solvent', 'Terpene' ];
		$data['metric_type_list'] = array_combine($type_list, $type_list);

		$data['MetricList'] = [
			'Cannabinoid' => [],
			'General' => [],
			'Metal' => [],
			'Microbe' => [],
			'Micotoxin' => [],
			'Pesticide' => [],
			'Solvent' => [],
			'Terpene' => [],
		];


		$lrm = $data['Result']['meta'];

		$data['MetricList']['General']['018NY6XC00LM0PXPG4592M8J14'] = [
			'name' => 'Moisture',
			'qom'  => $lrm['moisture_content_percent'],
		];
		$data['MetricList']['General']['018NY6XC00LMHF4266DN94JPPX'] = [
			'name' => 'Water Activity',
			'qom'  => $lrm['moisture_content_water_activity_rate']
		];
		$data['MetricList']['General']['018NY6XC00LMA50497RDC53DB5'] = [
			'name' => 'Seeds',
			'qom'  => $lrm['foreign_matter_seeds']
		];
		$data['MetricList']['General']['018NY6XC00LMQAZZSDXPYH62SS'] = [
			'name' => 'Stems',
			'qom'  => $lrm['foreign_matter_stems']
		];
		$data['MetricList']['General']['018NY6XC00LMHGENRW0DAPFQRZ'] = [
			'name' => 'Other',
			'qom'  => $lrm['foreign_matter']
		];

		$data['MetricList']['Cannabinoid']['018NY6XC00LM49CV7QP9KM9QH9'] = [
			'name' => 'd9-THC',
			'qom'  => $lrm['cannabinoid_d9_thc_percent'],
		];
		$data['MetricList']['Cannabinoid']['018NY6XC00LMB0JPRM2SF8F9F2'] = [
			'name' => 'd9-THCA',
			'qom'  => $lrm['cannabinoid_d9_thca_percent'],
		];
		$data['MetricList']['Cannabinoid']['018NY6XC00LMK7KHD3HPW0Y90N'] = [
			'name' => 'CBD',
			'qom'  => $lrm['cannabinoid_d9_cbd_percent']
		];
		$data['MetricList']['Cannabinoid']['018NY6XC00LMENDHEH2Y32X903'] = [
			'name' => 'CBDA',
			'qom'  => $lrm['cannabinoid_d9_cbda_percent']
		];

		//
		$data['MetricList']['Mycotoxin']['018NY6XC00LM638QCGB50ZKYKJ'] = [
			'name' => 'Bile Tolerant Bacteria',
			'qom'  => $lrm['microbial_bile_tolerant_cfu_g']
		];
		//
		$data['MetricList']['Mycotoxin']['018NY6XC00LM7S8H2RT4K4GYME'] = [
			'name' => 'E.Coli',
			'qom'  => $lrm['microbial_pathogenic_e_coli_cfu_g']
		];
		//
		$data['MetricList']['Mycotoxin']['018NY6XC00LMS96WE6KHKNP52T'] = [
			'name' => 'Salmonella',
			'qom'  => $lrm['microbial_salmonella_cfu_g']
		];
		//
		// $data['MetricList']['Mycotoxin'][''] = [
		// 	'name' => '',
		// 	'qom'  => $lrm['medically_compliant_status']
		// ];


		$data['MetricList']['Mycotoxin']['018NY6XC00LMR9PB7SNBP97DAS'] = [
			'name' => 'Aflatoxins',
			'qom'  => $lrm['mycotoxin_aflatoxins_ppb']
		];
		$data['MetricList']['Mycotoxin']['018NY6XC00LMK15566W1G0ZH5X'] = [
			'name' => 'Ochratoxin',
			'qom'  => $lrm['mycotoxin_ochratoxin_ppb']
		];


		// @todo Here we should evaluate LRM to find junk data
		unset($data['Result']['meta']['for_inventory']);
		// _exit_text($data);

		return $data;
	}
}
