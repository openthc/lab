<?php
/**
 * CCRS Output Format
 *
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * @see https://lcb.wa.gov/sites/default/files/publications/Marijuana/CCRS/Lab%20Test%20CSV%20TestName%20data%20field%20expanded%20detail.pdf
 */

use OpenTHC\CRE\CCRS;

header('content-type: text/plain');

// @hack
$data['License_Origin']['code'] = 'SRC_LIC_CODE';
$data['Laboratory_License']['code'] = 'LAB_LIC_CODE';

$dt0 = new DateTime($data['Lab_Result']['created_at']);

// Here's the Metric IDs we want to capture and the CCRS Names
$out_metric_list = [];
$out_metric_list['01FZ1H5VDP9GJZM5KBEYTGBQ50'] = '-?-';
$out_metric_list['018NY6XC00LM49CV7QP9KM9QH9'] = 'Potency - D9THC (%)'; // (mg/g) | (mg/mL) | (mg/serving)
$out_metric_list['018NY6XC00LMB0JPRM2SF8F9F2'] = 'Potency - D9THCA (%)'; // (mg/g) | (mg/mL) | (mg/serving)
$out_metric_list['018NY6XC00LMK7KHD3HPW0Y90N'] = 'Potency - CBD (%)'; // (mg/g) | (mg/mL) | (mg/serving)
$out_metric_list['018NY6XC00LMENDHEH2Y32X903'] = 'Potency - CBDA (%)'; // (mg/g) | (mg/mL) | (mg/serving)
$out_metric_list['018NY6XC00PXG4PH0TXS014VVW'] = 'Potency - Total THC (%)'; // (mg/g) | (mg/mL) | (mg/serving)
$out_metric_list['018NY6XC00DEEZ41QBXR2E3T97'] = 'Potency - Total CBD (%)'; // (mg/g) | (mg/mL) | (mg/serving)

$out_metric_list['018NY6XC00LMK15566W1G0ZH5X'] = 'Mycotoxin - Ochratoxin A (ug/kg)';
$out_metric_list['018NY6XC00LMR9PB7SNBP97DAS'] = 'Mycotoxin - Total Aflatoxins (ug/kg)';

$out_metric_list['018NY6XC00LMHF4266DN94JPPX'] = 'Moisture Analysis - Water Activity (aw)';
$out_metric_list['018NY6XC00LM0PXPG4592M8J14'] = 'Moisture Analysis - Moisture Content (%)';

// @todo Handle these booleans properly
$out_metric_list['018NY6XC00LMQAZZSDXPYH62SS'] = 'Foreign Matter - Stems (%)';
$out_metric_list['018NY6XC00LMA50497RDC53DB5'] = 'Foreign Matter - Seeds or Other (%)';

$out_metric_list['018NY6XC00LM638QCGB50ZKYKJ'] = 'Microbiological - BTGN (CFU/g)';
$out_metric_list['018NY6XC00LMS96WE6KHKNP52T'] = 'Microbiological - Salmonella (CFU/g)';
$out_metric_list['018NY6XC00LM7S8H2RT4K4GYME'] = 'Microbiological - STEC (CFU/g)';

$out_metric_list['018NY6XC00LM4E4T6EPA7WPHDK'] = 'Heavy Metal - Arsenic (ug/g) Heavy Metal - Arsenic (ppm)';
$out_metric_list['018NY6XC00LMGNGNEW1XMNRS8S'] = 'Heavy Metal - Cadmium (ug/g) Heavy Metal - Cadmium (ppm)';
$out_metric_list['018NY6XC00LM6YBP4J5ASBWVNR'] = 'Heavy Metal - Lead (ug/g) Heavy Metal - Lead (ppm)';
$out_metric_list['018NY6XC00LM10ZPAN42R490W3'] = 'Heavy Metal - Mercury (ug/g) Heavy Metal - Mercury (ppm)';


// Solvent
// Residual Solvent - Acetone (ug/g) Residual Solvent - Acetone (ppm)
// Residual Solvent - Benzene (ug/g) Residual Solvent - Benzene (ppm)
// Residual Solvent - Butanes (ug/g) Residual Solvent - Butanes (ppm)
// Residual Solvent - Cyclohexane (ug/g) Residual Solvent - Cyclohexane (ppm)
// Residual Solvent - Chloroform (ug/g) Residual Solvent - Chloroform (ppm)
// Residual Solvent - Dichloromethane (ug/g) Residual Solvent - Dichloromethane (ppm)
// Residual Solvent - Ethyl Acetate (ug/g) Residual Solvent - Ethyl Acetate (ppm)
// Residual Solvent - Heptanes (ug/g) Residual Solvent - Heptanes (ppm)
// Residual Solvent - Hexanes (ug/g) Residual Solvent - Hexanes (ppm)
// Residual Solvent - Isopropanol (ug/g) Residual Solvent - Isopropanol (ppm)
// Residual Solvent - Methanol (ug/g) Residual Solvent - Methanol (ppm)
// Residual Solvent - Pentanes (ug/g) Residual Solvent - Pentanes (ppm)
// Residual Solvent - Propanes (ug/g) Residual Solvent - Propanes (ppm)
// Residual Solvent - Toluene (ug/g) Residual Solvent - Toluene (ppm)
// Residual Solvent - Xylenes (ug/g) Residual Solvent - Xylenes (ppm)

// Pesticide
// Pesticide - Abamectin (ug/g) Pesticide - Abamectin (ppm)
// Pesticide - Acephate (ug/g) Pesticide - Acephate (ppm)
// Pesticide - Acequinocyl (ug/g) Pesticide - Acequinocyl (ppm)
// Pesticide - Acetamiprid (ug/g) Pesticide - Acetamiprid (ppm)
// Pesticide - Aldicarb (ug/g) Pesticide - Aldicarb (ppm)
// Pesticide - Azoxystrobin (ug/g) Pesticide - Azoxystrobin (ppm)
// Pesticide - Bifenazate (ug/g) Pesticide - Bifenazate (ppm)
// Pesticide - Bifenthrin (ug/g) Pesticide - Bifenthrin (ppm)
// Pesticide - Boscalid (ug/g) Pesticide - Boscalid (ppm)
// Pesticide - Carbaryl (ug/g) Pesticide - Carbaryl (ppm)
// Pesticide - Carbofuran (ug/g) Pesticide - Carbofuran (ppm)
// Pesticide - Chlorantraniliprole (ug/g) Pesticide - Chlorantraniliprole (ppm)
// Pesticide - Chlorfenapyr (ug/g) Pesticide - Chlorfenapyr (ppm)
// Pesticide - Chlorpyrifos (ug/g) Pesticide - Chlorpyrifos (ppm)
// Pesticide - Clofentizine (ug/g) Pesticide - Clofentizine (ppm)
// Pesticide - Cyfluthrin (ug/g) Pesticide - Cyfluthrin (ppm)
// Pesticide - Cypermethrin (ug/g) Pesticide - Cypermethrin (ppm)
// Pesticide - Daminozide (ug/g) Pesticide - Daminozide (ppm)
// Pesticide - DDVP (dichlorvos) (ug/g) Pesticide - DDVP (dichlorvos) (ppm)
// Pesticide - Diazinon (ug/g) Pesticide - Diazinon (ppm)
// Pesticide - Dimethoate (ug/g) Pesticide - Dimethoate (ppm)
// Pesticide - Ethoprophos (ug/g) Pesticide - Ethoprophos (ppm)
// Pesticide - Etofenprox (ug/g) Pesticide - Etofenprox (ppm)
// Pesticide - Etoxazole (ug/g) Pesticide - Etoxazole (ppm)
// Pesticide - Fenoxycarb (ug/g) Pesticide - Fenoxycarb (ppm)
// Pesticide - Fenpyroximate (ug/g) Pesticide - Fenpyroximate (ppm)
// Pesticide - Fipronil (ug/g) Pesticide - Fipronil (ppm)
// Pesticide - Flonicamid (ug/g) Pesticide - Flonicamid (ppm)
// Pesticide - Fludioxonil (ug/g) Pesticide - Fludioxonil (ppm)
// Pesticide - Hexythiazox (ug/g) Pesticide - Hexythiazox (ppm)
// Pesticide - Imazalil (ug/g) Pesticide - Imazalil (ppm)
// Pesticide - Imidacloprid (ug/g) Pesticide - Imidacloprid (ppm)
// Pesticide - Kresoxim-Methyl (ug/g) Pesticide - Kresoxim-Methyl (ppm)
// Pesticide - Malathion (ug/g) Pesticide - Malathion (ppm)
// Pesticide - Metalaxyl (ug/g) Pesticide - Metalaxyl (ppm)
// Pesticide - Methiocarb (ug/g) Pesticide - Methiocarb (ppm)
// Pesticide - Methomyl (ug/g) Pesticide - Methomyl (ppm)
// Pesticide - Methyl parathion (ug/g) Pesticide - Methyl parathion (ppm)
// Pesticide - MGK-264 (ug/g) Pesticide - MGK-264 (ppm)
// Pesticide - Myclobutanil (ug/g) Pesticide - Myclobutanil (ppm)
// Pesticide - Naled (ug/g) Pesticide - Naled (ppm)
// Pesticide - Oxamyl (ug/g) Pesticide - Oxamyl (ppm)
// Pesticide - Paclobutrazol (ug/g) Pesticide - Paclobutrazol (ppm)
// Pesticide - Permethrins (ug/g) Pesticide - Permethrins (ppm)
// Pesticide - Phosmet (ug/g) Pesticide - Phosmet (ppm)
// Pesticide - Piperonyl butoxide (ug/g) Pesticide - Piperonyl butoxide (ppm)
// Pesticide - Prallethrin (ug/g) Pesticide - Prallethrin (ppm)
// Pesticide - Propiconazole (ug/g) Pesticide - Propiconazole (ppm)
// Pesticide - Propoxur (ug/g) Pesticide - Propoxur (ppm)
// Pesticide - Pyrethrins (ug/g) Pesticide - Pyrethrins (ppm)
// Pesticide - Pyridaben (ug/g) Pesticide - Pyridaben (ppm)
// Pesticide - Spinosad (ug/g) Pesticide - Spinosad (ppm)
// Pesticide - Spiromesifen (ug/g) Pesticide - Spiromesifen (ppm)
// Pesticide - Spirotetramat (ug/g) Pesticide - Spirotetramat (ppm)
// Pesticide - Spiroxamine (ug/g) Pesticide - Spiroxamine (ppm)
// Pesticide - Tebuconazole (ug/g) Pesticide - Tebuconazole (ppm)
// Pesticide - Thiacloprid (ug/g) Pesticide - Thiacloprid (ppm)
// Pesticide - Thiamethoxam (ug/g) Pesticide - Thiamethoxam (ppm)
// Pesticide - Trifloxystrobin (ug/g) Pesticide - Trifloxystrobin (ppm)


// Build Output
$csv_output = [];
foreach ($out_metric_list as $mk0 => $mn0) {

	$lrm = $data['Lab_Result_Metric_list'][$mk0];

	$csv_output[] = [
		$data['License_Origin']['code'], // License Owner
		$data['Lot']['guid'],
		$data['Laboratory_License']['code'], // Code of the Laboratory
		'Pass',
		$mn0, // trim(sprintf('%s %s', $lrm['name'], $lrm['uom'])),
		$dt0->format('Y-m-d'), // Test Date
		$lrm['qom'],
		$lrm['id'],
		$dt0->format('Y-m-d'),
		'-system-',
		'',
		'',
		'INSERT',
	];
}

$out_handle = fopen('php://output', 'a');
CCRS::fputcsv_stupidly($out_handle, explode(',', 'SubmittedBy,OpenTHC,,,,,,,,,,,'));
CCRS::fputcsv_stupidly($out_handle, explode(',', sprintf('SubmittedDate,%s,,,,,,,,,,,', date('m/d/Y'))));
CCRS::fputcsv_stupidly($out_handle, explode(',', sprintf('NumberRecords,%d,,,,,,,,,,,', count($csv_output))));
CCRS::fputcsv_stupidly($out_handle, explode(',', 'LicenseNumber,InventoryExternalIdentifier,LabLicenseNumber,LabTestStatus,TestName,TestDate,TestValue,ExternalIdentifier,CreatedBy,CreatedDate,UpdatedBy,UpdatedDate,Operation'));

foreach ($csv_output as $row) {
	CCRS::fputcsv_stupidly($out_handle, $row);
}

exit(0);
