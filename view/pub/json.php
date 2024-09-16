<?php
/**
 * Output Data in OpenTHC Format
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

// "Lab_Sample",
// "Lab_Result",
// "Lab_Result_Metric_list",
// "Lab_Metric_Type_list",
// "Product",
// "Product_Type",
// "Variety",
// "Source_License",
// "Inventory",
// "License_Laboratory"

$output = [];
// Laboratory License
$output['License'] = [
	'@id' => $data['License_Laboratory']['id'],
	'name' => $data['License_Laboratory']['name'],
	'code' => $data['License_Laboratory']['code'],
];

$output['Inventory'] = [
	'@id' => $data['Inventory']['id'],
	'guid' => $data['Inventory']['guid'],
	'name' => $data['Inventory']['name'],
	'product' => [
		'@id' => $data['Inventory']['product_id'],
		'guid' => $data['Product']['guid'],
		'name' => $data['Product']['name'],
	],
	'section' => [
		'@id' => $data['Inventory']['section_id'],
		'guid' => $data['Section']['guid'],
		'name' => $data['Section']['name'],
	],
	'variety' => [
		'id' => $data['Inventory']['variety_id'],
		'name' => $data['Variety']['name'],
	],
];

$output['Sample'] = [
	'@id' => $data['Lab_Sample']['id'], // @todo v0 should be guid
	'guid' => $data['Lab_Sample']['name'], // @todo v0 should be guid
	'qty' => $data['Lab_Sample']['qty'],
];

$output['Result'] = [
	'@id' => $data['Lab_Result']['id'],
	'guid' => $data['Lab_Result']['guid'],
	// 'name' => ''
	'metric_list' => [],
];

foreach ($data['Lab_Result_Metric_list'] as $lrm0) {

	if (empty($lrm0['id'])) {
		// NO Result
		continue;
	}

	$output['Result']['metric_list'][] = [
		'@id' => $lrm0['id'],
		'metric' => [
			'id' => $lrm0['lab_metric_id'],
			'type' => $lrm0['type'],
			'name' => $lrm0['name'],
		],
		'qom' => $lrm0['qom'],
		'uom' => $lrm0['uom'],
	];
}

return $output;
