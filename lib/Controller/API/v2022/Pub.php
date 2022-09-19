<?php
/**
 * Publish a Lab Result
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\API\v2022;

use OpenTHC\Lab\Lab_Metric;
use OpenTHC\Lab\Lab_Result;
use OpenTHC\Lab\Lab_Report;

class Pub extends \OpenTHC\Lab\Controller\Base
{
	/**
	 * On POST or PUT we receive an inflated json Lab Result in the OpenTHC Style
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		session_write_close();

		$ret = [];

		$data = $this->parseJSON();

		// Basic Inputs
		if (empty($data['company']['id'])) {
			__exit_json([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Input [CAP-030]' ]
			]);
		}
		if (empty($data['lab_result']['id'])) {
			__exit_json([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Input [CAP-037]' ]
			]);
		}

		$lr0_meta = [];
		$lr0_meta['@context'] = 'http://openthc.org/lab/2022';
		$lr0_meta['@version'] = '2022.000';
		$lr0_meta['Lab_Result'] = $data['lab_result']; // $Lab_Result0->toArray(); // $dbc_user->fetchRow('SELECT * FROM lab_result WHERE id = :lr0', [ ':lr0' => $Lab_Result0['id'] ]);
		// $lr0_meta['Lab_Result']['meta'] = json_decode($lr0_meta['Lab_Result']['meta'], true);

		$dbc = _dbc();

		$lr0 = new Lab_Result($dbc, $data['lab_result']['id']);

		// Shit Hack
		// if ( ! empty($data['company']) && ! empty($data['lab_result'])) {

		// 	$dbc_auth = $this->_container->DBC_Auth;

		// 	$Company = $dbc_auth->fetchRow('SELECT id, name, dsn FROM auth_company WHERE id = :c0', [
		// 		':c0' => $data['company']
		// 	]);
		// 	$_SESSION['dsn'] = $Company['dsn'];

		// 	$dbc_user = new \Edoceo\Radix\DB\SQL($Company['dsn']);

		// 	$Lab_Result0 = new Lab_Result($dbc_user, $data['lab_result']);
		// 	if (empty($Lab_Result0['id'])) {
		// 		__exit_json([
		// 			'data' => null,
		// 			'meta' => [ 'detail' => 'Invalid Source Lab Result' ]
		// 		], 400);
		// 	}

		// 	return $this->_lab_result_publish($RES, $dbc_user, $Lab_Result0);
		// }

		// Proper Inflated OpenTHC Style JSON Data for Publishing?

		// __exit_json([
		// 	'data' => $data,
		// 	'meta' => [],
		// ], 404);

		$sql = <<<SQL
		INSERT INTO lab_result_file (id, lab_report_id, size, type, body)
		VALUES (ulid_create(), :lr0, :s1, :t1, :b1)
		SQL;

		$coa_file = [];
		$coa_file['body'] = __base64_decode_url($data['lab_result_file']['body']);
		$coa_file['name'] = $data['lab_result_file']['name'];
		$coa_file['size'] = strlen($data['lab_result_file']['body']);
		$coa_file['type'] = $data['lab_result_file']['type'];

		$cmd = $dbc->prepare($sql, null);
		$cmd->bindParam(':lr0', $lr0['id']);
		$cmd->bindParam(':s1', $coa_data['size']);
		$cmd->bindParam(':t1', $coa_data['type']);
		$cmd->bindParam(':b1', $coa_data['body'], \PDO::PARAM_LOB);
		$cmd->execute();

	}

	/**
	 * Take the Internal Lab Result and Publish Externally
	 */
	function _lab_result_publish($RES, $dbc_user, $Lab_Result0)
	{
		$lr1_meta = [];
		$lr1_meta['@context'] = 'http://openthc.org/lab/2022';
		$lr1_meta['@version'] = '2022.000';
		$lr1_meta['Lab_Result'] = $Lab_Result0->toArray(); // $dbc_user->fetchRow('SELECT * FROM lab_result WHERE id = :lr0', [ ':lr0' => $Lab_Result0['id'] ]);
		$lr1_meta['Lab_Result']['meta'] = json_decode($lr1_meta['Lab_Result']['meta'], true);

		// Verify COA Data
		$coa_file = $Lab_Result0->getCOAFile();
		// if ( ! is_file($coa_file)) {
		// 	$x = $lr1_meta['Lab_Result']['meta']['coa'];
		// 	if (empty($x)) {
		// 		$x = $lr1_meta['Lab_Result']['meta']['pdf_file'];
		// 	}
		// 	if ( ! empty($x)) {
		// 		$Lab_Result0->importCOA($x);
		// 	}
		// }
		// if (is_file($coa_file)) {
		// 	$lr1_meta['Lab_Result']['coa_file'] = $coa_file;
		// } else {
		// 	// Load lab_result_file ?
		// 	$chk = $dbc_user->fetchRow('SELECT id, name, type FROM lab_result_file WHERE lab_result_id = :lr0 AND type = :t0', [
		// 		':lr0' => $Lab_Result0['id'],
		// 		':t0' => 'application/pdf'
		// 	]);
		// 	if ( ! empty($chk)) {
		// 		$lr1_meta['Lab_Result']['coa_file'] = sprintf('database:%s', $chk['id']);
		// 	}
		// 	// $Lab_Result0->importCOA($x);
		// }
		if ( ! empty($lr1_meta['']))

		$lr1_meta['Lab_Sample'] = $dbc_user->fetchRow('SELECT * FROM lab_sample WHERE id = :ls0', [ ':ls0' => $Lab_Result0['lab_sample_id'] ]);
		$lr1_meta['Lab_Sample']['meta'] = json_decode($lr1_meta['Lab_Sample']['meta'], true);

		// Load Lot Data
		$Lot = $dbc_user->fetchRow('SELECT * FROM inventory WHERE id = :i0', [ ':i0' => $lr1_meta['Lab_Sample']['lot_id'] ]);
		// v1 lab_result_inventory
		if (empty($Lot['id'])) {
			$sql = <<<SQL
			SELECT *
			FROM inventory
			WHERE id IN (SELECT inventory_id FROM lab_result_inventory WHERE lab_result_id = :lr0 ORDER BY id LIMIT 1)
			SQL;
			$Lot = $dbc_user->fetchRow($sql, [ ':lr0' => $Lab_Result0['id'] ]);
		}
		// v0 inventory_lab_result
		if (empty($Lot['id'])) {
			$sql = <<<SQL
			SELECT *
			FROM inventory
			WHERE id IN (SELECT lot_id FROM inventory_lab_result WHERE lab_result_id = :lr0 ORDER BY id LIMIT 1)
			SQL;
			$Lot = $dbc_user->fetchRow($sql, [ ':lr0' => $Lab_Result0['id'] ]);
		}
		$lr1_meta['Lot'] = [
			'id' => $Lot['id'],
			'guid' => $Lot['guid']
		];

		// @hack the Lab_Sample object should never have a null name
		if (empty($lr1_meta['Lab_Sample']['name'])) {
			$lr1_meta['Lab_Sample']['name'] = $Lot['guid'];
		}

		$lr1_meta['Product'] = $dbc_user->fetchRow('SELECT * FROM product WHERE id = :p0', [ ':p0' => $Lot['product_id'] ]);
		$lr1_meta['Product_Type'] = $dbc_user->fetchRow('SELECT * FROM product_type WHERE id = :pt0', [ ':pt0' => $lr1_meta['Product']['product_type_id'] ]);
		$Variety = $dbc_user->fetchRow('SELECT * FROM variety WHERE id = :v0', [ ':v0' => $Lot['variety_id'] ]);
		$lr1_meta['Variety'] = [
			'id' => $Variety['id'],
			'name' => $Variety['name']
		];

		$lr1_meta['Lab_Result_Metric_list'] = $Lab_Result0->getMetrics();
		$lr1_meta['Lab_Result_Section_Metric_list'] = $Lab_Result0->getMetrics_Grouped();

		// Get Result
		$dbc_main = $this->_container->DBC_Main;
		$Lab_Result1 = new Lab_Result($dbc_main, $Lab_Result0['id']);
		$Lab_Result1['id'] = $Lab_Result0['id'];
		$Lab_Result1['license_id'] = $Lab_Result0['license_id'];
		$Lab_Result1['flag'] = $Lab_Result0['flag'];
		$Lab_Result1['stat'] = $Lab_Result0['stat'];
		$Lab_Result1['type'] = $lr1_meta['Product_Type']['name'];
		$Lab_Result1['name'] = $Lab_Result0['guid'];
		$Lab_Result1['meta'] = json_encode($lr1_meta);
		$Lab_Result1->save();

		$ret['data'] = [
			'pub' => sprintf('https://%s/pub/%s.html', $_SERVER['SERVER_NAME'], $Lab_Result1['id']),
			'coa' => (strlen($lr1_meta['Lab_Result']['coa_file']) ? sprintf('https://%s/pub/%s.pdf', $_SERVER['SERVER_NAME'], $Lab_Result1['id']) : ''),
			'json' => sprintf('https://%s/pub/%s.json', $_SERVER['SERVER_NAME'], $Lab_Result1['id']),
			'wcia' => sprintf('https://%s/pub/%s/wcia.json', $_SERVER['SERVER_NAME'], $Lab_Result1['id']),
		];

		// $this->_set_cache_json_wcia($lab_result, $output_data);
		// $this->_set_cache_html();
		// $this->_set_cache_pdf();

		return $RES->withJSON($ret);

	}

	/**
	 * Persist as HTML
	 */
	// function _set_cache_html($lab_result)
	// {
	// 	// $m = $lab_result['meta'];
	// 	$m = json_decode($lab_result['meta'], true);
	// 	if ( ! empty($m['coa'])) {

	// 	}
	// 	$output_file = sprintf('%s/webroot/pub/%s/%s.html', APP_ROOT, $lab_result['id'], $lab_result['guid']);
	// 	file_put_contents($output_file, $output_data);
	// }

	/**
	 * Persist as JSON+WCIA format
	 */
	// function _set_cache_json_wcia($lab_result, $output_data)
	// {
	// 	// Persist the WCIA data as a file
	// 	$output_file = sprintf('%s/webroot/pub/%s/wcia.json', APP_ROOT, $lab_result['id']);
	// 	$output_path = dirname($output_file);
	// 	mkdir($output_path, 0755, true);

	// 	$output_data['metric_list'] = array_values($output_data['metric_list']);
	// 	$output_data = json_encode($output_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

	// 	$x = file_put_contents($output_file, $output_data);

	// 	return $x;

	// }

	/**
	 * Persist as PDF
	 */
	function _set_cache_pdf($lab_result)
	{
		// $m = $lab_result['meta'];
		$m = json_decode($lab_result['meta'], true);
		if ( ! empty($m['coa'])) {
			// Get it?
			// $output_data = __curl_get();

		}

		$output_file = sprintf('%s/webroot/pub/%s/%s-COA.pdf', APP_ROOT, $lab_result['id'], $lab_result['guid']);
		$output_data = $pdf->output('', 'S');
		file_put_contents($output_file, $output_data);

		return sprintf('https://%s/pub/%s/%s-COA.pdf', $_SERVER['SERVER_NAME'], $lab_result['id'], $lab_result['guid']);

	}

}
