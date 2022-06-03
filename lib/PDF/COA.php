<?php
/**
 * View Data in OpenTHC Style COA PDF
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\PDF;

class COA extends \App\PDF\Base
{
	// It's the Font Size + 2, Pre-Calculated Helpers
	const FS_08 = 9 / 72;
	const FS_09 = 10 / 72;
	const FS_10 = 12 / 72;
	const FS_12 = 14 / 72;
	const FS_14 = 16 / 72;
	const FS_16 = 18 / 72;

	private $_data = [];

	function setData($data)
	{
		$this->_data = $data;
	}

	/**
	 *
	 */
	public function Header() : void
	{
		// Lines
		$this->line(0, 11/3, 1/4, 11/3); // 1/3 Fold Lines
		$this->line(0, (11/3) * 2, 1/4, (11/3) * 2);
		$x = 0;
		$y = 10;
		$w = 1/4;
		$this->line($x, $y, $w, $y);

		$this->setXY(0.5, 0.5);

		// Lab Logo
		$i = 'https://cdn.openthc.com/img/icon.png';
		if ( ! empty($this->_data['License_Laboratory']['icon'])) {
			$i = $this->_data['License_Laboratory']['icon'];
		}
		$x = 6;
		$y = 0.4;
		$w = 2;
		$h = .75;
		$this->image($i, $x, $y, $w, $h, $type='', $link='', $align='', $resize=true, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox='RT');


		// Document Title
		$x = 2.5;
		$y = 0.5;
		$this->setXY($x, $y);
		$this->setFont('', 'B', 16);
		$this->cell(3.5, self::FS_16, 'Certificate of Analysis', 0, 0, 'C');

		$y += self::FS_16;
		$this->setXY($x, $y);
		// $this->cell(3.5, self::FS_12, $this->_data['Lab_Sample']['name'], 0, 0, 'C');
		$this->setFont('', '', 12);
		$this->cell(3.5, self::FS_12, $this->_data['Lab_Result']['guid'], 0, 0, 'C');

		$y += self::FS_12;
		$this->setXY($x, $y);
		$dt = new \DateTime($this->_data['Lab_Result']['created_at']);
		// $dt->setTimezone(new DateTimezone($_SESSION['tz']));
		$this->cell(3.25, self::FS_12, sprintf('Date: %s', $dt->format('m/d/Y')), 0, 0, 'C');
		// $y += self::FS_12;


		// Laboratory Address
		$this->setFont('', '', 10);

		// Header Laboratory License Information
		$x = 0.5;
		$y = 0.5;

		$this->setXY($x, $y);
		$this->cell(1.5, self::FS_10, $this->_data['License_Laboratory']['name']); // $_SESSION['License']['id']);
		$y += self::FS_10;

		$this->setXY($x, $y);
		$this->cell(1.5, self::FS_10, $this->_data['License_Laboratory']['address_line_1']);
		$y += self::FS_10;

		$this->setXY($x, $y);
		$this->cell(1.5, self::FS_10, $this->_data['License_Laboratory']['address_line_2']);
		$y += self::FS_10;

		$this->setXY($x, $y);
		$this->cell(1.5, self::FS_10, $this->_data['License_Laboratory']['phone']);
		$y += self::FS_10;

		$this->setXY($x, $y);
		$this->cell(1.5, self::FS_10, $this->_data['License_Laboratory']['email']);
		$y += self::FS_10;
		$y += (5/72);

		$x  = 0.5;
		$x1 = 8;
		$y1 = $y;
		$this->line($x, $y, $x1, $y1);

		$this->setXY($y, $y + self::FS_14);

		$this->header_page_one();

	}

	/**
	 *
	 */
	public function header_page_one() : void
	{
		$p = $this->getPage();
		if (1 !== $p) {
			return;
		}

		// Client Logo Image
		$x = 0.50;
		$y = 1.50;
		$this->rect($x, $y, 1, 1);
		$this->setXY($x, $y);
		$this->cell(1, self::FS_12, 'LOGO');
		// $this->image();

		// Sample/Product Picture
		$x = 7.00;
		$y = 1.50;
		$this->rect($x, $y, 1, 1);
		$this->setXY($x, $y);
		$this->cell(1, self::FS_12, 'SAMP');
		// $this->image();

		// Client Information
		$x = 1.75;
		$y = 1.43;

		$this->setFont('', 'B', 14);

		$this->setXY($x, $y);
		$this->cell(3.25, self::FS_14, $this->_data['License_Source']['name']);
		$y += self::FS_14;

		$this->setFont('', '', 12);

		$this->setXY($x, $y);
		$this->cell(3.25, self::FS_12, sprintf('Sample: %s', $this->_data['Lab_Sample']['name']));
		$y += self::FS_12;

		$this->setXY($x, $y);
		$this->cell(3.25, self::FS_12, sprintf('Product: %s', $this->_data['Product']['name']));
		$y += self::FS_12;

		$this->setXY($x, $y);
		$this->cell(3.25, self::FS_12, sprintf('Product Type: %s', $this->_data['Product_Type']['name']));
		$y += self::FS_12;

		$this->setXY($x, $y);
		$this->cell(3.25, self::FS_12, sprintf('Quantity: %s', $this->_data['Lab_Sample']['qty']));
		$y += self::FS_12;

		$this->setXY(0.50, 3.75);

	}

	/**
	 * @deprecated
	 */
	public function header_client_info()
	{
		// Client Information
		$x = 0.5;
		$y = 1.75;
		// var_dump($y); exit;

		// Client Information
		$this->setXY($x, $y - self::FS_14);
		$this->setFont('', 'B', 14);
		$this->cell(3.25, self::FS_14, sprintf('Client: %s', $this->_data['License_Source']['name']));
		// $y += self::FS_14;

		$this->setFont('', '', 12);

		// $this->setXY($x, $y);
		// $this->cell(1.5, self::FS_12, $this->_data['License_Name']);
		// $y += self::FS_12;

		$this->setXY($x, $y);
		$this->cell(1.5, self::FS_12, $this->_data['Address_Line_1']);
		$y += self::FS_12;

		$this->setXY($x, $y);
		$this->cell(1.5, self::FS_12, $this->_data['Address_Line_2']);

	}

	/**
	 *
	 */
	public function footer()
	{
		// Disclaimer Text
		$this->setXY(0.5, 9.5);
		$this->setFont('', '', 9);
		$this->multicell(7.5, self::FS_10, $this->_data['footer_text'], $opt['border'], $opt['align'], $opt['fill'], null, 0.5, 10);

	}

	/**
	 *
	 */
	function draw_metric_table($metric_list)
	{
		foreach ($metric_list as $m) {

			$lrm = $this->_data['Lab_Result_Metric_list'][ $m['id'] ];

			$x = $this->getX();
			$y = $this->getY();

			$this->setXY($x, $y);
			$this->cell(2.5, self::FS_10, $m['name'], 0, 0, 'L');

			$this->setXY($x + 1.875, $y);
			// If the Metric Has an Action Limit
			// $this->cell(0.5, self::FS_10, 'A/L', 0, 0, 'R');

			$this->draw_metric_qom($lrm);
			$this->setXY($x + 2.5, $y);
			$this->cell(0.5, self::FS_10, _qom_nice($lrm['qom']), 0, 0, 'R');

			$this->setXY($x + 3, $y);
			$this->cell(0.5, self::FS_10, \App\UOM::nice($lrm['uom']), 0, 0, 'L');

			$y += self::FS_10;

			// Advance to Next Line
			$this->setXY($x, $y);
			if ($this->checkPageBreak(self::FS_10)) {
				// We're on the new page
				// Am I on the new Page?
				// $this->setXY($x, 1.75);
				// $this->cell(0.5, self::FS_10, 'FDSFDS');
			}
			// 	// $this->setXY($x, $y + self::FS_10);
			// 	// $this->setXY();
			// }

		}

	}

	/**
	 * Draw the Metric for the Specific LRM
	 */
	function draw_metric_qom($x, $y, $lrm)
	{

		$this->setXY($x, $y);
		$this->cell(2.5, self::FS_10, $lrm['name'], 0, 0, 'L');

		$pfl = $lrm['pfl']; // Pass / Fail Limit
		$uom = $lrm['uom'];
		$qom = $lrm['qom'];

		// $this->setXY($x + 1.875, $y);
		// $this->cell(0.5, self::FS_10, 'A/L', 0, 0, 'R');

		switch ($uom) {
			case 'bool':
				$txt = $qom ? 'Pass' : 'Fail';
				$this->setXY($x + 2.5, $y);
				$this->cell(0.5, self::FS_10, $txt, 0, 0, 'R');
				return(0);
				break;
		}

		$this->setXY($x + 2.5, $y);
		$this->cell(0.5, self::FS_10, _qom_nice($lrm['qom']), 0, 0, 'R');

		$this->setXY($x + 3, $y);
		$this->cell(0.5, self::FS_10, \App\UOM::nice($lrm['uom']), 0, 0, 'L');

		// switch ($lrm['id']) {
		// 	case '':

		// }

		// switch ($lrm['qom']) {
		// 	case -1:
		// 	case -2:
		// 	case -3:
		// }

		// $this->cell(0.5, self::FS_10, $txt, 0, 0, 'R');

	}

	/**
	 * Draw the Two Column Metrics, Alphabetically, Across then Down
	 */
	function draw_metric_table_2_col_a_then_d($metric_name, $metric_list)
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
		$max = count($metric_list);
		$metric_list_key_list = array_keys($metric_list);

		for ($idx=0; $idx<$max; $idx+=2) {

			$x = 0.50; // $this->getX();
			$y = $this->getY();

			$keyA = $metric_list_key_list[$idx];
			$keyB = $metric_list_key_list[$idx + 1];

			$lrmA = $this->_data['Lab_Result_Metric_list'][ $keyA ];
			$lrmB = $this->_data['Lab_Result_Metric_list'][ $keyB ];

			// Column 1
			$this->draw_metric_qom($x, $y, $lrmA);
			// $this->setXY($x, $y);
			// $this->cell(2.5, self::FS_10, $lrmA['name'], 0, 0, 'L');

			// $this->setXY($x + 1.875, $y);
			// // $this->cell(0.5, self::FS_10, 'A/L', 0, 0, 'R');

			// $this->setXY($x + 2.5, $y);
			// $this->cell(0.5, self::FS_10, _qom_nice($lrmA['qom']), 0, 0, 'R');

			// $this->setXY($x + 3, $y);
			// $this->cell(0.5, self::FS_10, \App\UOM::nice($lrmA['uom']), 0, 0, 'L');

			// Column 2
			$x = 4.25;
			if ( ! empty($lrmB)) {

				$this->draw_metric_qom($x, $y, $lrmB);
				// $this->setXY($x, $y);
				// $this->cell(2.5, self::FS_10, $lrmB['name'], 0, 0, 'L');

				// $this->setXY($x + 1.875, $y);
				// // $this->cell(0.5, self::FS_10, 'A/L', 0, 0, 'R');

				// $this->setXY($x + 2.5, $y);
				// $this->cell(0.5, self::FS_10, _qom_nice($lrmB['qom']), 0, 0, 'R');

				// $this->setXY($x + 3, $y);
				// $this->cell(0.5, self::FS_10, \App\UOM::nice($lrmB['uom']), 0, 0, 'L');

			}

			$y += self::FS_10;

			// Advance to Next Line
			$this->setXY($x, $y);
			if ($this->checkPageBreak(self::FS_10 * 2)) {

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
