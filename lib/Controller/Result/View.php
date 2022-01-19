<?php
/**
 * View a Lab Result
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace App\Controller\Result;

use Edoceo\Radix\Session;

use App\Lab_Metric;
use App\Lab_Result;
use App\Lab_Sample;

class View extends \App\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$id = $ARG['id'];
		if (empty($id)) {
			_exit_text('Invalid Request [CRV-020]', 400);
		}

		$data = $this->load_lab_result_full($id);

		// Data
		$data = $this->loadSiteData($data);
		$data['Page'] = array('title' => 'Result :: View');
		$data['Sample'] = $data['Lab_Sample']->toArray();
		$data['Sample']['id_nice'] = _nice_id($data['Sample']['id'], $data['Sample']['guid']);
		$data['Result'] = $data['Lab_Result']->toArray();
		$data['Result']['id_nice'] = _nice_id($data['Result']['id'], $data['Result']['guid']);
		// $data['Product'] = $Product;
		// $data['Product_Type'] = $ProductType;
		// $data['Variety'] = $Variety;
		// $data['Sample'] = $LS->toArray();
		// $data['Sample']  = $meta['Sample'];
		// $data['Result']  = $meta['Result'];
		$data['Result']['coa_file'] = $data['Lab_Result']->getCOAFile();
		if (!is_file($data['Result']['coa_file'])) {
			$data['Result']['coa_file'] = null;
		}

		// if (!empty($LR['license_id_lab'])) {
		// 	$x = \OpenTHC\License::findByGUID($LR['license_id_lab']);
		// 	if ($x) {
		// 		$data['Laboratory'] = $x->toArray();
		// 	}
		// }

		// // @todo whats the difference?
		// if (!empty($LR['license_id']))
		// {
		// 	$x = new \OpenTHC\License($dbc, $LR['license_id']);
		// 	if (!empty($x)) {
		// 		$data['License'] = $x->toArray();
		// 		$data['License']['phone'] = _phone_nice($data['License']['phone']);
		// 	}
		// }

		// if (!empty($Lab_Result['license_id_lab'])) {
		// 	$x = \OpenTHC\License::findByGUID($Lab_Result['license_id_lab']);
		// 	if ($x) {
		// 		$data['Laboratory'] = $x->toArray();
		// 	}
		// }

		// @todo use dbc_auth and create an auth_context_ticket
		$data['coa_upload_hash'] = _encrypt(json_encode(array(
			'a' => 'coa-upload',
			'r' => $data['Lab_Result']['id'],
			'x' => $_SERVER['REQUEST_TIME'] + (86400 * 4)
		)));

		// https://stackoverflow.com/a/8940515
		$data['share_mail_link'] = http_build_query(array(
			'subject' => sprintf('Lab Results %s', $data['Result']['global_id']),
			'body' => sprintf("\n\nHere is the link: https://%s/pub/%s.html", $_SERVER['SERVER_NAME'], $data['Lab_Result']['id']),
		), null, '&amp;', PHP_QUERY_RFC3986);

		// __exit_text($data);

		if (!empty($_POST['a'])) {
			return $this->_postHandler($REQ, $RES, $data['Lab_Result'], $data);
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
			_exit_html(sprintf('Lab Result Not Found, please <a href="/result/%s/sync">sync this result</a>', $id), 404);
		}

		// Load Sample (or Lot)
		$Lab_Sample = new Lab_Sample();
		if ( ! empty($Lab_Result['lab_sample_id'])) {
			if ($Lab_Result['lab_sample_id'] == $Lab_Result['inventory_id']) {
				$chk = $dbc_user->fetchRow('SELECT * FROM inventory WHERE id = :ls0', [ ':ls0' => $Lab_Result['inventory_id'] ]);
				$Lab_Sample = new Lab_Sample(null, $chk);
			} else {
				$Lab_Sample = new Lab_Sample($dbc_user, $Lab_Result['lab_sample_id']);
			}
		} elseif (!empty($Lab_Result['inventory_id'])) {
			$chk = $dbc_user->fetchRow('SELECT * FROM inventory WHERE id = :ls0', [ ':ls0' => $Lab_Result['inventory_id'] ]);
			$Lab_Sample = new Lab_Sample(null, $chk);
		}

		$Product = $dbc_user->fetchRow('SELECT * FROM product WHERE id = ?', [ $Lab_Sample['product_id'] ]);
		$ProductType = $dbc_user->fetchRow('SELECT * FROM product_type WHERE id = ?', [ $Product['product_type_id'] ]);
		$Variety = $dbc_user->fetchRow('SELECT * FROM variety WHERE id = ?', [ $Lab_Sample['variety_id'] ]);

		// $meta = json_decode($Lab_Result['meta'], true);
		// $Lab_Result->getMetrics();

		// Get authoriative lab metrics
		$lab_metric_type_list = [];
		$lab_result_metric_list = [];

		$sql = <<<SQL
SELECT lab_result_metric.*
	, lab_metric.type
	, lab_metric.sort
	, lab_metric.name
	, lab_metric.meta
FROM lab_result_metric
JOIN lab_metric ON lab_result_metric.lab_metric_id = lab_metric.id
WHERE lab_result_metric.lab_result_id = :lr0
ORDER BY lab_metric.type, lab_metric.sort, lab_metric.stat, lab_metric.name
SQL;
		$arg = [
			':lr0' => $Lab_Result['id']
		];
		$res = $dbc_user->fetchAll($sql, $arg);
		foreach ($res as $rec) {
			$lab_result_metric_list[$rec['type']][$rec['lab_metric_id']] = $rec;
		}

		$Lab_Metric = new Lab_Metric($dbc_user);

		$data['License'] = $dbc_user->fetchRow('SELECT * FROM license WHERE id = :l0', [ ':l0' => $Lab_Sample['license_id'] ]);

		$dbc_main = $this->_container->DBC_Main;

		$data['License_Source'] = $dbc_main->fetchRow('SELECT * FROM license WHERE id = :l0', [ ':l0' => $Lab_Sample['license_id_source'] ]);

		return [
			'Lot' => [],
			'Lab_Sample' => $Lab_Sample,
			'Lab_Result' => $Lab_Result,
			'Lab_Result_Metric_list' => $lab_result_metric_list,
			'Lab_Metric_Type_list' => $Lab_Metric->getTypes(),
			'Product' => $Product,
			'Product_Type' => $ProductType,
			'Variety' => $Variety
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

				// exit;
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

					$out_file = sprintf('%s/webroot/output/COA-%s.pdf', APP_ROOT, $data['Result']['id']);
					var_dump($out_file);

					rename('/tmp/print.pdf', $out_file);

					$ret = sprintf('/output/COA-%s.pdf', $data['Result']['id']);
					return $RES->withRedirect($ret, 303);

				}

				_exit_html($html);

			break;

		case 'coa-download':
		case 'download-coa':

			if (empty($data['Result']['coa_file'])) {
				_exit_html('<h1>COA File Not Found [CRV#186]</h1>', 404);
			}

			if (!is_file($data['Result']['coa_file'])) {
				_exit_html('<h1>COA File Not Found [CRV#190]</h1>', 404);
			}

			if (filesize($data['Result']['coa_file']) < 512) {
				_exit_html('<h1>COA File Not Found [CRV#194]</h1>', 404);
			}

			$data['Result']['coa_type'] = mime_content_type($data['Result']['coa_file']);

			// // File Type
			// switch ($data['Result']['coa_type']) {
			// 	case 'application/pdf':
			// 		// Proper
			// 	break;
			// 	case 'image/jpeg':
			// 		// OK
			// 	break;
			// 	default:
			// 		_exit_html('<h1>COA File Type Not Supported [CRV#211]</h1>', 404);
			// }

			header(sprintf('content-disposition: inline; filename="COA-%s.pdf"', $data['Result']['id']));
			header('content-transfer-encoding: binary');
			header(sprintf('content-type: %s', $data['Result']['coa_type']));

			readfile($data['Result']['coa_file']);

			exit(0);

			break;

		case 'coa-upload':

			$src_name = strtolower($_FILES['file']['name']);
			$pat_want = strtolower(preg_match('/\.(\w+)/', $LR['id'], $m) ? $m[1] : $LR['id']);

			$chk_name = strpos($src_name, $pat_want);

			if (false === $chk_name) {
				Session::flash('warn', 'Naming the file the same as the Lab Result is a good idea');
			}

			try {
				$LR->setCOAFile($_FILES['file']['tmp_name']);
			} catch (\Exception $e) {
				Session::flash('fail', $e->getMessage());
			}

			$LR->setCOAFile($_FILES['file']['tmp_name']);

			return $RES->withRedirect(sprintf('/result/%s', $LR['id']));

			break;

		case 'commit':

			// Commits to a CRE
			// require_once(__DIR__ . '/Create_LeafData.php');
			// $x = new \App\Controller\Result\Create_LeafData($this->_container);
			// $_POST['result_id'] = $LR['id'];
			// return $x->_commit($REQ, $RES, $ARG);

			break;

		case 'mute':
			$LR->setFlag(\App\Lab_Result::FLAG_MUTE);
			$LR->save();
			break;
		case 'share':

			// @todo Make Sure it's Published in MAIN

			// $dbc_main = $this->_container->DBC_Main;
			// $Lab_Result1 = new Lab_Result($dbc_main, $id);

			// Load all the Lab Result Fully
			// $data -=

			// $lab = new \OpenTHC\Service\OpenTHC('lab');
			// $arg = [ 'form_params' => [
			// 	'id' => $LR['id'],
			// 	'license_id' => $_SESSION['License']['id'],
			// 	'type' => $LR['type'],
			// 	'name' => $LR['name'],
			// 	'meta' => $data,
			// ]];
			// $res = [];
			// $chk = $lab->get('/api/v2015/result/' . $LR['id']);
			// switch ($chk['code']) {
			// 	case 200:
			// 		// UPDATE
			// 		$res = $lab->post('/api/v2015/result/' . $LR['id'], $arg);
			// 	break;
			// 	case 404:
			// 		// INSERT
			// 		$res = $lab->post('/api/v2015/result', $arg);
			// 	break;
			// 	default:
			// 		_exit_text('Lab API Failure', 500);
			// }
			// if (200 != $res['code']) {
			// 	_exit_text(print_r($lab, true));
			// 	throw new \Exception('Unexpected Response from Lab Portal');
			// }

			// Build All Necessary Datas
			// INSERT/UPDATE to openthc_main.lab_result with a HUGE meta

			$data['Sample']['meta'] = json_decode($data['Sample']['meta'], true);
			$data['Result']['meta'] = json_decode($data['Result']['meta'], true);
			$data['Product']['meta'] = json_decode($data['Product']['meta'], true);
			$data['Product_Type']['meta'] = json_decode($data['Product_Type']['meta'], true);
			$data['Variety']['meta'] = json_decode($data['Variety']['meta'], true);
			$data['License']['meta'] = json_decode($data['License']['meta'], true);

			unset($data['OpenTHC']);
			unset($data['Site']);
			unset($data['Page']);
			unset($data['menu']);
			unset($data['coa_upload_hash']);
			unset($data['share_mail_link']);
			unset($data['Lab_Sample']);
			unset($data['Lab_Result']);

			// Get Result
			$dbc_main = $this->_container->DBC_Main;
			$Lab_Result1 = new Lab_Result($dbc_main, $LR['id']);
			$Lab_Result1['id'] = $LR['id'];
			$Lab_Result1['license_id'] = $LR['license_id'];
			$Lab_Result1['flag'] = $LR['flag'];
			$Lab_Result1['stat'] = $LR['stat'];
			$Lab_Result1['type'] = '-system-';
			$Lab_Result1['name'] = 'Lab Result';
			$Lab_Result1['meta'] = json_encode($data);
			$Lab_Result1->save();

			// return $RES->withRedirect(sprintf('/pub/%s.html', $LR['guid']));
			return $RES->withRedirect(sprintf('/pub/%s.html', $LR['id']));

			break;
		case 'sync':
			_exit_html('Not Implemented', 501);
			$S = new Sync($this->_container);
			return $S->__invoke(null, $RES, array('id' => $data['Result']['id']));
			break;
		case 'void':
			_exit_html('Not Implemented', 501);
			// $cre = new \OpenTHC\CRE($_SESSION['pipe-token']);
			// $res = $cre->qa()->delete($data['Result']['id']);
			// var_dump($res);
			// exit;
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
		// _exit_text($data);

		// Filter out one for the auto-display
		// Fold so the NAME is the Key
		// $data['Lab_Metric_Type_list'] = array_filter($data['Lab_Metric_Type_list'], function($v, $k) {
		// 	return ('General' != $v['name']);
		// }, ARRAY_FILTER_USE_BOTH);

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

		// Render from Twig Template?
		// $html = _twig(path/to/user/provided/twig/template.twig.html);

		// Or Trap HTML, write to file then call /bin/print-coa.php
		// Or directly use right in this code to make a PDF w/o shelling out to PHP (which itself shellout to chromium)

		// render as HTML or as PDF?
		_exit_html($html);
		// _exit_pdf($pdf_file);

		// return $RES->write( $this->render('result/coa.php', $data) );

	}
}
