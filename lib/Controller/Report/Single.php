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

				$data = $this->_load_data($dbc_user, $Lab_Report);

				$lab_report_file_list = $data['lab_report_file_list'];
				unset($data['lab_report_file_list']);

				$Lab_Report_Meta = $Lab_Report->getMeta();
				$Lab_Report_Meta['public_link_list'] = [];

				// Publish Files
				if ( ! empty($lab_report_file_list)) {

					foreach ($lab_report_file_list as $lrf) {

						// Re-maps Path
						$req_path = [];
						$req_path[] = 'lab';
						$req_path[] = $Lab_Report['id'];
						// Custom Shit Here
						if (preg_match('/\w{26}\-CCRS.csv$/', $lrf['name'])) {
							$req_path[] = 'ccrs.csv';
						} elseif (preg_match('/\w{26}\-WCIA.json/', $lrf['name'])) {
							$req_path[] = 'wcia.json';
						} else {
							$req_path[] = $lrf['name'];
						}
						$req_path = implode('/', $req_path);

						$lrf_body = $dbc_user->fetchOne('SELECT body FROM lab_report_file WHERE id = :lrf0', [
							':lrf0' => $lrf['id']
						]);
						$req_body = stream_get_contents($lrf_body);
						$req_type = $lrf['type'];
						// $Lab_Result1->importCOA($req_body);
						$res = _openthc_pub($req_path, $req_body, $req_type);
						var_dump($res);
						if ( ! empty($res['data'])) {
							$url = $res['data'];
							$Lab_Report_Meta['public_link_list'][ $lrf['id'] ] = [
								'link' => $url,
								'name' => $lrf['name'],
							];
						}

					}

					$Lab_Report['meta'] = json_encode($Lab_Report_Meta);
					$Lab_Report->setFlag(Lab_Report::FLAG_PUBLIC);
					$Lab_Report->setFlag(Lab_Report::FLAG_PUBLIC_COA);
				}

				$Lab_Report->save('Lab Report Published by User');

				Session::flash('info', 'Lab Results Published');

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

			$Lab_Report->setFLag(Lab_Report::FLAG_OUTPUT_COA);
		}

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

		$Lab_Report->save('Lab Report Committed by User');

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

		$sql = 'DELETE FROM inventory_lab_report WHERE lab_report_id = :lr0';
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

				$lrf = [];
				$lrf['id'] = _ulid();

				$sql = 'INSERT INTO lab_report_file (id, lab_report_id, name, size, type, body) VALUES (:lrf0, :lr0, :n1, :s1, :t1, :b1)';
				$cmd = $dbc_user->prepare($sql, null);
				$cmd->bindParam(':lrf0', $lrf['id']);
				$cmd->bindParam(':lr0', $Lab_Report['id']);
				$cmd->bindParam(':n1', $coa_data['name']);
				$cmd->bindParam(':s1', $coa_data['size']);
				$cmd->bindParam(':t1', $coa_data['type']);
				$cmd->bindParam(':b1', $coa_data['body'], \PDO::PARAM_LOB);
				$cmd->execute();

				Session::flash('info', 'COA File was attached from QBench');

				return $lrf['id'];;
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
		// if (empty($Lab_Sample['meta']['has_report'])) {
		// 	echo "NO REPORT\n";
		// 	return null;
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
