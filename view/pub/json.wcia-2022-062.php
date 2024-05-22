<?php
/**
 * View Data in WCIA Preferred JSON Format
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$lab_result_metric = $data['Lab_Result_Metric_list'];

// Data to Return
$wcia = [
	'document_name' => 'WCIA Lab Result Schema',
	'document_schema_version' => '2.0.0',
	'document_origin' => null,
	'lab_name' => $data['Laboratory']['License']['name'],
	'lab_ubi_license' => $data['Laboratory']['Company']['guid'],
	'lab_ccrs_license' => $data['Laboratory']['License']['code'],
	'sample' => [
		'id' => $data['Lot']['guid'], // $data['Lab_Sample']['name'],
		'source_id' => $data['Lot']['guid'],
	],
	'labresult_id' => $data['Lab_Result']['guid'] ?: $data['Lab_Result']['name'],
	'result' => [
		// '@note' => 'Experimental Field by OpenTHC',
		'id' => $data['Lab_Result']['guid'],
		'status' => 'pass',
		'created_at' => $data['Lab_Result']['created_at'],
		'approved_at' => $data['Lab_Result']['approved_at'],
		'expires_at' => $data['Lab_Result']['expires_at'],
	],
	'status' => 'pass',
	'coa' => null,
	'release_date' => $data['Lab_Result']['approved_at'],
	'amended_date' => null, // ?? Have we seen this one?
	'expire_date' => $data['Lab_Result']['expires_at'],
	'metric_list' => [],
];


// Terpenes Here
foreach ($data['Lab_Result_Section_Metric_list'] as $lms) {

	$tmp_metric_list = [];

	foreach ($lms['metric_list'] as $lm) {

		$lrm = $lab_result_metric[ $lm['id'] ];

		if (0 == strlen($lrm['qom'])) {
			continue;
		}

		$tmp_metric_list[] = _wcia_lab_metric_2([
			'id' => $lrm['id'],
			'lab_metric_id' => $lrm['lab_metric_id'],
			'lab_metric_name' => $lrm['name'],
			'name' => $lm['name'],
			'stat' => $lrm['stat'],
			'qom' => $lrm['qom'],
			'uom' => $lrm['uom']
		], $lms['name']);

		if (200 != $lrm['stat']) {
			$wcia['status'] = 'fail';
		}
	}

	if (count($tmp_metric_list)) {
		$wcia['metric_list'][ $lms['id'] ]['test_id'] = $lms['id'];
		$wcia['metric_list'][ $lms['id'] ]['test_type'] = $lms['name'];
		$wcia['metric_list'][ $lms['id'] ]['metrics'] = $tmp_metric_list;
	}

}

// Cleanup to Remove Named Keys
$wcia['metric_list'] = array_values($wcia['metric_list']);

return $wcia;


/**
 * Little Output Array Helper
 */
function _wcia_lab_metric_2($data, $type)
{
	if (empty($data['id'])) {
		return null;
	}

	$ret = [
		'id' => $data['lab_metric_id'] ?: $data['id'],
		'name' => $data['lab_metric_name'] ?: $data['name'],
		'analyte_type' => $type,
		'qom' => $data['qom'],
		'uom' => $data['uom'],
		'status' => ($data['stat'] == 200 ? 'pass' : 'fail')
	];

	switch ($ret['qom']) {
		case -1:
			$ret['qom'] = 0;
			$ret['status'] = 'N/A';
			break;
		case -2:
			$ret['qom'] = 0;
			$ret['status'] = 'pass'; // N/D';
			break;
		case -3:
			$ret['qom'] = 0;
			$ret['status'] = 'N/T';
			break;
	}

	return $ret;

}
