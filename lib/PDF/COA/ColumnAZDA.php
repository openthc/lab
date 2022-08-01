<?php
/**
 * View Data in OpenTHC Style COA PDF
 * Draws the Metrics in Alphabetical
 * Fills Down First, then Across
 * eg: Column 1 is A-M; Column 2 is N-Z
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

/*
// $metric_list_count = count($metric_list);
// $metric_listA_count = ceil($metric_list_count / 2);
// $metric_listB_count = $metric_list_count - $metric_listA_count;
// $metric_listA = array_slice($metric_list, 0, $metric_listA_count);
// $metric_listB = array_slice($metric_list, $metric_listB_count + 1);
// $pdf->draw_metric_table_2_col_a_then_d('Cannabinoids', $metric_listA, $metric_listB);
*/

namespace OpenTHC\Lab\PDF\COA;

use OpenTHC\Lab\UOM;

class ColumnAZDA extends \OpenTHC\Lab\PDF\COA
{
	/**
	 *
	 */
	function draw_metric_table_2_col($metric_name, $metric_listA, $metric_listB)
	{
		$x = 0.50;
		$y = $this->getY();

		// Section Header
		$this->setXY($x, $y);
		$this->setFont('', 'B', 14);
		$this->cell(7.5, App_PDF_COA::FS_14, $metric_name);
		$this->setXY($x, $y + App_PDF_COA::FS_14);
		$this->setFont('', '', 10);


		$idx = 0;
		$max = max(count($metric_listA), count($metric_listB));
		$metric_listA_key_list = array_keys($metric_listA);
		$metric_listB_key_list = array_keys($metric_listB);

		for ($idx=0; $idx<$max; $idx++) {

			$x = 0.50; // $this->getX();
			$y = $this->getY();

			$keyA = $metric_listA_key_list[$idx];
			$keyB = $metric_listB_key_list[$idx];

			// Column 1
			// $m = $metric_listA[$idx];
			$lrmA = $this->_data['Lab_Result_Metric_list'][ $keyA ];
			$lrmB = $this->_data['Lab_Result_Metric_list'][ $keyB ];

			$this->setXY($x, $y);
			$this->cell(2.5, self::FS_10, $lrmA['name'], 0, 0, 'L');

			$this->setXY($x + 1.875, $y);
			$this->cell(0.5, self::FS_10, 'A/L', 0, 0, 'R');

			$this->setXY($x + 2.5, $y);
			$this->cell(0.5, self::FS_10, _qom_nice($lrmA['qom']), 0, 0, 'R');

			$this->setXY($x + 3, $y);
			$this->cell(0.5, self::FS_10, UOM::nice($lrmA['uom']), 0, 0, 'L');

			// Column 2
			$x = 4.25;
			if ( ! empty($lrmB)) {

				$this->setXY($x, $y);
				$this->cell(2.5, self::FS_10, $lrmB['name'], 0, 0, 'L');

				$this->setXY($x + 1.875, $y);
				$this->cell(0.5, self::FS_10, 'A/L', 0, 0, 'R');

				$this->setXY($x + 2.5, $y);
				$this->cell(0.5, self::FS_10, _qom_nice($lrmB['qom']), 0, 0, 'R');

				$this->setXY($x + 3, $y);
				$this->cell(0.5, self::FS_10, UOM::nice($lrmB['uom']), 0, 0, 'L');

			}

			$y += self::FS_10;

			// Advance to Next Line
			$this->setXY($x, $y);
			if ($this->checkPageBreak(self::FS_10)) {

				// We're on the new page
				$x = 0.50;
				$y = 1.75;
				$this->setXY($x, $y);

				// Table Header
				$this->setFont('', 'B', 14);
				$this->cell(7.5, App_PDF_COA::FS_14, sprintf('%s (continued)', $metric_name));
				$this->setXY($x, $y + App_PDF_COA::FS_14);
				$this->setFont('', '', 10);

			}

		}

	}

}
