<?php
/**
 * View Data in WCIA Preferred JSON Format
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

$lab_result_metric = $data['Lab_Result_Metric_list'];
$lab_result_section_metric = $data['Lab_Result_Section_Metric_list'];

// Data to Return
$wcia = [
	'document_name' => 'WCIA Lab Result Schema',
	'document_schema_version' => '2.0.0',
	'sample' => [
		'id' => $data['Lab_Sample']['name'],
		'source_id' => $data['Lot']['guid'],
	],
	'labresult_id' => $data['Lab_Result']['guid'],
	'result' => [
		'@note' => 'Experimental Field by OpenTHC',
		'id' => $data['Lab_Result']['guid'],
		'status' => 'pass',
		'created_at' => $data['Lab_Result']['created_at'],
		'approved_at' => $data['Lab_Result']['approved_at'],
		'expires_at' => $data['Lab_Result']['expires_at'],
	],
	'status' => 'pass',
	'coa' => sprintf('https://%s/pub/%s.pdf', $_SERVER['SERVER_NAME'], $data['Lab_Result']['id']),
	'metric_list' => [
		// '018NY6XC00LMT0HRHFRZGY72C7' => [
		// 	'test_id' => '018NY6XC00LMT0HRHFRZGY72C7',
		// 	'test_type' => 'Cannabinoids',
		// 	'metrics' => []
		// ],
		// '018NY6XC00LMT07DPNKHQV2GRS' => [
		// 	'test_id' => '018NY6XC00LMT07DPNKHQV2GRS',
		// 	'test_type' => 'Terpenes',
		// 	'metrics' => []
		// ],
		// '018NY6XC00LMT0BY5GND653C0C' => [
		// 	'test_id' => '018NY6XC00LMT0BY5GND653C0C',
		// 	'test_type' => 'General',
		// 	'metrics' => [],
		// ],
		// '018NY6XC00LMT0B7NMK7RGYAMN' => [
		// 	'test_id' => '018NY6XC00LMT0B7NMK7RGYAMN',
		// 	'test_type' => 'Microbes',
		// 	'metrics' => []
		// ],
		// 'Mycotoxin' => [],
		// 'Water Activity' => [],
		// 'Foreign Matter' => [],
		// 'Loss on Drying' => [],
		// '018NY6XC00LMT09ZG05C2NE7KX' => [
		// 	'test_id' => '018NY6XC00LMT09ZG05C2NE7KX',
		// 	'test_type' => 'Pesticides',
		// 	'metrics' => [],
		// ],
		// '018NY6XC00LMT0AQAMJEDSD0NW' => [
		// 	'test_id' => '018NY6XC00LMT0AQAMJEDSD0NW',
		// 	'test_type' => 'Solvents',
		// 	'metrics' => [],
		// ],
	],
];


// Cannabinoids
// $lmg = [];
// $lmg[] = _wcia_lab_metric_2($lab_result_metric['018NY6XC00LM49CV7QP9KM9QH9'], 'Cannabinoids'); // d9-THC
// $lmg[] = _wcia_lab_metric_2($lab_result_metric['018NY6XC00LMB0JPRM2SF8F9F2'], 'Cannabinoids'); // d9-THCA
// $lmg[] = _wcia_lab_metric_2($lab_result_metric['018NY6XC00LMK7KHD3HPW0Y90N'], 'Cannabinoids'); // CBD
// $lmg[] = _wcia_lab_metric_2($lab_result_metric['018NY6XC00LMENDHEH2Y32X903'], 'Cannabinoids'); // CBDA
// $wcia['metric_list']['Cannabinoids'][] = [
// 	'id' => '',
// 	'name' => 'CBG',
// 	'analyte_type' => 'Cannabinoids',
// 	'qom' => $lab_result_metric['']['qom'],
// 	'uom' => $lab_result_metric['']['uom'],
// 	'status' => 'pass'
// ];
// $lmg[] = _wcia_lab_metric_2($lab_result_metric['018NY6XC00LM3W3G1ERAF2QEF5'], 'Cannabinoids'); // CBN

// // Total THC
// $lmg[] = _wcia_lab_metric_2($lab_result_metric['018NY6XC00PXG4PH0TXS014VVW'], 'Cannabinoids');
// // Total CBD
// $lmg[] = _wcia_lab_metric_2($lab_result_metric['018NY6XC00DEEZ41QBXR2E3T97'], 'Cannabinoids');
// // Total THC + CBD
// $lmg[] = _wcia_lab_metric_2($lab_result_metric['018NY6XC00V7ACCY94MHYWNWRN'], 'Cannabinoids');
// // Total All
// $lmg[] = _wcia_lab_metric_2($lab_result_metric['018NY6XC00SAE8Q4JSMF40YSZ3'], 'Cannabinoids');

// $wcia['metric_list']['018NY6XC00LMT0HRHFRZGY72C7']['metrics'] = array_values(array_filter($lmg));

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
			'name' => $lrm['name'],
			'qom' => $lrm['qom'],
			'uom' => $lrm['uom']
		], $lms['name']);
	}

	if (count($tmp_metric_list)) {
		$wcia['metric_list'][ $lms['id'] ]['test_id'] = $lms['id'];
		$wcia['metric_list'][ $lms['id'] ]['test_type'] = $lms['name'];
		$wcia['metric_list'][ $lms['id'] ]['metrics'] = $tmp_metric_list;
	}

}

// General
// $lmg = [];
// $lmg[] = _wcia_lab_metric('018NY6XC00LM0PXPG4592M8J14', 'General', $lab_result_metric);
// $lmg[] = _wcia_lab_metric('018NY6XC00LMQAZZSDXPYH62SS', 'General', $lab_result_metric);
// $lmg[] = _wcia_lab_metric('018NY6XC00LMHF4266DN94JPPX', 'General', $lab_result_metric);
// $wcia['metric_list']['General']['metrics'] = array_values(array_filter($lmg));


// Microbes
// if ( ! empty($lab_result_metric['018NY6XC00LM638QCGB50ZKYKJ'])) {
// 	$wcia['metric_list']['Microbes']['metrics'][] = [
// 		'id' => '018NY6XC00LM638QCGB50ZKYKJ',
// 		'name' => 'bile-tolerant-bacteria',
// 		'analyte_type' => 'Microbes',
// 		'qom' => $lab_result_metric['018NY6XC00LM638QCGB50ZKYKJ']['qom'],
// 		'uom' => 'cfu',
// 		'status' => 'pass'
// 	];
// }

// if ( ! empty($lab_result_metric['018NY6XC00LM7S8H2RT4K4GYME'])) {
// 	$wcia['metric_list']['Microbes']['metrics'][] = [
// 		'id' => '018NY6XC00LM7S8H2RT4K4GYME',
// 		'name' => 'e-coli',
// 		'analyte_type' => 'Microbes',
// 		'qom' => $lab_result_metric['018NY6XC00LM7S8H2RT4K4GYME']['qom'],
// 		'uom' => 'cfu',
// 		'status' => 'pass'
// 	];
// }

// if ( ! empty($lab_result_metric['018NY6XC00LMS96WE6KHKNP52T'])) {
// 	$wcia['metric_list']['Microbes']['metrics'][] = [
// 		'id' => '018NY6XC00LMS96WE6KHKNP52T',
// 		'name' => $lab_result_metric['018NY6XC00LMS96WE6KHKNP52T']['lab_metric_name'],
// 		'analyte_type' => 'Microbes',
// 		'qom' => $lab_result_metric['018NY6XC00LMS96WE6KHKNP52T']['qom'],
// 		'uom' => 'cfu',
// 		'status' => 'pass'
// 	];
// }


// Mycotoxins
// $wcia['metric_list']['Mycotoxin']['metrics'][] = _wcia_lab_metric('018NY6XC00LMR9PB7SNBP97DAS', 'Mycotoxin', $lab_result_metric);
// $wcia['metric_list']['Mycotoxin']['metrics'][] = _wcia_lab_metric('018NY6XC00LMK15566W1G0ZH5X', 'Mycotoxin', $lab_result_metric);
// $wcia['metric_list']['Mycotoxin']['metrics'][] = _wcia_lab_metric('01FX796BWY4KC12HBZ35GKR677', 'Mycotoxin', $lab_result_metric);


// Pesticides
// $lmg = [];
// $wcia['metric_list']['Pesticides']['metrics'] = $lmg;


// Solvents
// $lmg = [];
// foreach ($data['Lab_Result_Metric_list'] as $k => $v) {
// 	if ('Solvent' == $v['type']) {
// 		$lmg[] = _wcia_lab_metric($k, 'Solvents', $lab_result_metric);
// 	}
// }
// $wcia['metric_list']['Solvents']['metrics'] = $lmg;
// __exit_text($lab_result_metric);

// $lmg = [];
// $lmg[] = _wcia_lab_metric('018NY6XC00LM0PXPG4592M8J14', 'General', $lab_result_metric);
// $lmg[] = _wcia_lab_metric('018NY6XC00LMQAZZSDXPYH62SS', 'General', $lab_result_metric);
// $lmg[] = _wcia_lab_metric('018NY6XC00LMHF4266DN94JPPX', 'General', $lab_result_metric);
// $wcia['metric_list']['General']['metrics'] = $lmg;


// Cleanup to Remove Named Keys
$wcia['metric_list'] = array_values($wcia['metric_list']);

return($wcia);


/**
 * Little Output Array Helper
 */
function _wcia_lab_metric($id, $type, $data)
{
	$ret = [
		'id' => $id,
		'name' => $data[$id]['lab_metric_name'] ?: $data[$id]['name'],
		'analyte_type' => $type,
		'qom' => $data[$id]['qom'],
		'uom' => $data[$id]['uom'],
		'status' => 'pass'
	];

	switch ($ret['qom']) {
		case -1:
			$ret['status'] = 'N/A';
			break;
		case -2:
			$ret['status'] = 'N/T';
			break;
		case -3:
			$ret['status'] = 'N/D';
			break;
	}

	return $ret;

}

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
		'status' => 'pass'
	];

	switch ($ret['qom']) {
		case -1:
			$ret['status'] = 'N/A';
			break;
		case -2:
			$ret['status'] = 'N/T';
			break;
		case -3:
			$ret['status'] = 'N/D';
			break;
	}

	return $ret;

}
