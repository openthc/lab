<?php
/**
 * View Data in OpenTHC Style COA PDF
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

use OpenTHC\Lab\PDF\COA;


$pdf = new COA();
$pdf->setData($data);
$pdf->setTitle(sprintf('COA %s', $data['Lab_Result']['id']));
$pdf->setSubject(sprintf('COA %s', $data['Lab_Result']['id']));
// $pdf->setKeywords($this->name);
$pdf->setFont('freesans');

$pdf->addPage();

$pdf->setX(0.5);
$pdf->setY(2.75);

// General Header Block
$x = 0.5;
$y = 2.75;

$pdf->setXY($x, $y);
$pdf->draw_metric_table_2_col_a_then_d('General', $data['Lab_Result_Section_Metric_list']['018NY6XC00LMT0BY5GND653C0C']['metric_list']);


// Cannabinoids
$x = 0.5;
$y = $pdf->getY() + COA::FS_14;
$pdf->setXY($x, $y);
// $pdf->setFont('freesans', 'B', 14);
// $pdf->cell(7.5, COA::FS_14, );
// $pdf->setXY(0.5, $y + COA::FS_14);
// $pdf->setFont('freesans', '', 10);
// $pdf->draw_metric_table($data['Lab_Result_Section_Metric_list']['018NY6XC00LMT0HRHFRZGY72C7']['metric_list']);
$metric_list = $data['Lab_Result_Section_Metric_list']['018NY6XC00LMT0HRHFRZGY72C7']['metric_list'];
// $metric_list_count = count($metric_list);
// $metric_listA_count = ceil($metric_list_count / 2);
// $metric_listB_count = $metric_list_count - $metric_listA_count;
// $metric_listA = array_slice($metric_list, 0, $metric_listA_count);
// $metric_listB = array_slice($metric_list, $metric_listB_count + 1);
// $pdf->draw_metric_table_2_col_a_then_d('Cannabinoids', $metric_listA, $metric_listB);
$pdf->draw_metric_table_2_col_a_then_d('Cannabinoids', $metric_list);


// Terpenes
$x = 0.50;
$y = $pdf->getY(); // 1.75;
$pdf->setXY($x, $y);
$metric_list = $data['Lab_Result_Section_Metric_list']['018NY6XC00LMT07DPNKHQV2GRS']['metric_list'];
$pdf->draw_metric_table_2_col_a_then_d('Terpenes', $metric_list);


// Metals
$x = 0.5;
$y = $pdf->getY() + COA::FS_14;
$pdf->setXY($x, $y);
$pdf->draw_metric_table_2_col_a_then_d('Metals', $data['Lab_Result_Section_Metric_list']['018NY6XC00LMT0V6XE7P0BHBCR']['metric_list']);


// Microbes
$x = 0.5;
$y = $pdf->getY() + COA::FS_14;
$pdf->setXY($x, $y);
$pdf->draw_metric_table_2_col_a_then_d('Microbes', $data['Lab_Result_Section_Metric_list']['018NY6XC00LMT0B7NMK7RGYAMN']['metric_list']);


// Mycotoxins
$x = 0.50;
$y = $pdf->getY() + COA::FS_14;
$pdf->setXY($x, $y);
$pdf->draw_metric_table_2_col_a_then_d('Mycotoxins', $data['Lab_Result_Section_Metric_list']['018NY6XC00LMT0GDBPF0V9B71Z']['metric_list']);


// Solvents
$x = 0.50;
$y = $pdf->getY();
$y = $y + COA::FS_14;
$pdf->setXY($x, $y);
$pdf->draw_metric_table_2_col_a_then_d('Solvents', $data['Lab_Result_Section_Metric_list']['018NY6XC00LMT0AQAMJEDSD0NW']['metric_list']);


// Pesticides
$x = 0.50;
$y = $pdf->getY();
$y += COA::FS_14;
$pdf->setXY($x, $y);
$metric_list = $data['Lab_Result_Section_Metric_list']['018NY6XC00LMT09ZG05C2NE7KX']['metric_list'];
// $metric_list_count = count($metric_list);
// $metric_listA_count = ceil($metric_list_count / 2);
// $metric_listB_count = $metric_list_count - $metric_listA_count;
// $metric_listA = array_slice($metric_list, 0, $metric_listA_count);
// $metric_listB = array_slice($metric_list, $metric_listB_count + 1);
// $pdf->draw_metric_table_2_col('Pesticides', $metric_listA, $metric_listB);
$pdf->draw_metric_table_2_col_a_then_d('Pesticides', $metric_list);
// $pdf->setXY($x, $y);
// $pdf->draw_metric_table($metric_listA);


// $x = 4.25;
// $pdf->setXY($x, $y);
// $pdf->draw_metric_table($metric_listB);

// More disclaimer text

$pdf->output(sprintf('coa-%s.pdf', $data['Lab_Result']['guid']), 'I');


function _want_metric_type($pt_want, $pt_matrix)
{
	// $mt_show = false;
	// $pt_want = $data['Product_Type']['id'];
	// $pt_matrix = $data['lab_metric_type_list']['018NY6XC00LMT0BY5GND653C0C']['meta']['product-type-matrix'];
	if (empty($pt_matrix)) {
		return true;
	} else {
		// Lookup Something?
		foreach ($pt_matrix as $pt_ulid => $pt_show) {
			if ($pt_ulid == $pt_want) {
				if ($pt_show) {
					return true;
				}
			}
		}
	}

	return false;

}
