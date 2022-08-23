<?php
/**
 * Report Viewer
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Report;

use Edoceo\Radix\Session;

use OpenTHC\Lab\Lab_Sample;
use OpenTHC\Lab\Lab_Report;
use OpenTHC\Lab\Lab_Result;

class Single extends \OpenTHC\Lab\Controller\Base
{
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

		// $data['Page'] = [ 'title' => 'Lab Reports' ];
		$data = $this->_load_data($dbc_user, $Lab_Report);
		$data = $this->loadSiteData($data);
		$data['Page'] = [ 'title' => $Lab_Report['name'] ];

		return $RES->write( $this->render('report/single.php', $data) );
	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		// Get Result
		$dbc_user = $this->_container->DBC_User;
		$Lab_Report = new Lab_Report($dbc_user, $ARG['id']);
		if (empty($Lab_Report['id'])) {
			_exit_html_warn('<h1>Lab Report Not Found [CRS-044]', 404);
		}

		switch ($_POST['a']) {
			case 'lab-report-commit';
				// Log a Commit?
				$Lab_Report['stat'] = 200;
				$Lab_Report->save('Lab Report Committed by User');
				break;
			case 'lab-report-share':

				// Move to PUB somehow?
				$data = $this->_load_data($dbc_user, $Lab_Report);

				// Alias the data into this field
				$data['Lab_Result'] = [
					'id' => $Lab_Report['id'],
					'guid' => $data['Lab_Sample']['name'],
					'name' => $Lab_Report['name'],
					'created_at' => $Lab_Report['created_at'],
				];

				$dir_base = sprintf('%s/var/%s', APP_ROOT, $Lab_Report['id']);
				if ( ! is_dir($dir_base)) {
					mkdir($dir_base, 0755, true);
				}

				$subC = new \OpenTHC\Lab\Controller\Report\Download($this->_container);

				// Invoke on ourselves for the HTML view
				// $res1 = $RES->withBody(new \Slim\Http\Body(fopen('php://temp', 'r+')));
				// $res1 = $this->__invoke($res1, $ARG, $data);
				// $out_file = sprintf('%s/output.pdf', $dir_base);
				// $out_body = $res1->getBody();
				// $out_body->rewind();
				// $out_size = $out_body->getSize();
				// $out_data = $out_body->getContents();
				// file_put_contents($out_file, $out_data);


				// Generate the COA/PDF
				$res1 = $RES->withBody(new \Slim\Http\Body(fopen('php://temp', 'r+')));
				$res1 = $subC->pdf($res1, $ARG, $data);
				$out_file = sprintf('%s/output.pdf', $dir_base);
				$out_body = $res1->getBody();
				$out_body->rewind();
				$out_size = $out_body->getSize();
				$out_data = $out_body->getContents();
				file_put_contents($out_file, $out_data);

				// Generate the CSV/CCRS
				$res1 = $RES->withBody(new \Slim\Http\Body(fopen('php://temp', 'r+')));
				$res1 = $subC->csv_ccrs($res1, $ARG, $data);
				$out_file = sprintf('%s/output-ccrs.csv', $dir_base);
				$out_body = $res1->getBody();
				$out_body->rewind();
				$out_size = $out_body->getSize();
				$out_data = $out_body->getContents();
				file_put_contents($out_file, $out_data);

				// Generate the HTML?
				// $res1 = $subC->csv_ccrs($RES, $ARG, $data);
				// Get Response Body into File
				// $out_file = sprintf('%s/output.html', $dir_base);
				// unlink($out_file);
				// $out_body = $res1->getBody();
				// $out_body->rewind();
				// $out_size = $out_body->getSize();
				// $out_data = $out_body->getContents();
				// file_put_contents($out_file, $out_data);

				// Generate the JSON
				$res1 = $RES->withBody(new \Slim\Http\Body(fopen('php://temp', 'r+')));
				$res1 = $subC->json_wcia($res1, $ARG, $data);
				$out_file = sprintf('%s/output-wcia.json', $dir_base);
				$out_body = $res1->getBody();
				$out_body->rewind();
				$out_size = $out_body->getSize();
				$out_data = $out_body->getContents();
				file_put_contents($out_file, $out_data);

				// API to Self
				// $lab_self = new \OpenTHC\Service\OpenTHC('lab');
				// $arg = [ 'json' => [
				// 	'company' => $_SESSION['Company']['id']
				// 	, 'lab_report' => $Lab_Report['id']
				// ]];
				// $res = $lab_self->post('/api/v2018/report/publish', $arg);

				// Create Lab Report in Main/Public Database
				$data['@context'] = 'http://openthc.org/lab/2021';

				$dbc_main = $this->_container->DBC_Main;
				$Lab_Result1 = new Lab_Result($dbc_main, $Lab_Report['id']);
				$Lab_Result1['id'] = $Lab_Report['id'];
				$Lab_Result1['license_id'] = $Lab_Report['license_id'];
				$Lab_Result1['flag'] = $Lab_Report['flag'];
				$Lab_Result1['stat'] = $Lab_Report['stat'];
				$Lab_Result1['type'] = 'Lab_Report';
				$Lab_Result1['name'] = $Lab_Report['name'];
				$Lab_Result1['meta'] = json_encode($data);
				$Lab_Result1->save();

				// if (_is_ajax()) {
				// 	$ret['data'] = [
				// 		'pub' => sprintf('https://%s/pub/%s.html', $_SERVER['SERVER_NAME'], $Lab_Result1['id']),
				// 		'coa' => sprintf('https://%s/pub/%s.pdf', $_SERVER['SERVER_NAME'], $Lab_Result1['id']),
				// 		'json' => sprintf('https://%s/pub/%s.json', $_SERVER['SERVER_NAME'], $Lab_Result1['id']),
				// 		'wcia' => sprintf('https://%s/pub/%s/wcia.json', $_SERVER['SERVER_NAME'], $Lab_Result1['id']),
				// 	];
				// }

				$Lab_Report->setFlag(Lab_Report::FLAG_PUBLIC);
				$Lab_Report->save('Lab Report Published by User');

				return $RES->withRedirect(sprintf('/pub/%s.html', $Lab_Result1['id']));

				break;
		}

		return $RES->withRedirect(sprintf('/report/%s', $Lab_Report['id']));

	}

	/**
	 *
	 */
	function _load_data($dbc_user, $Lab_Report)
	{
		$this->loadSiteData();
		$data['Lab_Report'] = $Lab_Report->toArray();
		$data['Lab_Report']['meta'] = __json_decode($Lab_Report['meta']);

		$Lab_Sample = new Lab_Sample($dbc_user, $data['Lab_Report']['lab_sample_id']);
		$data['Lab_Sample'] = $Lab_Sample->toArray();
		$data['Lab_Sample']['img_file'] = $Lab_Sample->getImageFile();

		$data['Lot'] = $dbc_user->fetchRow('SELECT id, product_id, variety_id, guid FROM inventory WHERE id = :i0', [ ':i0' => $Lab_Sample['inventory_id'] ?: $Lab_Sample['lot_id'] ]);
		$data['Product'] = $dbc_user->fetchRow('SELECT * FROM product WHERE id = ?', [ $data['Lot']['product_id'] ]);
		$data['Product_Type'] = $dbc_user->fetchRow('SELECT * FROM product_type WHERE id = ?', [ $data['Product']['product_type_id'] ]);
		$data['Variety'] = $dbc_user->fetchRow('SELECT * FROM variety WHERE id = ?', [ $data['Lot']['variety_id'] ]);

		$data['License_Laboratory'] = $dbc_user->fetchRow('SELECT * FROM license WHERE id = :l0', [
			':l0' => $data['Lab_Report']['license_id']
		]);

		$data['License_Source'] = $dbc_user->fetchRow('SELECT * FROM license WHERE id = :l0', [
			':l0' => $data['Lab_Sample']['license_id_source']
		]);

		// Metric Types
		$res = $dbc_user->fetchAll('SELECT id, name, sort FROM lab_metric_type ORDER BY sort');
		foreach ($res as $rec) {
			$data['lab_metric_type_list'][ $rec['id'] ] = $rec;
		}

		// Metrics
		$res = $dbc_user->fetchAll("SELECT id, name, meta->>'uom' AS uom, sort FROM lab_metric ORDER BY sort");
		foreach ($res as $rec) {
			$data['lab_metric_list'][ $rec['id'] ] = $rec;
		}

		// Upscale Data for PDF, JSON, etc
		$data['Lab_Result_Metric_list'] = [];
		foreach ($data['Lab_Report']['meta']['lab_result_metric_list'] as $x) {

			$lm = $dbc_user->fetchRow('SELECT * FROM lab_metric WHERE id = :lm0', [ ':lm0' => $x['lab_metric_id'] ]);
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

		// Another Name for the THing
		// $data['Lab_Result_Section_Metric_list'] = $Lab_Result->getMetrics_Grouped();

		// Another Name for the Stuff
		// $data['Lab_Result_Metric_Type_list'] = $Lab_Result->getMetricListGrouped();

		return $data;
	}

}
