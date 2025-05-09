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
			_exit_html_warn('<h1>Lab Report Not Found [CRS-030]</h1>', 404);
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
			_exit_html_warn('<h1>Lab Report Not Found [CRS-044]</h1>', 404);
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

				$pub_service = new \OpenTHC\Lab\Facade\Pub();

				$data = $this->_load_data($dbc_user, $Lab_Report);

				$lab_report_file_list = $data['lab_report_file_list'];
				unset($data['lab_report_file_list']);

				$Lab_Report_Meta = $Lab_Report->getMeta();
				$Lab_Report_Meta['public_link_list'] = [];

				// Publish Files
				if ( ! empty($lab_report_file_list)) {

					// Find the COA
					// Find the WCIA
					$pub_link_list = [];
					foreach ($lab_report_file_list as $key => $lrf) {

						$req_path = [];
						$req_path[] = 'lab';
						$req_path[] = $Lab_Report['id'];

						// Custom Shit Here
						if (preg_match('/\w{26}\-CCRS.csv$/', $lrf['name'])) {
							$req_path[] = 'ccrs.csv';
							$pub_link_list['ccrs'] = implode('/', $req_path);
						} elseif (preg_match('/\w{26}\-WCIA.json/', $lrf['name'])) {
							$req_path[] = 'wcia.json';
							$pub_link_list['wcia'] = implode('/', $req_path);
						} elseif (('application/pdf' == $lrf['type'])
								&& (
										(preg_match('/^\w{26}\.pdf$/', $lrf['name'])
										|| preg_match('/\d+\.pdf$/', $lrf['name']))
									)
								) {
							$req_path[] = 'coa.pdf';
							$pub_link_list['coa'] = implode('/', $req_path);
						} else {
							$req_path[] = $lrf['name'];
						}

						$lab_report_file_list[$key]['pub_path'] = implode('/', $req_path);

					}

					foreach ($lab_report_file_list as $lrf) {

						$req_path = $lrf['pub_path'];

						$lrf_body = $dbc_user->fetchOne('SELECT body FROM lab_report_file WHERE id = :lrf0', [
							':lrf0' => $lrf['id']
						]);
						$req_body = stream_get_contents($lrf_body);
						$req_type = $lrf['type'];

						// Rewrite the Origin and COA Links
						if ('application/json' == $req_type) {
							$req_body = json_decode($req_body, true);
							if ( ( ! empty($req_body['document_name'])) && ('WCIA Lab Result Schema' == $req_body['document_name']) ) {
								if ( ! empty($pub_link_list['wcia'])) {
									$req_body['document_origin'] = $pub_service->getURL($pub_link_list['wcia']);
								}
								if ( ! empty($pub_link_list['coa'])) {
									$req_body['coa'] = $pub_service->getURL($pub_link_list['coa']);
								}
							}
							$req_body = json_encode($req_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
						}

						$res = $pub_service->put($req_path, $req_body, $req_type);
						switch ($res['code']) {
						case 200:
						case 201:
							if ( ! empty($res['data'])) {
								$url = $res['data'];
								$Lab_Report_Meta['public_link_list'][ $lrf['id'] ] = [
									'link' => $url,
									'name' => $lrf['name'],
								];
							}
							break;
						default:
							Session::flash('warn', sprintf('[CRS-168] Failed to Publish "%s"; Response %d', $lrf['name'], $res['code']));
							break;
						}

					}

					$Lab_Report['meta'] = json_encode($Lab_Report_Meta);
					$Lab_Report->setFlag(Lab_Report::FLAG_PUBLIC);
					$Lab_Report->setFlag(Lab_Report::FLAG_PUBLIC_COA);
				}

				$Lab_Report->save('Lab Report Published by User');

				// Publish (eg CRE, Qbench)
				// @todo Should be Webhook
				// $who_service = new \OpenTHC\Lab\Facade\Webhook();
				// $who_service->post('/lab/report/published', $data);

				$cmd = [];
				$cmd[] = sprintf('%s/bin/qbench-export.php', APP_ROOT);
				$cmd[] = sprintf('--company=%s', $_SESSION['Company']['id']);
				$cmd[] = sprintf('--license=%s', $_SESSION['License']['id']);
				$cmd[] = '--object=lab-report';
				$cmd[] = sprintf('--object-id=%s', escapeshellarg($Lab_Report['id']));
				$cmd[] = '>/dev/null';
				$cmd[] = '2>&1';
				$cmd[] = '&';
				$cmd = implode(' ', $cmd);
				$buf = shell_exec($cmd);

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
			$Lab_Report->setFlag(Lab_Report::FLAG_OUTPUT_COA);
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

			$Lab_Report->setFlag(Lab_Report::FLAG_OUTPUT_COA);
		}

		// Invoke on ourselves for the HTML view
		// $res1 = $RES->withBody(new \Slim\Http\Body(fopen('php://temp', 'r+')));
		// $res1 = $this->__invoke($res1, $ARG, $data);
		// $out_body = $res1->getBody();
		// $out_body->rewind();
		// $out_size = $out_body->getSize();
		// $out_data = $out_body->getContents();
		// $Lab_Report->setFlag(Lab_Report::FLAG_OUTPUT_HTML);

		// Generate the HTML?
		// $res1 = $subC->html($RES, $ARG, $data);
		// Get Response Body into File
		// $out_body = $res1->getBody();
		// $out_body->rewind();
		// $out_size = $out_body->getSize();
		// $out_data = $out_body->getContents();
		// $Lab_Report->setFLag(Lab_Report::FLAG_OUTPUT_HTML);

		// Generate the CSV/CCRS
		$out_name = sprintf('%s-CCRS.csv', $data['Lab_Report']['id']);
		$res1 = $RES->withBody(new \Slim\Http\Body(fopen('php://temp', 'r+')));
		$res1 = $subC->csv_ccrs($res1, $ARG, $data);
		$out_body = $res1->getBody();
		$out_body->rewind();
		$tmp_name = $res1->getHeaderLine('content-disposition');
		if (preg_match('/filename="(.+?)"/', $tmp_name, $m)) {
			$out_name = $m[1];
		}
		$this->_commit_insert_file($dbc_user
			, $Lab_Report['id']
			, $out_name
			, $out_body->getSize()
			, 'text/csv'
			, $out_body->getContents()
		);
		$Lab_Report->setFLag(Lab_Report::FLAG_OUTPUT_CSV);

		// Generate the JSON/OpenTHC
		// $res1 = $RES->withBody(new \Slim\Http\Body(fopen('php://temp', 'r+')));
		// $res1 = $subC->json_openthc($res1, $ARG, $data);
		// $out_body = $res1->getBody();
		// $out_body->rewind();
		// $this->_commit_insert_file($dbc_user
		// 	, $Lab_Report['id']
		// 	, sprintf('%s.json', $data['Lab_Report']['id'])
		// 	, $out_body->getSize()
		// 	, 'application/json'
		// 	, $out_body->getContents()
		// );
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

		// Change All Others NOT This One Just Made to STAT 410
		$arg = [
			':ls0' => $Lab_Report['lab_sample_id'],
			':lr0' => $Lab_Report['id'],
			':f1' => 0x08000000,
			':s1' => 410,
		];
		// $dbc->query('UPDATE lab_report SET flag = (flag | :f1), stat = :s1 WHERE lab_sample_id = :ls0 AND id != :lr0', $arg);

		return $RES;

	}

	/**
	 * SQL Insert Wrapper
	 */
	function _commit_insert_file($dbc_user, $lr0_ulid, $name, $size, $type, $body)
	{
		$lrf0_ulid = _ulid();

		$sql = 'INSERT INTO lab_report_file (id, lab_report_id, name, size, type, body) VALUES (:lrf0, :lr0, :n1, :s1, :t1, :b1)';
		$cmd = $dbc_user->prepare($sql, null);
		$cmd->bindParam(':lrf0', $lrf0_ulid);
		$cmd->bindParam(':lr0', $lr0_ulid);
		$cmd->bindParam(':n1', $name);
		$cmd->bindParam(':s1', $size);
		$cmd->bindParam(':t1', $type);
		$cmd->bindParam(':b1', $body, \PDO::PARAM_LOB);
		$res = $cmd->execute();

		return $lrf0_ulid;
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

				// _commit_insert_file
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

				return $lrf['id'];
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

		$sql = <<<SQL
		SELECT id, flag, stat, name, size, type
		FROM lab_report_file
		WHERE lab_report_id = :lr0
		ORDER BY id
		SQL;
		$res = $dbc_user->fetchAll($sql, [
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
		$data['Laboratory'] = [];
		$data['Laboratory']['License'] = $dbc_user->fetchRow('SELECT * FROM license WHERE id = :l0', [
			':l0' => $data['Lab_Report']['license_id']
		]);
		$data['Laboratory']['Company'] = $_SESSION['Company'];
		// $dbc_user->fetchRow('SELECT * FROM license WHERE id = :l0', [
		// 	':l0' => $data['Lab_Report']['license_id']
		// ]);

		$data['Source_License'] = $dbc_user->fetchRow('SELECT * FROM license WHERE id = :l0', [
			':l0' => $data['Lab_Sample']['license_id_source']
		]);

		// Metric Types
		$sql = <<<SQL
		SELECT id, name, meta, sort
		FROM lab_metric_type
		WHERE id NOT IN ('018NY6XC00LMT0000000000000')
		ORDER BY sort, name
		SQL;
		$res = $dbc_user->fetchAll($sql);
		foreach ($res as $rec) {
			$rec['meta'] = json_decode($rec['meta'], true);
			$data['lab_metric_type_list'][ $rec['id'] ] = $rec;
		}

		// Metrics
		$sql = <<<SQL
		SELECT id, lab_metric_type_id, name, type, sort, meta
		FROM lab_metric
		ORDER BY sort, name
		SQL;
		$res = $dbc_user->fetchAll($sql);
		foreach ($res as $rec) {
			$rec['meta'] = json_decode($rec['meta'], true);
			$data['lab_metric_list'][ $rec['id'] ] = $rec;
		}

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
