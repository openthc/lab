<?php
/**
 * CCRS Output Format
 *
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * @see https://lcb.wa.gov/sites/default/files/publications/Marijuana/CCRS/Lab%20Test%20CSV%20TestName%20data%20field%20expanded%20detail.pdf
 *
 * Please use the listed TestName's when reporting TestValues. Unit of measure will be included in the
 * TestName and NOT in the the TestValue.
 * For the puposes of our reporting we will use the following units as equivalent:
 * ug/g = parts per million (ppm)
 * ug/kg = parts per billion (ppb)
 */

use OpenTHC\Lab\Lab_Result;

use OpenTHC\CRE\CCRS;

if (empty($data['Lab_Result'])) {
	if ( ! empty($data['Lab_Report'])) {
		$data['Lab_Result'] = $data['Lab_Report'];
		unset($data['Lab_Report']);
	}
}

if (empty($data['Lab_Result_Metric_list'])) {
	// $data['Lab_Result_Metric_list']
	// __exit_text($data['Lab_Result']['meta']);
	$data['Lab_Result_Metric_list'] = $data['Lab_Result']['meta']['lab_result_metric_list'];
}
if (empty($data['Lab_Result_Metric_list'])) {
	throw new \Exception('Invalid Data [VPC-033]');
}


// Get Some Configuration Options
$csv_config = [];
$csv_config['lab_name'] = $data['License_Laboratory']['name'] ?: $_SESSION['Company']['name'];

$dt0 = new DateTime($data['Lab_Result']['approved_at']);
$dtA = clone $dt0;

// $dtA = new \DateTime($data['Lab_Sample']['approved_at']);
// @todo Fix this Date to use the Source Value
// $dt0 = new DateTime($data['Lab_Result']['created_at']);


// Here's the Metric IDs we want to capture and the CCRS Names
$out_metric_list = [];
// $out_metric_list['01FZ1H5VDP9GJZM5KBEYTGBQ50'] = '-?-';
$out_metric_list['018NY6XC00LM49CV7QP9KM9QH9'] = 'Potency - delta-9-THC (mg/g)'; // (mg/g) | (mg/mL) | (mg/serving)
$out_metric_list['018NY6XC00LMB0JPRM2SF8F9F2'] = 'Potency - delta-9-THCA (mg/g)'; // (mg/g) | (mg/mL) | (mg/serving)
$out_metric_list['018NY6XC00LMK7KHD3HPW0Y90N'] = 'Potency - CBD (mg/g)'; // (mg/g) | (mg/mL) | (mg/serving)
$out_metric_list['018NY6XC00LMENDHEH2Y32X903'] = 'Potency - CBDA (mg/g)'; // (mg/g) | (mg/mL) | (mg/serving)
$out_metric_list['018NY6XC00PXG4PH0TXS014VVW'] = 'Potency - Total THC (mg/g)'; // (mg/g) | (mg/mL) | (mg/serving)
$out_metric_list['018NY6XC00DEEZ41QBXR2E3T97'] = 'Potency - Total CBD (mg/g)'; // (mg/g) | (mg/mL) | (mg/serving)


// Updated for PPB
$out_metric_list['018NY6XC00LMR9PB7SNBP97DAS'] = 'Mycotoxin - Total Aflatoxins (ug/kg)'; // (ppb)
$out_metric_list['01EDPTAXEXNAN89RM5AN0VVTP3'] = 'Mycotoxin - Aflatoxin B1 (ug/kg)';
$out_metric_list['01EDPTB0J14D79E5FDVRVM290N'] = 'Mycotoxin - Aflatoxin B2 (ug/kg)';
$out_metric_list['01EDPTB1FYFSFB0NXEFCRF5E72'] = 'Mycotoxin - Aflatoxin G1 (ug/kg)';
$out_metric_list['01EDPTB5B0PPN4WPYRMZD09BWY'] = 'Mycotoxin - Aflatoxin G2 (ug/kg)';
$out_metric_list['018NY6XC00LMK15566W1G0ZH5X'] = 'Mycotoxin - Ochratoxin A (ug/kg)';     // (ppb)


// Review WAC and CCRS Data 314-55-101
$out_metric_list['018NY6XC00LMHF4266DN94JPPX'] = 'Moisture Analysis - Water Activity (aw)';
// $out_metric_list['018NY6XC00LM0PXPG4592M8J14'] = 'Moisture Analysis - Moisture Content (%)';


// @todo Handle these booleans properly
$out_metric_list['018NY6XC00SV2M1J801BRSMA5F'] = 'Foreign Matter - IHE (each)'; // One insect fragment, one hair, or one mammalian excreta in sample.
$out_metric_list['018NY6XC00LMQAZZSDXPYH62SS'] = 'Foreign Matter - Stems (%)';
$out_metric_list['018NY6XC00LMA50497RDC53DB5'] = 'Foreign Matter - Seeds or Other (%)';


$out_metric_list['018NY6XC00LM638QCGB50ZKYKJ'] = 'Microbiological - BTGN (CFU/g)';
$out_metric_list['018NY6XC00LMS96WE6KHKNP52T'] = 'Microbiological - Salmonella (CFU/g)';
$out_metric_list['018NY6XC00LM7S8H2RT4K4GYME'] = 'Microbiological - STEC (CFU/g)';


// Heavy Metals
$out_metric_list['018NY6XC00LM4E4T6EPA7WPHDK'] = 'Heavy Metal - Arsenic (ug/g)';  // Heavy Metal - Arsenic (ppm)';
$out_metric_list['018NY6XC00LMGNGNEW1XMNRS8S'] = 'Heavy Metal - Cadmium (ug/g)';  // Heavy Metal - Cadmium (ppm)';
$out_metric_list['018NY6XC00LM6YBP4J5ASBWVNR'] = 'Heavy Metal - Lead (ug/g)';     // Heavy Metal - Lead (ppm)';
$out_metric_list['018NY6XC00LM10ZPAN42R490W3'] = 'Heavy Metal - Mercury (ug/g)';  // Heavy Metal - Mercury (ppm)';


// Solvent
$out_metric_list['018NY6XC00LM9HW5DZGD5KR55G'] = 'Residual Solvent - Acetone (ug/g)'; // Residual Solvent - Acetone (ppm)';
$out_metric_list['018NY6XC00LMT7VRMWMXMH59Y5'] = 'Residual Solvent - Benzene (ug/g)'; // Residual Solvent - Benzene (ppm)';
$out_metric_list['018NY6XC00LMSTBW55VFR0QG56'] = 'Residual Solvent - Total Butanes (ug/g)'; // Residual Solvent - Butanes (ppm)';
$out_metric_list['01FQFG9EJC2Z0EFM33WDMPDEV7'] = 'Residual Solvent - n-Butane (ug/g)';
$out_metric_list['018NY6XC00LMPTJEH2SHH45155'] = 'Residual Solvent - Isobutane (ug/g)';
$out_metric_list['018NY6XC00LM6QHYX79AXVVRH1'] = 'Residual Solvent - Cyclohexane (ug/g)'; // Residual Solvent - Cyclohexane (ppm)';
$out_metric_list['018NY6XC00LMAA8QXZ8CD0QQMW'] = 'Residual Solvent - Chloroform (ug/g)'; // Residual Solvent - Chloroform (ppm)';
$out_metric_list['018NY6XC00LM7D70QAKTR6WM30'] = 'Residual Solvent - Dichloromethane (ug/g)'; // Residual Solvent - Dichloromethane (ppm)';
$out_metric_list['018NY6XC00LMTBZ6MS529BRMDY'] = 'Residual Solvent - Ethanol (ug/g)'; // Residual Solvent - Ethanol (ppm)
$out_metric_list['018NY6XC00LMH5RPYTRCS5BQKJ'] = 'Residual Solvent - Ethyl Acetate (ug/g)'; // Residual Solvent - Ethyl Acetate (ppm)';
// $out_metric_list['018NY6XC00LM50MYZZY71MQ7BE'] = 'Residual Solvent - Heptanes (ug/g)'; // Residual Solvent - Heptanes (ppm)';
$out_metric_list['01FRK0CC1G0SMZT4RW3WR8QB9Z'] = 'Residual Solvent - Heptanes (ug/g)';
$out_metric_list['01FQFG9EJB2ZNCCGA3W92QF354'] = 'Residual Solvent - n-Heptane (ug/g)';
$out_metric_list['01FRK0CC1G0SMZT4RW3WR8QBA0'] = 'Residual Solvent - Total Hexanes (ug/g)'; // Residual Solvent - Hexanes (ppm)';
$out_metric_list['01FQFG9EJB2ZNCCGA3W92QF35K'] = 'Residual Solvent - n-Hexane (ug/g)';
// Need COnfirmation on thse, search 'ack -i methyl' in api/etc
// Residual Solvent - 2-Methylpentane (ug/g)
// Residual Solvent - 3-Methylpentane (ug/g)
// Residual Solvent - 2,2-Dimethylbutane (ug/g)
// Residual Solvent - 2,3-Dimethylbutane (ug/g)
$out_metric_list['018NY6XC00LM3CKFFVVSNJYGCH'] = 'Residual Solvent - Isopropanol (ug/g)'; // Residual Solvent - Isopropanol (ppm)';
$out_metric_list['018NY6XC00LMYC6MEJARSBRGW8'] = 'Residual Solvent - Methanol (ug/g)'; // Residual Solvent - Methanol (ppm)';
$out_metric_list['01FRK0CC1G0SMZT4RW3WR8QBA1'] = 'Residual Solvent - Total Pentanes (ug/g)'; // Residual Solvent - Pentanes (ppm)';
$out_metric_list['01FQFG9EJC2Z0EFM33WDMPDEV8'] = 'Residual Solvent - n-Pentane (ug/g)';
$out_metric_list['018NY6XC00LMVSE5MJFPQEDRSC'] = 'Residual Solvent - Isopentane (ug/g)';
$out_metric_list['01EDPV85CT7SX8BB5BP3VCWJCK'] = 'Residual Solvent - Neopentane (ug/g)';
$out_metric_list['018NY6XC00LMCK0YZ3T76QWMNF'] = 'Residual Solvent - Propane (ug/g)'; // Residual Solvent - Propanes (ppm)';
$out_metric_list['018NY6XC00LMGG9JR3SM0MEDGQ'] = 'Residual Solvent - Toluene (ug/g)'; // Residual Solvent - Toluene (ppm)';
$out_metric_list['018NY6XC00LMW1FC0RA14FZ3PF'] = 'Residual Solvent - Total Xylenes (ug/g)'; // Residual Solvent - Xylenes (ppm)';
$out_metric_list['01FQFG9EJB2ZNCCGA3W92QF355'] = 'Residual Solvent - ortho-Xylene (ug/g)';
// Questions about these two
// CAS calls this meta-para-xylene ?
// 01FQFG9EJB2ZNCCGA3W92QF356.yaml // Residual Solvent - meta-Xylene (ug/g)
// Residual Solvent - para-Xylene (ug/g)


// Pesticide
// This one might be the wrong name?
$out_metric_list['01FRK0CC1G0SMZT4RW3WR8QB9T'] = 'Pesticide - Total Abamectin (ug/g)'; // Pesticide - Abamectin (ppm)';
$out_metric_list['018NY6XC00LMA5GGMB8GXE0SJW'] = 'Pesticide - Avermectin B1a (ug/g)';
$out_metric_list['018NY6XC00LMJ195SKA604Y6CC'] = 'Pesticide - Avermectin B1b (ug/g)';
$out_metric_list['018NY6XC00LMME2KAJD5CJZCFC'] = 'Pesticide - Acephate (ug/g)'; // Pesticide - Acephate (ppm)';
$out_metric_list['018NY6XC00LMB7TEPP64SS0VXD'] = 'Pesticide - Acequinocyl (ug/g)'; // Pesticide - Acequinocyl (ppm)';
$out_metric_list['018NY6XC00LMHPJYYDQT7FCM8P'] = 'Pesticide - Acetamiprid (ug/g)'; // Pesticide - Acetamiprid (ppm)';
$out_metric_list['018NY6XC00LME4KJM6Y8XP8WGA'] = 'Pesticide - Aldicarb (ug/g)'; // Pesticide - Aldicarb (ppm)';
$out_metric_list['018NY6XC00KKW13KEN6JWKZNJF'] = 'Pesticide - Azoxystrobin (ug/g)'; // Pesticide - Azoxystrobin (ppm)';
$out_metric_list['018NY6XC00LMKCE4E30P3R72SK'] = 'Pesticide - Bifenazate (ug/g)'; // Pesticide - Bifenazate (ppm)';
$out_metric_list['018NY6XC00LMPH4K88KC1PKJVJ'] = 'Pesticide - Bifenthrin (ug/g)'; // Pesticide - Bifenthrin (ppm)';
$out_metric_list['018NY6XC00LM3P767WQ0KSFARZ'] = 'Pesticide - Boscalid (ug/g)'; // Pesticide - Boscalid (ppm)';
$out_metric_list['018NY6XC00LMZP42VJGA642TEB'] = 'Pesticide - Carbaryl (ug/g)'; // Pesticide - Carbaryl (ppm)';
$out_metric_list['018NY6XC00LM7N4CCX5ZRVADDN'] = 'Pesticide - Carbofuran (ug/g)'; // Pesticide - Carbofuran (ppm)';
$out_metric_list['018NY6XC00LMKKEFRB9BJ8KP0P'] = 'Pesticide - Chlorantraniliprole (ug/g)'; // Pesticide - Chlorantraniliprole (ppm)';
$out_metric_list['018NY6XC00LMR0FZPVYAEJ8JET'] = 'Pesticide - Chlorfenapyr (ug/g)'; // Pesticide - Chlorfenapyr (ppm)';
$out_metric_list['018NY6XC00LMXCM3VG0XGR6KAH'] = 'Pesticide - Chlorpyrifos (ug/g)'; // Pesticide - Chlorpyrifos (ppm)';
$out_metric_list['018NY6XC00LM230ECKQSRFZ2BE'] = 'Pesticide - Clofentezine (ug/g)'; // Pesticide - Clofentizine (ppm)';
$out_metric_list['018NY6XC00LMCQDJ36Y13GX6W3'] = 'Pesticide - Cyfluthrin (ug/g)'; // Pesticide - Cyfluthrin (ppm)';
$out_metric_list['018NY6XC00LM7CX800BM0FSGJR'] = 'Pesticide - Cypermethrin (ug/g)'; // Pesticide - Cypermethrin (ppm)';
$out_metric_list['018NY6XC00LM8H6MET0WJ2YCV1'] = 'Pesticide - Daminozide (ug/g)'; // Pesticide - Daminozide (ppm)';
$out_metric_list['018NY6XC00LMXVVJP95SCEWYMJ'] = 'Pesticide - Dichlorvos (ug/g)'; // Pesticide - DDVP (dichlorvos) (ug/g)'; // Pesticide - DDVP (dichlorvos) (ppm)';
$out_metric_list['018NY6XC00LM1FYB8674M2X435'] = 'Pesticide - Diazinon (ug/g)'; // Pesticide - Diazinon (ppm)';
$out_metric_list['018NY6XC00LMMGQYZ01JNTDSZ1'] = 'Pesticide - Dimethoate (ug/g)'; // Pesticide - Dimethoate (ppm)';
$out_metric_list['018NY6XC00LMYBVS4P9WE8MT73'] = 'Pesticide - Ethoprophos (ug/g)'; // Pesticide - Ethoprophos (ppm)';
$out_metric_list['018NY6XC00LMJHEQ07C7YKJBFM'] = 'Pesticide - Etofenprox (ug/g)'; // Pesticide - Etofenprox (ppm)';
$out_metric_list['018NY6XC00LMNPCTGHS6PVWKS3'] = 'Pesticide - Etoxazole (ug/g)'; // Pesticide - Etoxazole (ppm)';
$out_metric_list['018NY6XC00LMGN496XNG04YCKA'] = 'Pesticide - Fenoxycarb (ug/g)'; // Pesticide - Fenoxycarb (ppm)';
$out_metric_list['018NY6XC00LM1NGMNDNYD3R0HE'] = 'Pesticide - Fenpyroximate (ug/g)'; // Pesticide - Fenpyroximate (ppm)';
$out_metric_list['018NY6XC00LM98WRGGCSFYYGVX'] = 'Pesticide - Fipronil (ug/g)'; // Pesticide - Fipronil (ppm)';
$out_metric_list['018NY6XC00LMZMT6NYFV6QM9JH'] = 'Pesticide - Flonicamid (ug/g)'; // Pesticide - Flonicamid (ppm)';
$out_metric_list['018NY6XC00LMZ91MVJB81J4JQT'] = 'Pesticide - Fludioxonil (ug/g)'; // Pesticide - Fludioxonil (ppm)';
$out_metric_list['018NY6XC00LMADJX0GMMS5MXVB'] = 'Pesticide - Hexythiazox (ug/g)'; // Pesticide - Hexythiazox (ppm)';
$out_metric_list['018NY6XC00LMX1R3RFFRFZS8T4'] = 'Pesticide - Imazalil (ug/g)'; // Pesticide - Imazalil (ppm)';
$out_metric_list['018NY6XC00LMR9Z32S7WHPBZP9'] = 'Pesticide - Imidacloprid (ug/g)'; // Pesticide - Imidacloprid (ppm)';
$out_metric_list['018NY6XC00LM4VRHKTYTJJRDPW'] = 'Pesticide - Kresoxim-methyl (ug/g)'; // Pesticide - Kresoxim-Methyl (ppm)';
$out_metric_list['018NY6XC00LMEN8F7VNXYV7HCS'] = 'Pesticide - Malathion (ug/g)'; // Pesticide - Malathion (ppm)';
$out_metric_list['018NY6XC00LMMFPYJ25XC5QTTQ'] = 'Pesticide - Metalaxyl (ug/g)'; // Pesticide - Metalaxyl (ppm)';
$out_metric_list['018NY6XC00LMC4048KGG4SR6WF'] = 'Pesticide - Methiocarb (ug/g)'; // Pesticide - Methiocarb (ppm)';
$out_metric_list['018NY6XC00LM7WBZ76X1E3T868'] = 'Pesticide - Methomyl (ug/g)'; // Pesticide - Methomyl (ppm)';
$out_metric_list['018NY6XC00LM4N6RPDAC97NM9V'] = 'Pesticide - Methyl parathion (ug/g)'; // Pesticide - Methyl parathion (ppm)';
$out_metric_list['018NY6XC00LMCQ7DX02S94RMM7'] = 'Pesticide - MGK-264 (ug/g)'; // Pesticide - MGK-264 (ppm)';
$out_metric_list['018NY6XC00LMN56HSR1X5ACEJB'] = 'Pesticide - Myclobutanil (ug/g)'; // Pesticide - Myclobutanil (ppm)';
$out_metric_list['018NY6XC00LMSCF0SS8VVJ9DE5'] = 'Pesticide - Naled (ug/g)'; // Pesticide - Naled (ppm)';
$out_metric_list['018NY6XC00LM83VNPJMHTKX5F0'] = 'Pesticide - Oxamyl (ug/g)'; // Pesticide - Oxamyl (ppm)';
$out_metric_list['018NY6XC00LMV3YF9F83621G84'] = 'Pesticide - Paclobutrazol (ug/g)'; // Pesticide - Paclobutrazol (ppm)';
$out_metric_list['018NY6XC00LM3ZJH23WAKV7JEB'] = 'Pesticide - Total Permethrins (ug/g)'; // Pesticide - Permethrins (ppm)';
$out_metric_list['018NY6XC00LMXSM3QAXV8HQD5F'] = 'Pesticide - cis-Permethrin (ug/g)';
$out_metric_list['018NY6XC00LMWKXB3PQ2STF5RS'] = 'Pesticide - trans-Permethrin (ug/g)';
$out_metric_list['018NY6XC00LMZ95MW0N3JPZ056'] = 'Pesticide - Phosmet (ug/g)'; // Pesticide - Phosmet (ppm)';
$out_metric_list['018NY6XC00LM6VF2D0V998AY9Q'] = 'Pesticide - Piperonyl butoxide (ug/g)'; // Pesticide - Piperonyl butoxide (ppm)';
$out_metric_list['018NY6XC00LMKX28NVG7PJT5WJ'] = 'Pesticide - Prallethrin (ug/g)'; // Pesticide - Prallethrin (ppm)';
$out_metric_list['018NY6XC00LM6T0NCQGXBCSNCS'] = 'Pesticide - Propiconazole (ug/g)'; // Pesticide - Propiconazole (ppm)';
$out_metric_list['018NY6XC00LMD2VKZ8FHZ3F3X8'] = 'Pesticide - Propoxur (ug/g)'; // Pesticide - Propoxur (ppm)';
$out_metric_list['01FRK0CC1G0SMZT4RW3WR8QB9W'] = 'Pesticide - Total Pyrethrins (ug/g)'; // Pesticide - Pyrethrins (ppm)';
$out_metric_list['018NY6XC00A4CC4C21KRAMGQE0'] = 'Pesticide - Pyrethrin I (ug/g)';
$out_metric_list['018NY6XC00FE5KW2ZCRY7ED5WG'] = 'Pesticide - Pyrethrin II (ug/g)';
$out_metric_list['018NY6XC00LMH66XZD64ZDTHZW'] = 'Pesticide - Pyridaben (ug/g)'; // Pesticide - Pyridaben (ppm)';
$out_metric_list['018NY6XC00LMKF9QEXJGS0HGHM'] = 'Pesticide - Total Spinosad (ug/g)'; // Pesticide - Spinosad (ppm)';
$out_metric_list['018NY6XC00LMMYVTVKPR0V8C0F'] = 'Pesticide - Spinosyn A (ug/g)';
$out_metric_list['018NY6XC00LMPNZXG9Z9YNFFX9'] = 'Pesticide - Spinosyn D (ug/g)';
$out_metric_list['018NY6XC00LMT9BF2M636RZBZX'] = 'Pesticide - Spiromesifen (ug/g)'; // Pesticide - Spiromesifen (ppm)';
$out_metric_list['018NY6XC00LMWDVDHEYRS6058S'] = 'Pesticide - Spirotetramat (ug/g)'; // Pesticide - Spirotetramat (ppm)';
$out_metric_list['018NY6XC00LMQ6AM5TE0FYPN2R'] = 'Pesticide - Spiroxamine (ug/g)'; // Pesticide - Spiroxamine (ppm)';
$out_metric_list['018NY6XC00LMT8QJD3BG6CNXA8'] = 'Pesticide - Tebuconazole (ug/g)'; // Pesticide - Tebuconazole (ppm)';
$out_metric_list['018NY6XC00LMMFQB5HBHJBQ9BS'] = 'Pesticide - Thiacloprid (ug/g)'; // Pesticide - Thiacloprid (ppm)';
$out_metric_list['018NY6XC00LMCH7YXS32M4PNZF'] = 'Pesticide - Thiamethoxam (ug/g)'; // Pesticide - Thiamethoxam (ppm)';
$out_metric_list['018NY6XC00LMRG0A40VCNVW3YX'] = 'Pesticide - Trifloxystrobin (ug/g)'; // Pesticide - Trifloxystrobin (ppm)';


// Build Output
$csv_output = [];
foreach ($out_metric_list as $mk0 => $mn0) {

	$lrm = $data['Lab_Result_Metric_list'][$mk0];
	if (empty($lrm['id'])) {
		continue;
	}

	$rec = [
		$data['Source_License']['code'], // License Owner
		$data['Lot']['guid'],
		$data['License_Laboratory']['code'], // Code of the Laboratory
		'',
		$mn0, // trim(sprintf('%s %s', $lrm['name'], $lrm['uom'])),
		$dtA->format('Y-m-d'), // Test Date
		_ccrs_qom_uom_format($lrm),
		$lrm['id'],
		$csv_config['lab_name'],
		$dt0->format('Y-m-d'),
		'',
		'',
		'INSERT',
	];

	switch ($lrm['stat']) {
		case Lab_Result::STAT_PASS:
			$rec[3] = 'PASS';
			break;
		case Lab_Result::STAT_FAIL:
			$rec[3] = 'FAIL';
			break;
	}

	$csv_output[] = $rec;

}

$csv_header = [
	'LicenseNumber',
	'InventoryExternalIdentifier',
	'LabLicenseNumber',
	'LabTestStatus',
	'TestName',
	'TestDate',
	'TestValue',
	'ExternalIdentifier',
	'CreatedBy',
	'CreatedDate',
	'UpdatedBy',
	'UpdatedDate',
	'Operation'
];

$out_handle = fopen('php://output', 'a');
CCRS::fputcsv_stupidly($out_handle, explode(',', sprintf('SubmittedBy,%s,,,,,,,,,,,', $csv_config['lab_name'])));
CCRS::fputcsv_stupidly($out_handle, explode(',', sprintf('SubmittedDate,%s,,,,,,,,,,,', date('m/d/Y'))));
CCRS::fputcsv_stupidly($out_handle, explode(',', sprintf('NumberRecords,%d,,,,,,,,,,,', count($csv_output))));
CCRS::fputcsv_stupidly($out_handle, $csv_header);

foreach ($csv_output as $row) {
	CCRS::fputcsv_stupidly($out_handle, $row);
}

/**
 * UOM Helper
 * Since CCRS can't handle things like N/A, N/D or N/T
 */
function _ccrs_qom_uom_format($lrm)
{
	// In OpenTHC BOOL=False => Fail and BOOL=True => Pass
	// In CCRS for Foreign Matter 0 => PASS and >0 => Fail
	switch ($lrm['lab_metric_id']) {
	case '018NY6XC00SV2M1J801BRSMA5F':
	case '018NY6XC00LMQAZZSDXPYH62SS':
	case '018NY6XC00LMA50497RDC53DB5':
		switch ($lrm['qom']) {
			case 0:
				$lrm['qom'] = 1; // 'Fail';
				break;
			case 1:
				$lrm['qom'] = 0; // 'Pass';
				break;
		}
	}

	$ret = sprintf('%0.2f', $lrm['qom']);

	// Promote LOQ-LB (Level of Quantization Lower Bound)
	// @todo this code should be moved up-stream where maybe Lab_Result->getMetricsDatabase() does something?
	$loq = floatval($lrm['loq-lb'] ?: $lrm['meta']['loq-lb'] ?: $lrm['lab_metric_meta']['loq-lb'] ?: $lrm['lab_metric_meta']['loq']);
	if ($loq > 0) {
		if ($lrm['qom'] <= $loq) {
			$ret = sprintf('<%0.2f', $loq);
		}
	}

	return $ret;

}
