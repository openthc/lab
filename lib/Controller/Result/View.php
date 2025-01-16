<?php
/**
 * View a Lab Result
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Result;

use Edoceo\Radix\Session;

use OpenTHC\Lab\Lab_Metric;
use OpenTHC\Lab\Lab_Result;
use OpenTHC\Lab\Lab_Sample;

class View extends \OpenTHC\Lab\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$id = $ARG['id'];
		if (empty($id)) {
			_exit_html_fail('<h1>Invalid Request [CRV-020]</h1>', 400);
		}

		$dbc = $this->_container->DBC_User;

		$Lab_Result = new Lab_Result($dbc, $id);
		if (empty($Lab_Result['id'])) {
			_exit_html_fail('<h1>Lab Result Not Found [CRV-032]</h1>', 400);
		}

		$Lab_Sample = new Lab_Sample($dbc, $Lab_Result['lab_sample_id']);
		if (empty($Lab_Sample['id'])) {

		}

		$data = $this->load_lab_result_full($id);

		// Data
		$data = $this->loadSiteData($data);

		$data['Page'] = array('title' => 'Result :: View');

		$data['Lab_Result']['id_nice'] = _nice_id($data['Lab_Result']['id'], $data['Lab_Result']['guid']);
		$data['Lab_Sample']['id_nice'] = _nice_id($data['Lab_Sample']['id'], $data['Lab_Sample']['guid']);

		// @note this is not resolving the file correctly
		if (empty($data['Lab_Result']['coa_file'])) {
			$data['Lab_Result']['coa_file'] = $data['Lab_Result']->getCOAFile();
		}
		if ( ! empty($data['Lab_Result']['coa_file'])) {
			if ( ! is_file($data['Lab_Result']['coa_file'])) {
				$data['Lab_Result']['coa_file'] = null;
			}
		}

		// @todo use dbc_auth and create an auth_context_ticket?
		// Use Redis, with Timelimit
		$data['coa_upload_hash'] = _encrypt(json_encode(array(
			'a' => 'coa-upload',
			'r' => $data['Lab_Result']['id'],
			'x' => $_SERVER['REQUEST_TIME'] + (86400 * 4)
		)));

		// https://stackoverflow.com/a/8940515
		$data['share_mail_link'] = http_build_query(array(
			'subject' => sprintf('Lab Result %s', $data['Lab_Result']['guid']),
			'body' => sprintf("\n\nHere is the link: https://%s/pub/%s.html", $_SERVER['SERVER_NAME'], $data['Lab_Result']['id']),
		), null, '&amp;', PHP_QUERY_RFC3986);

		if (!empty($_POST['a'])) {
			return $this->_postHandler($REQ, $RES, $Lab_Result, $data);
		}

		$x = $data['Lab_Result']->getMeta();
		$data['Lab_Result'] = $data['Lab_Result']->toArray();
		$data['Lab_Result']['meta'] = $x;

		if ('dump' == $_GET['_dump']) {
			__exit_text($data);
		}

		return $RES->write( $this->render('result/single.php', $data) );

	}

	/**
	 * Load the Full Lab Result Data
	 */
	function load_lab_result_full($id)
	{
		// Get Result
		$dbc_user = $this->_container->DBC_User;
		$chk = $dbc_user->fetchRow('SELECT * FROM lab_result WHERE (id = :lr0 OR guid = :lr0)', [ ':lr0' => $id ]);
		$Lab_Result = new Lab_Result($dbc_user, $chk);

		if (empty($Lab_Result['id'])) {
			_exit_html_fail('<h1>Lab Result Not Found [CRV-106]</h1>', 404);
		}

		// Load Sample (or Inventory)
		// @note should ALWAYS have a proper Lab_Sample record, if not it needs a data-patch /djb 20220222
		$Lab_Sample = new Lab_Sample();
		if ( ! empty($Lab_Result['lab_sample_id'])) {
			// if ($Lab_Result['lab_sample_id'] == $Lab_Result['inventory_id']) {
			// 	$chk = $dbc_user->fetchRow('SELECT * FROM inventory WHERE id = :ls0', [ ':ls0' => $Lab_Result['inventory_id'] ]);
			// 	$Lab_Sample = new Lab_Sample(null, $chk);
			// } else {
				$Lab_Sample = new Lab_Sample($dbc_user, $Lab_Result['lab_sample_id']);
			// }
		// } elseif (!empty($Lab_Result['inventory_id'])) {
		// 	$chk = $dbc_user->fetchRow('SELECT * FROM inventory WHERE id = :ls0', [ ':ls0' => $Lab_Result['inventory_id'] ]);
		// 	$Lab_Sample = new Lab_Sample(null, $chk);
		}

		$Inventory = $dbc_user->fetchRow('SELECT id, product_id, variety_id FROM inventory WHERE id = :i0', [ ':i0' => $Lab_Sample['inventory_id'] ?: $Lab_Sample['lot_id'] ]);
		$Product = $dbc_user->fetchRow('SELECT * FROM product WHERE id = ?', [ $Inventory['product_id'] ]);
		$ProductType = $dbc_user->fetchRow('SELECT * FROM product_type WHERE id = ?', [ $Product['product_type_id'] ]);
		$Variety = $dbc_user->fetchRow('SELECT * FROM variety WHERE id = ?', [ $Inventory['variety_id'] ]);

		// Get base lab metrics
		$lab_result_metric_list = [];
		$sql = <<<SQL
		SELECT id AS lab_metric_id, lab_metric_type_id, type, sort, name, meta
		FROM lab_metric
		WHERE stat = 200
		ORDER BY sort, name
		SQL;
		$res = $dbc_user->fetchAll($sql);
		foreach ($res as $rec) {
			$rec['@source'] = 'lab_metric';
			$rec['meta'] = json_decode($rec['meta'], true);
			$lab_result_metric_list[ $rec['lab_metric_id'] ] = $rec;
		}

		// Get authoriative lab result metrics
		// Over-Writes the ones Above
		$sql = <<<SQL
		SELECT lab_result_metric.*
			, lab_result_metric.id AS lab_result_metric_id
			, lab_metric.lab_metric_type_id
			, lab_metric.type
			, lab_metric.sort
			, lab_metric.name
			, lab_metric.meta AS lab_metric_meta
		FROM lab_metric
		JOIN lab_result_metric ON lab_metric.id = lab_result_metric.lab_metric_id
		WHERE lab_result_metric.lab_result_id = :lr0
		ORDER BY lab_metric.type, lab_metric.sort, lab_metric.stat, lab_metric.name
		SQL;
		$arg = [
			':lr0' => $Lab_Result['id']
		];
		$res = $dbc_user->fetchAll($sql, $arg);
		foreach ($res as $rec) {
			$rec['@source'] = 'lab_result_metric';
			$rec['meta'] = json_decode($rec['meta'], true);
			$rec['lab_metric_meta'] = json_decode($rec['lab_metric_meta'], true);
			$lab_result_metric_list[ $rec['lab_metric_id'] ] = $rec;
		}

		$Lab_Metric = new Lab_Metric($dbc_user);

		// $data['License'] = $dbc_user->fetchRow('SELECT * FROM license WHERE id = :l0', [ ':l0' => $Lab_Sample['license_id'] ]);
		$Source_License = $dbc_user->fetchRow('SELECT * FROM license WHERE id = :l0', [ ':l0' => $Lab_Sample['license_id_source'] ]);

		return [
			'Lot' => $Inventory,
			'Inventory' => $Inventory,
			'Lab_Sample' => $Lab_Sample,
			'Lab_Result' => $Lab_Result,
			'Lab_Result_Metric_list' => $lab_result_metric_list,
			'Lab_Metric_Type_list' => $Lab_Metric->getTypeList(),
			'Product' => $Product,
			'Product_Type' => $ProductType,
			'Variety' => $Variety,
			'Source_License' => $Source_License
		];

	}

	/**
	 *
	 */
	function _postHandler($REQ, $RES, $LR, $data)
	{
		switch ($_POST['a']) {
			case 'coa-create':
			case 'coa-create-pdf':

				// @note not implemented yet
				$chk = $this->_container->DBC_User->fetchAll('SELECT * FROM lab_layout');
				if (empty($chk)) {
					// _exit_html('<p>You must <a href="/config/coa-layout">upload some COA Layouts</a> to get printable output</p>', 501);
				}

				// Genereate HTML then PDF
				$RES = $this->_viewCOA($REQ, $RES, $data);
				$html = $RES->getBody()->__toString();

				// Save some HTML (somehow?)
				// $coa_htm_file = _tempnam();
				// $subC = new self($this->_container);
				// $subR = $subC->__invoke($REQ, $RES, $ARG);
				// // var_dump($subR);

				// $html = $subR->getBody()->__toString();

				// var_dump($html);

				if ('coa-create-pdf' == $_POST['a']) {

					// _exit_text($html);
					$src_file = '/tmp/print.html';
					file_put_contents($src_file, $html);

					$cmd = [];
					$cmd[] = sprintf('%s/bin/html2pdf.sh', APP_ROOT);
					$cmd[] = escapeshellarg(sprintf('file://%s', $src_file));
					$cmd[] = '/tmp/print.pdf';
					$cmd[] = '2>&1';
					$cmd = implode(' ', $cmd);
					var_dump($cmd);

					$buf = shell_exec($cmd);

					var_dump($buf);

					$out_file = sprintf('%s/webroot/output/COA-%s.pdf', APP_ROOT, $data['Lab_Result']['id']);
					var_dump($out_file);

					rename('/tmp/print.pdf', $out_file);

					$ret = sprintf('/output/COA-%s.pdf', $data['Lab_Result']['id']);
					return $RES->withRedirect($ret, 303);

				}

				_exit_html($html);

			break;

		case 'coa-download':
		case 'download-coa':

			$coa_file = $data['Lab_Result']['coa_file'];


			if (empty($data['Lab_Result']['coa_file'])) {
				_exit_html('<h1>COA File Not Found [CRV-186]</h1>', 404);
			}

			if ( ! is_file($data['Lab_Result']['coa_file'])) {
				__exit_text($data);
				_exit_html('<h1>COA File Not Found [CRV-190]</h1>', 404);
			}

			if (filesize($data['Lab_Result']['coa_file']) < 512) {
				_exit_html('<h1>COA File Not Found [CRV-194]</h1>', 404);
			}

			$data['Lab_Result']['coa_type'] = mime_content_type($data['Lab_Result']['coa_file']);

			// // File Type
			// switch ($coa_type) {
			// 	case 'application/pdf':
			// 		// Proper
			// 	break;
			// 	case 'image/jpeg':
			// 		// OK
			// 	break;
			// 	default:
			// 		_exit_html('<h1>COA File Type Not Supported [CRV#211]</h1>', 404);
			// }

			header(sprintf('content-disposition: inline; filename="COA-%s.pdf"', $data['Lab_Result']['id']));
			header('content-transfer-encoding: binary');
			header(sprintf('content-type: %s', $data['Lab_Result']['coa_type']));

			readfile($data['Lab_Result']['coa_file']);

			exit(0);

			break;

		case 'coa-upload':

			try {
				$LR->setCOAFile($_FILES['file']['tmp_name']);
			} catch (\Exception $e) {
				Session::flash('fail', $e->getMessage());
			}

			// publish / re-publish
			$lab_self = new \OpenTHC\Service\OpenTHC('lab');
			$arg = [ 'json' => [
				'company' => $_SESSION['Company']['id']
				, 'lab_result' => $LR['id']
			]];
			$res = $lab_self->post('/api/v2018/pub', $arg);

			switch ($res['code']) {
				case 200:
				case 201:
					// Success
					Session::flash('info', 'COA Uploaded and Lab Result Published');
					break;
				default:
					var_dump($res);
					Session::flash('warn', sprintf('Unable to (re)-publish to Lab Portal (%s) [CRV-345]', $res['code']));
			}

			return $RES->withRedirect(sprintf('/result/%s', $LR['id']));

			break;

		case 'commit':
		case 'lab-result-commit':

			// Commits to a CRE
			// require_once(__DIR__ . '/Create_LeafData.php');
			// $x = new \OpenTHC\Lab\Controller\Result\Create_LeafData($this->_container);
			// $_POST['result_id'] = $LR['id'];
			// return $x->_commit($REQ, $RES, $ARG);

			$dtA = new \DateTime();
			$dtE = clone $dtA;
			$dtE->add(new \DateInterval('P365D'));

			$LR['approved_at'] = $dtA->format(\DateTimeInterface::RFC3339);
			$LR['expires_at'] = $dtE->format(\DateTimeInterface::RFC3339);
			$LR->setFlag(Lab_Result::FLAG_LOCK);
			$LR->save('Lab Result Committed by User');

			Session::flash('info', 'Lab Result Committed');

			return $RES->withRedirect(sprintf('/result/%s', $LR['id']));

			break;

		case 'mute':
			$LR->setFlag(Lab_Result::FLAG_MUTE);
			$LR->save();
			break;
		case 'lab-result-share':
		case 'share': // v0

			$lab_self = new \OpenTHC\Service\OpenTHC('lab');

			$arg = [ 'json' => [
				'company' => $_SESSION['Company']['id']
				, 'lab_result' => $LR['id']
			]];
			$res = $lab_self->post('/api/v2018/pub', $arg);

			switch ($res['code']) {
				case 200:
				case 201:

					// Success
					$LR->setFlag(Lab_Result::FLAG_PUBLIC);
					$LR->save();

					return $RES->withRedirect($res['data']['pub']);

				default:

					throw new \Exception('Unable to Publish to Lab Portal');
			}

			break;

		case 'void':

			_exit_html_fail('<h1>Not Implemented [CRV-395]</h1>', 501);

			break;
		default:
			var_dump($_POST);
			var_dump($_FILES);
			die("not Handled");
		}
	}

	/**
	 * Create a Printable COA
	 */
	public function _viewCOA($REQ, $RES, $data)
	{
		$data['Company'] = $_SESSION['Company'];
		$data['License'] = $_SESSION['License'];
		if (empty($data['License']['address_full'])) {
			$m = json_decode($data['License']['meta'], true);
			$data['License']['address_full'] = $m['address_full'];
		}

		// Refactor Metric Type List
		$metric_type_list = [];
		foreach ($data['Lab_Metric_Type_list'] as $i => $mt) {
			$metric_type_list[ $mt['stub'] ] = $mt;
		}
		$data['Lab_Metric_Type_list'] = $metric_type_list;

		// Fix Unit Display
		foreach ($data['Lab_Result_Metric_list'] as $mt => $mtd_list) {
			foreach ($mtd_list as $mdi => $mdd) {
				$mdd['qom'] = rtrim($mdd['qom'], '0');
				$mdd['qom'] = rtrim($mdd['qom'], '.');
				$data['Lab_Result_Metric_list'][$mt][$mdi] = $mdd;
			}
		}
		// $data['metric_list'] = $data['Lab_Result_Metric_list']; //

		// $data['Lab_Metric_Type_list'] = array_filter($data['Lab_Metric_Type_list'], _filter_types);
		$data['Lab_Metric_Type_list'] = [
			// 'General',
			'Cannabinoid',
			'Terpene',
			'Pesticide',
			'Solvent',
			'Metals',
			'Microbe',
			'Mycotoxin',
		];

		if ('stub' == $_GET['_']) {
			$stub = file_get_contents(APP_ROOT . '/etc/coa/lab-result-stub.json');
			$data['metric_list'] = json_decode($stub, true);
		}

		// Render from HTML Template?

		// Render from PHP Template?
		ob_start();
		// require_once(APP_ROOT . '/etc/coa/openthc-default.php');
		require_once(APP_ROOT . '/etc/coa/template-b.php');
		// require_once(APP_ROOT . '/view/result/coa.php');
		$html = ob_get_clean();

		// Or Trap HTML, write to file then call /bin/print-coa.php
		// Or directly use right in this code to make a PDF w/o shelling out to PHP (which itself shellout to chromium)

		// render as HTML or as PDF?
		_exit_html($html);
		// _exit_pdf($pdf_file);

		// return $RES->write( $this->render('result/coa.php', $data) );

	}
}
