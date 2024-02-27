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

		switch ($_GET['a']) {
			case 'lab-report-file-download':
				$rec = $dbc_user->fetchRow('SELECT * FROM lab_report_file WHERE id = :lf0 AND lab_report_id = :lr0', [
					':lr0' => $Lab_Report['id'],
					':lf0' => $_GET['id']
				]);
				// var_dump($rec);

				header('content-transfer-encoding: binary');
				header(sprintf('content-type: %s', $rec['type']));
				header(sprintf('content-disposition: inline; filename="%s"', $rec['name']));

				fpassthru($rec['body']);

				exit;
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
			case 'coa-upload':
				// Save to lab_report_file
				$coa_data = [
					'name' => $_FILES['file']['name'],
					'type' => $_FILES['file']['type'],
					'body' => file_get_contents($_FILES['file']['tmp_name']),
					'size' => $_FILES['file']['size'],
				];

				if ($coa_data['size']) {

					$sql = <<<SQL
					INSERT INTO lab_report_file (id, lab_report_id, size, type, body, name)
					VALUES (ulid_create(), :lr0, :s1, :t1, :b1, :n1)
					SQL;

					$cmd = $dbc_user->prepare($sql, null);
					$cmd->bindParam(':lr0', $Lab_Report['id']);
					$cmd->bindParam(':s1', $coa_data['size']);
					$cmd->bindParam(':t1', $coa_data['type']);
					$cmd->bindParam(':b1', $coa_data['body'], \PDO::PARAM_LOB);
					$cmd->bindParam(':n1', $coa_data['name']);
					$cmd->execute();
				}

				break;
			case 'lab-report-commit';
				$RES = $this->_commit($RES, $dbc_user, $Lab_Report);
				break;
			case 'lab-report-delete':
				$RES = $this->_delete($RES, $dbc_user, $Lab_Report);
				return $RES;
				break;
			case 'lab-report-share':

				// Move to PUB somehow?
				$data = $this->_load_data($dbc_user, $Lab_Report);

				$lab_report_file_list = $data['lab_report_file_list'];
				unset($data['lab_report_file_list']);

				// Alias the data into this field
				$data['Lab_Result'] = $data['Lab_Report'];

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

				$Lab_Report->setFlag(Lab_Report::FLAG_PUBLIC);

				// $coa_file = $Lab_Result1->getCOAFile();
				// $coa_file = $Lab_Report->getCOA();
				if ( ! empty($lab_report_file_list)) {
					$lrf_id = $lab_report_file_list[0]['id'];
					$lrf = $dbc_user->fetchOne('SELECT body FROM lab_report_file WHERE id = :lrf0', [
						':lrf0' => $lrf_id
					]);
					$lrf_body = stream_get_contents($lrf);
					$Lab_Result1->importCOA($lrf_body);
					$Lab_Report->setFlag(Lab_Report::FLAG_PUBLIC_COA);
				}

				// if (_is_ajax()) {
				// 	$ret['data'] = [
				// 		'pub' => sprintf('https://%s/pub/%s.html', $_SERVER['SERVER_NAME'], $Lab_Result1['id']),
				// 		'coa' => sprintf('https://%s/pub/%s.pdf', $_SERVER['SERVER_NAME'], $Lab_Result1['id']),
				// 		'json' => sprintf('https://%s/pub/%s.json', $_SERVER['SERVER_NAME'], $Lab_Result1['id']),
				// 		'wcia' => sprintf('https://%s/pub/%s/wcia.json', $_SERVER['SERVER_NAME'], $Lab_Result1['id']),
				// 	];
				// }

				// Publish (eg CRE, Qbench)
				// $this->_publish_external($Lab_Report);

				$Lab_Report->save('Lab Report Published by User');

				return $RES->withRedirect(sprintf('/pub/%s.html', $Lab_Result1['id']));

				break;
		}

		return $RES->withRedirect(sprintf('/report/%s', $Lab_Report['id']));

	}

	/**
	 * Commit this Lab Report
	 */
	function _commit($RES, $dbc_user, $Lab_Report)
	{
		// Log a Commit?
		$dtA = new \DateTime();
		$dtE = clone $dtA;
		$dtE->add(new \DateInterval('P365D'));

		$report_stat = 0;
		$report_data = $Lab_Report->getMeta();
		foreach ($report_data['lab_result_metric_list'] as $lrm) {
			$report_stat = max($report_stat, $lrm['stat']);
		}

		$Lab_Report['approved_at'] = $dtA->format(\DateTimeInterface::RFC3339);
		$Lab_Report['expires_at'] = $dtE->format(\DateTimeInterface::RFC3339);
		$Lab_Report['stat'] = $report_stat;
		$Lab_Report->setFlag(Lab_Report::FLAG_LOCK);
		$Lab_Report->save('Lab Report Committed by User');

		$data = $this->_load_data($dbc_user, $Lab_Report);

		// Alias the data into this field
		$data['Lab_Result'] = $data['Lab_Report'];

		$subC = new \OpenTHC\Lab\Controller\Report\Download($this->_container);

		$got_coa = $this->_import_external_system($dbc_user, $Lab_Report, $data['Lab_Sample']);
		if ($got_coa) {
			// Use External System COA
			$Lab_Report->setFLag(Lab_Report::FLAG_OUTPUT_COA);
		} else {
			// Use Internal System COA
			// Generate the COA/PDF
			$res1 = $RES->withBody(new \Slim\Http\Body(fopen('php://temp', 'r+')));
			$res1 = $subC->pdf($res1, $ARG, $data);
			$out_body = $res1->getBody();
			$out_body->rewind();

			$this->_commit_insert_file($dbc_user
				, $Lab_Report['id']
				, sprintf('%s.pdf', $data['Lab_Report']['id'])
				, $out_body->getSize()
				, 'application/pdf'
				, $out_body->getContents()
			);

			// _openthc_pub(sprintf('/lab/%s/coa.pdf', $Lab_Report['id']), $out_body->getContents(), 'application/pdf');

			$Lab_Report->setFLag(Lab_Report::FLAG_OUTPUT_COA);
		}

		// WCIAdataLINK

		// Invoke on ourselves for the HTML view
		// $res1 = $RES->withBody(new \Slim\Http\Body(fopen('php://temp', 'r+')));
		// $res1 = $this->__invoke($res1, $ARG, $data);
		// $out_body = $res1->getBody();
		// $out_body->rewind();
		// $out_size = $out_body->getSize();
		// $out_data = $out_body->getContents();
		// $Lab_Report->setFLag(Lab_Report::FLAG_OUTPUT_HTML);

		// Generate the CSV/CCRS
		$res1 = $RES->withBody(new \Slim\Http\Body(fopen('php://temp', 'r+')));
		$res1 = $subC->csv_ccrs($res1, $ARG, $data);
		$out_body = $res1->getBody();
		$out_body->rewind();
		$this->_commit_insert_file($dbc_user
			, $Lab_Report['id']
			, sprintf('%s-CCRS.csv', $data['Lab_Report']['id'])
			, $out_body->getSize()
			, 'text/csv'
			, $out_body->getContents()
		);
		$Lab_Report->setFLag(Lab_Report::FLAG_OUTPUT_CSV);

		$out_body->rewind();
		// _openthc_pub(sprintf('/lab/%s/ccrs.csv', $Lab_Report['id']), $out_body->getContents(), 'text/csv');

		// Generate the HTML?
		// $res1 = $subC->html($RES, $ARG, $data);
		// Get Response Body into File
		// $out_body = $res1->getBody();
		// $out_body->rewind();
		// $out_size = $out_body->getSize();
		// $out_data = $out_body->getContents();
		// $Lab_Report->setFLag(Lab_Report::FLAG_OUTPUT_HTML);

		// Generate the JSON/OpenTHC
		// $res1 = $RES->withBody(new \Slim\Http\Body(fopen('php://temp', 'r+')));
		// $res1 = $subC->json_openthc($res1, $ARG, $data);
		// $out_body = $res1->getBody();
		// $out_body->rewind();
		// $out_size = $out_body->getSize();
		// $out_data = $out_body->getContents();
		// $Lab_Report->setFLag(Lab_Report::FLAG_OUTPUT_JSON);

		// Generate the JSON/WCIA
		$res1 = $RES->withBody(new \Slim\Http\Body(fopen('php://temp', 'r+')));
		$res1 = $subC->json_wcia($res1, $ARG, $data);
		$out_body = $res1->getBody();
		$out_body->rewind();
		$this->_commit_insert_file($dbc_user
			, $Lab_Report['id']
			, sprintf('%s-WCIA.json', $data['Lab_Report']['id'])
			, $out_body->getSize()
			, 'application/json'
			, $out_body->getContents()
		);
		$Lab_Report->setFLag(Lab_Report::FLAG_OUTPUT_JSON);

		// API to Self
		// $lab_self = new \OpenTHC\Service\OpenTHC('lab');
		// $arg = [ 'json' => [
		// 	'company' => $_SESSION['Company']['id']
		// 	, 'lab_report' => $Lab_Report['id']
		// ]];
		// $res = $lab_self->post('/api/v2018/report/publish', $arg);

		return $RES;

	}

	/**
	 * SQL Insert Wrapper
	 */
	function _commit_insert_file($dbc_user, $lr0_ulid, $name, $size, $type, $body)
	{
		$sql = 'INSERT INTO lab_report_file (id, lab_report_id, name, size, type, body) VALUES (ulid_create(), :lr0, :n1, :s1, :t1, :b1)';
		$cmd = $dbc_user->prepare($sql, null);
		$cmd->bindParam(':lr0', $lr0_ulid);
		$cmd->bindParam(':n1', $name);
		$cmd->bindParam(':s1', $size);
		$cmd->bindParam(':t1', $type);
		$cmd->bindParam(':b1', $body, \PDO::PARAM_LOB);
		return $cmd->execute();
	}

	/**
	 * Delete this Lab Report
	 */
	function _delete($RES, $dbc_user, $Lab_Report)
	{
		$arg = [
			':lr0' => $Lab_Report['id']
		];

		$sql = 'DELETE FROM lab_report_inventory WHERE lab_report_id = :lr0';
		$dbc_user->query($sql, $arg);

		$sql = 'DELETE FROM lab_report_file WHERE lab_report_id = :lr0';
		$dbc_user->query($sql, $arg);

		$sql = 'DELETE FROM lab_report WHERE id = :lr0';
		$dbc_user->query($sql, $arg);

		Session::flash('info', 'Lab Report Deleted');

		return $RES->withRedirect(sprintf('/sample/%s', $Lab_Report['lab_sample_id']));

	}

	/**
	 * If QBench Source (or other external source)
	 */
	function _import_external_system($dbc_user, $Lab_Report, $Lab_Sample)
	{
		$cfg = $dbc_user->fetchOne("SELECT val FROM base_option WHERE key = 'qbench-auth'");
		if ( ! empty($cfg)) {

			$cfg = json_decode($cfg, true);
			$qbc = new \OpenTHC\CRE\QBench($cfg);
			$res = $qbc->auth();

			$coa_data = $this->_qbench_sample_report_fetch($dbc_user, $qbc, $Lab_Sample);
			if ( ! empty($coa_data)) {

				// Do we want to give it an alternate name somehow?
				_openthc_pub(sprintf('/lab/%s/coa?name=%s.pdf', $Lab_Report['id'], $Lab_Report['name']), $coa_data['body'], 'application/pdf');

				$sql = 'INSERT INTO lab_report_file (id, lab_report_id, name, size, type, body) VALUES (ulid_create(), :lr0, :n1, :s1, :t1, :b1)';
				$cmd = $dbc_user->prepare($sql, null);
				$cmd->bindParam(':lr0', $Lab_Report['id']);
				$cmd->bindParam(':n1', $coa_data['name']);
				$cmd->bindParam(':s1', $coa_data['size']);
				$cmd->bindParam(':t1', $coa_data['type']);
				$cmd->bindParam(':b1', $coa_data['body'], \PDO::PARAM_LOB);
				$cmd->execute();

				Session::flash('info', 'COA File was attached from QBench');

				return true;
			}

		}

		return false;

	}



	/**
	 *
	 */
	function _load_data($dbc_user, $Lab_Report)
	{
		$this->loadSiteData();
		$data['Lab_Report'] = $Lab_Report->toArray();
		$data['Lab_Report']['meta'] = __json_decode($Lab_Report['meta']);

		$res = $dbc_user->fetchAll('SELECT id, flag, stat, name, size, type FROM lab_report_file WHERE lab_report_id = :lr0', [
			':lr0' => $Lab_Report['id']
		]);
		$data['lab_report_file_list'] = $res;

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

		$data['Source_License'] = $dbc_user->fetchRow('SELECT * FROM license WHERE id = :l0', [
			':l0' => $data['Lab_Sample']['license_id_source']
		]);

		// Metric Types
		$res = $dbc_user->fetchAll('SELECT id, name, meta, sort FROM lab_metric_type ORDER BY sort');
		foreach ($res as $rec) {
			$rec['meta'] = json_decode($rec['meta'], true);
			$data['lab_metric_type_list'][ $rec['id'] ] = $rec;
		}

		// Metrics
		$res = $dbc_user->fetchAll("SELECT id, name, meta->>'uom' AS uom, meta, sort FROM lab_metric ORDER BY sort");
		foreach ($res as $rec) {
			$rec['meta'] = json_decode($rec['meta'], true);
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

	/**
	 * Imports the QBench COA Document to our System
	 *
	 * What if the Report doesn't exist?
	 * What if the Report DOES exist and has a COA already?
	 */
	function _qbench_sample_report_fetch($dbc, $qbc, $Lab_Sample) : ?array
	{
		// __exit_text($Lab_Sample);

		// Flag in QBench
		// if (empty($rec['has_report'])) {
		// 	echo "NO REPORT\n";
		// 	return(null);
		// }

		// Get the QBench COA?
		$qb_ls_id = str_replace('qbench:', '', $Lab_Sample['id']);
		$res = $qbc->get(sprintf('/api/v1/report/sample/%s', $qb_ls_id));
		if ( ! empty($res['url'])) {
			$url_info = parse_url($res['url']);
			$res = \Edoceo\Radix\Net\HTTP::get($res['url']);
			if (200 == $res['info']['http_code']) {
				if ('application/pdf' == $res['info']['content_type']) {
					return [
						'body' => $res['body'],
						'name' => basename($url_info['path']),
						'size' => strlen($res['body']),
						'type' => $res['info']['content_type'],
					];
				}
			}
		}

		return null;

	}

}
