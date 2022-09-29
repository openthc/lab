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


		$dbc_auth = $this->_container->DBC_Auth;
		$dbc_main = $this->_container->DBC_Main;

		$Company = $dbc_auth->fetchRow('SELECT id, name, dsn FROM auth_company WHERE id = :c0', [
			':c0' => $data['company']['id']
		]);
		if (empty($Company['id'])) {
			__exit_json([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Input [CAP-049]' ]
			]);
		}

		$lr0_meta = $data;
		$lr0_meta['@context'] = 'http://openthc.org/lab/2022';
		$lr0_meta['@version'] = '2022.256';

		$ret_code = 200;
		$ret_data = [];

		$Lab_Result1 = new Lab_Result($dbc_main, $data['lab_result']['id']);
		if (empty($Lab_Result1['id'])) {
			$ret_code = 201;
		}
		$Lab_Result1['id'] = $data['lab_result']['id'];
		$Lab_Result1['license_id'] = $data['license']['id'];
		$Lab_Result1['flag'] = intval($data['lab_result']['flag']);
		$Lab_Result1['stat'] = intval($data['lab_result']['stat']);
		$Lab_Result1['type'] = ($data['product_type']['name'] ?: '-orphan-');
		$Lab_Result1['name'] = $data['lab_result']['guid'];
		$Lab_Result1['meta'] = json_encode($lr0_meta);
		$Lab_Result1->save();
		$ret_data['pub'] = sprintf('https://%s/pub/%s.html', $_SERVER['SERVER_NAME'], $Lab_Result1['id']);

		// Attachment
		if ( ! empty($data['lab_result_file']['body'])) {

			$lrf_data = [];
			$lrf_data['body'] = __base64_decode_url($data['lab_result_file']['body']);
			$lrf_data['name'] = $data['lab_result_file']['name'];
			$lrf_data['size'] = strlen($data['lab_result_file']['body']);
			$lrf_data['type'] = $data['lab_result_file']['type'];

			if ($lrf_data['size']) {

				$sql = <<<SQL
				INSERT INTO lab_result_file (id, lab_result_id, name, size, type, body)
				VALUES (ulid_create(), :lr0, :n1, :s1, :t1, :b1)
				SQL;

				$cmd = $dbc_main->prepare($sql, null);
				$cmd->bindParam(':lr0', $Lab_Result1['id']);
				$cmd->bindParam(':n1', $coa_data['name']);
				$cmd->bindParam(':s1', $coa_data['size']);
				$cmd->bindParam(':t1', $coa_data['type']);
				$cmd->bindParam(':b1', $coa_data['body'], \PDO::PARAM_LOB);
				$cmd->execute();

				$coa_file = $Lab_Result1->getCOAFile();
				$coa_path = dirname($coa_file);
				if ( ! is_dir($coa_path)) {
					mkdir($coa_path, 0755, true);
				}
				file_put_contents($coa_file, $lrf_data['body']);

				$ret_data['coa'] = sprintf('https://%s/pub/%s.pdf', $_SERVER['SERVER_NAME'], $Lab_Result1['id']);
			}

		}

		return $RES->withJSON([
			'data' => $ret_data,
			'meta' => [ ]
		], $ret_code);

		// $ret['data'] = [
		// 	'pub' => sprintf('https://%s/pub/%s.html', $_SERVER['SERVER_NAME'], $Lab_Result1['id']),
		// 	'coa' => (strlen($lr1_meta['Lab_Result']['coa_file']) ? sprintf('https://%s/pub/%s.pdf', $_SERVER['SERVER_NAME'], $Lab_Result1['id']) : ''),
		// 	'json' => sprintf('https://%s/pub/%s.json', $_SERVER['SERVER_NAME'], $Lab_Result1['id']),
		// 	'wcia' => sprintf('https://%s/pub/%s/wcia.json', $_SERVER['SERVER_NAME'], $Lab_Result1['id']),
		// ];

		// // $this->_set_cache_json_wcia($lab_result, $output_data);
		// // $this->_set_cache_html();
		// // $this->_set_cache_pdf();

		// return $RES->withJSON($ret);

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
