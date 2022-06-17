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
	'sample' => [
		'id' => $data['Lab_Sample']['guid'],
		'source_id' => $data['Lot']['guid'],
	],
	'labresult_id' => $data['Lab_Result']['guid'],
	'status' => 'pass',
	'coa' => sprintf('https://%s/pub/%s.pdf', $_SERVER['SERVER_NAME'], $data['Lab_Result']['id']),
	'metric_list' => [
		'Cannabinoids' => [
			'test_id' => 'Cannabinoids',
			'test_type' => 'Cannabinoids',
			'metrics' => [],
		],
		'Terpenes' => [
			'test_id' => 'Terpenes',
			'test_type' => 'Terpenes',
			'metrics' => [],
		],
		'General' => [
			'test_id' => 'General',
			'test_type' => 'General',
			'metrics' => [],
		],
		'Heavy Metals' => [
			'test_id' => 'Metals',
			'test_type' => 'Metals',
			'metrics' => [],
		],
		'Microbes' => [
			'test_id' => 'Microbes',
			'test_type' => 'Microbes',
			'metrics' => [],
		],
		'Mycotoxin' => [
			'test_id' => 'Mycotoxins',
			'test_type' => 'Mycotoxins',
			'metrics' => [],
		],
		// 'Water Activity' => [],
		// 'Foreign Matter' => [],
		// 'Loss on Drying' => [],
		'Pesticides' => [
			'test_id' => 'Pesticides',
			'test_type' => 'Pesticides',
			'metrics' => [],
		],
		'Solvents' => [
			'test_id' => 'Solvents',
			'test_type' => 'Solvents',
			'metrics' => [],
		],
	],
];


// Cannabinoids
$lmg = [];
$lmg[] = _wcia_lab_metric('018NY6XC00LM49CV7QP9KM9QH9', 'd9-THC', 'Cannabinoids', $lab_result_metric);
$lmg = [
	'id' => '018NY6XC00LMB0JPRM2SF8F9F2',
	'name' => 'd9-THCA',
	'analyte_type' => 'Cannabinoids',
	'qom' => $lab_result_metric['018NY6XC00LMB0JPRM2SF8F9F2']['qom'],
	'uom' => $lab_result_metric['018NY6XC00LMB0JPRM2SF8F9F2']['uom'],
	'status' => 'pass'
];
$lmg = [
	'id' => '018NY6XC00LMK7KHD3HPW0Y90N',
	'name' => 'CBD',
	'analyte_type' => 'Cannabinoids',
	'qom' => $lab_result_metric['018NY6XC00LMK7KHD3HPW0Y90N']['qom'],
	'uom' => $lab_result_metric['018NY6XC00LMK7KHD3HPW0Y90N']['uom'],
	'status' => 'pass'
];
$lmg = [
	'id' => '018NY6XC00LMENDHEH2Y32X903',
	'name' => 'CBDA',
	'analyte_type' => 'Cannabinoids',
	'qom' => $lab_result_metric['018NY6XC00LMENDHEH2Y32X903']['qom'],
	'uom' => $lab_result_metric['018NY6XC00LMENDHEH2Y32X903']['uom'],
	'status' => 'pass'
];
// $wcia['metric_list']['Cannabinoids'][] = [
// 	'id' => '',
// 	'name' => 'CBG',
// 	'analyte_type' => 'Cannabinoids',
// 	'qom' => $lab_result_metric['']['qom'],
// 	'uom' => $lab_result_metric['']['uom'],
// 	'status' => 'pass'
// ];
$lmg = [
	'id' => '018NY6XC00LM3W3G1ERAF2QEF5',
	'name' => 'CBN',
	'analyte_type' => 'Cannabinoids',
	'qom' => $lab_result_metric['018NY6XC00LM3W3G1ERAF2QEF5']['qom'],
	'uom' => $lab_result_metric['018NY6XC00LM3W3G1ERAF2QEF5']['uom'],
	'status' => 'pass'
];
// Total THC
$lmg = [
	'id' => '018NY6XC00PXG4PH0TXS014VVW',
	'name' => 'total-thc',
	'analyte_type' => 'Cannabinoids',
	'qom' => $lab_result_metric['018NY6XC00PXG4PH0TXS014VVW']['qom'],
	'uom' => $lab_result_metric['018NY6XC00PXG4PH0TXS014VVW']['uom'],
	'status' => 'pass'
];
// Total CBD
$lmg = [
	'id' => '018NY6XC00DEEZ41QBXR2E3T97',
	'name' => 'total-cbd',
	'analyte_type' => 'Cannabinoids',
	'qom' => $lab_result_metric['018NY6XC00DEEZ41QBXR2E3T97']['qom'],
	'uom' => $lab_result_metric['018NY6XC00DEEZ41QBXR2E3T97']['uom'],
	'status' => 'pass'
];
// Total THC + CBD
$lmg = [
	'id' => '018NY6XC00V7ACCY94MHYWNWRN',
	'name' => 'total-thc-cbd',
	'analyte_type' => 'Cannabinoids',
	'qom' => $lab_result_metric['018NY6XC00V7ACCY94MHYWNWRN']['qom'],
	'uom' => $lab_result_metric['018NY6XC00V7ACCY94MHYWNWRN']['uom'],
	'status' => 'pass'
];
// Total All
$lmg = [
	'id' => '018NY6XC00SAE8Q4JSMF40YSZ3',
	'name' => 'total-all',
	'analyte_type' => 'Cannabinoids',
	'qom' => $lab_result_metric['018NY6XC00SAE8Q4JSMF40YSZ3']['qom'],
	'uom' => $lab_result_metric['018NY6XC00SAE8Q4JSMF40YSZ3']['uom'],
	'status' => 'pass'
];
$wcia['metric_list']['Cannabinoids']['metrics'] = $lmg;


// Terpenes Here


// General
$lmg = [];
$lmg[] = _wcia_lab_metric('018NY6XC00LM0PXPG4592M8J14', 'Moisture Loss', 'General', $lab_result_metric);
$lmg[] = _wcia_lab_metric('018NY6XC00LMQAZZSDXPYH62SS', 'Foreign Matter', 'General', $lab_result_metric);
$lmg[] = _wcia_lab_metric('018NY6XC00LMHF4266DN94JPPX', 'Water Activity', 'General', $lab_result_metric);
$wcia['metric_list']['General']['metrics'] = $lmg;


// Microbes
if ( ! empty($lab_result_metric['018NY6XC00LM638QCGB50ZKYKJ'])) {
	$wcia['metric_list']['Microbes']['metrics'][] = [
		'id' => '018NY6XC00LM638QCGB50ZKYKJ',
		'name' => 'bile-tolerant-bacteria',
		'analyte_type' => 'Microbes',
		'qom' => $lab_result_metric['018NY6XC00LM638QCGB50ZKYKJ']['qom'],
		'uom' => 'cfu',
		'status' => 'pass'
	];
}

if ( ! empty($lab_result_metric['018NY6XC00LM7S8H2RT4K4GYME'])) {
	$wcia['metric_list']['Microbes']['metrics'][] = [
		'id' => '018NY6XC00LM7S8H2RT4K4GYME',
		'name' => 'e-coli',
		'analyte_type' => 'Microbes',
		'qom' => $lab_result_metric['018NY6XC00LM7S8H2RT4K4GYME']['qom'],
		'uom' => 'cfu',
		'status' => 'pass'
	];
}

if ( ! empty($lab_result_metric['018NY6XC00LMS96WE6KHKNP52T'])) {
	$wcia['metric_list']['Microbes']['metrics'][] = [
		'id' => '018NY6XC00LMS96WE6KHKNP52T',
		'name' => 'salmonella',
		'analyte_type' => 'Microbes',
		'qom' => $lab_result_metric['018NY6XC00LMS96WE6KHKNP52T']['qom'],
		'uom' => 'cfu',
		'status' => 'pass'
	];
}


// Mycotoxins
$wcia['metric_list']['Mycotoxin']['metrics'][] = _wcia_lab_metric('018NY6XC00LMR9PB7SNBP97DAS', 'Aflatoxin', 'Mycotoxin', $lab_result_metric);
$wcia['metric_list']['Mycotoxin']['metrics'][] = _wcia_lab_metric('018NY6XC00LMK15566W1G0ZH5X', 'Ochratoxin A', 'Mycotoxin', $lab_result_metric);


// Pesticides
$lmg = [];
$wcia['metric_list']['Pesticides']['metrics'] = $lmg;


// Solvents
$lmg = [];
foreach ($data['Lab_Result_Metric_list'] as $k => $v) {
	if ('Solvent' == $v['type']) {
		$lmg[] = _wcia_lab_metric($k, $v['name'], 'Solvents', $lab_result_metric);
	}
}
$wcia['metric_list']['Solvents']['metrics'] = $lmg;
// __exit_text($lab_result_metric);

$lmg = [];
$lmg[] = _wcia_lab_metric('018NY6XC00LM0PXPG4592M8J14', 'Moisture Loss', 'General', $lab_result_metric);
$lmg[] = _wcia_lab_metric('018NY6XC00LMQAZZSDXPYH62SS', 'Foreign Matter', 'General', $lab_result_metric);
$lmg[] = _wcia_lab_metric('018NY6XC00LMHF4266DN94JPPX', 'Water Activity', 'General', $lab_result_metric);
$wcia['metric_list']['General']['metrics'] = $lmg;


// Cleanup to Remove Named Keys
$wcia['metric_list'] = array_values($wcia['metric_list']);


return($wcia);

/**
 * Little Output Array Helper
 */
function _wcia_lab_metric($id, $name, $type, $data)
{
	$ret = [
		'id' => $id,
		'name' => $name,
		'analyte_type' => $type,
		'qom' => $data[$id]['qom'],
		'uom' => $data[$id]['uom'],
		'status' => 'pass'
	];

	switch ($ret['qom']) {
		case -3:
			$ret['status'] = 'ND';
			break;
	}

	return $ret;

}
