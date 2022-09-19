<?php
/**
 * View Data in OpenTHC Style COA PDF
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\PDF;

use Edoceo\Radix\DB\SQL;

use OpenTHC\Lab\UOM;

class COA extends \OpenTHC\Lab\PDF\Base
{
	// It's the Font Size + 2, Pre-Calculated Helpers
	const FS_08 = 9 / 72;
	const FS_09 = 10 / 72;
	const FS_10 = 12 / 72;
	const FS_12 = 14 / 72;
	const FS_14 = 16 / 72;
	const FS_16 = 18 / 72;

	private $_data = [];
	private $_dbc_lab_data = null;

	protected $c1w = 2.125;
	protected $c2w = 0.500;
	protected $c3w = 0.500;
	protected $c4w = 0.375;

	/**
	 * Set the Huge Data Blob
	 */
	function setData($data)
	{
		$this->_data = $data;
		$this->_dbc_lab_data = new SQL('sqlite::memory:');
		$this->_dbc_lab_data->query('CREATE TABLE lab_report_data (id, lab_metric_type_id, name, sort, qom, uom, max)');
	}

	/**
	 * Add a Lab Result Metric
	 */
	function addLabResultMetric($lm)
	{

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
		$this->setFont('freesans', 'B', 16);
		$this->cell(3.5, self::FS_16, 'Certificate of Analysis', 0, 0, 'C');

		$y += self::FS_16;
		$this->setXY($x, $y);
		// $this->cell(3.5, self::FS_12, $this->_data['Lab_Sample']['name'], 0, 0, 'C');
		$this->setFont('freesans', '', 12);
		$this->cell(3.5, self::FS_12, $this->_data['Lab_Result']['guid'], 0, 0, 'C');


		$y += self::FS_12;
		// if ($this->_data['Lab_Report']['stat'] == 200) {
			$this->setXY($x, $y);
			$this->setFont('freesans', 'B', 12);
			$this->setTextColor(0x00, 0x99, 0x00);
			$this->cell(3.25, self::FS_12, 'Passed', 0, 0, 'C');
			$this->setFont('freesans', '', 12);
			$this->setTextColor(0x00, 0x00, 0x00);
		// }

		// Received
		// Reported
		// Expires

		$y += (16 / 72);
		$this->setXY($x, $y);
		$this->setFont('freesans', '', 10);
		$dt = new \DateTime($this->_data['Lab_Result']['approved_at']);
		$dt->setTimezone(new \DateTimezone($_SESSION['tz']));
		$this->cell(1.75, self::FS_10, sprintf('Approved: %s', $dt->format('m/d/Y')), 0, 0, 'C');

		// $y += self::FS_10;
		$this->setXY(4.25, $y);
		$dtE = new \DateTime($this->_data['Lab_Result']['expires_at']);
		$dtE->setTimezone(new \DateTimezone($_SESSION['tz']));
		$this->cell(1.75, self::FS_10, sprintf('Expires: %s', $dtE->format('m/d/Y')), 0, 0, 'C');

		// $dt = new \DateTime($this->_data['Lab_Result']['created_at']);
		// $dt->setTimezone(new DateTimezone($_SESSION['tz']));

		// $this->cell(3.25, self::FS_12, sprintf('Date Reported: %s', $dt->format('m/d/Y')), 0, 0, 'C');

		// Laboratory Address
		$this->setFont('freesans', '', 10);

		// Header Laboratory License Information
		$x = 0.5;
		$y = 0.5;

		$this->setXY($x, $y);
		$this->cell(1.5, self::FS_10, $this->_data['License_Laboratory']['name']);
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
		$url_logo = 'https://cdn.openthc.com/img/icon.png';
		$url_logo = $this->_data['License_Source']['icon'];

		$x = 0.50;
		$y = 1.50;
		$w = 1;
		$h = 1;
		// $this->rect($x, $y, $w, $h);
		// $this->setXY($x, $y);
		// $this->cell(1, self::FS_12, 'LOGO');
		$this->image($url_logo, $x, $y, $w, $h, $type='', $link='', $align='', $resize=true, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox='RT');

		// Sample/Product Picture
		$img_file = $this->_data['Lab_Sample']['img_file'];
		if ($img_file) {
			$x = 7.00;
			$y = 1.50;
			$w = 1;
			$h = 1;
			$this->rect($x, $y, $w, $h);
			$this->image($img_file, $x, $y, 1, 1, $type='', $link='', $align='', $resize=true, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox='RT');
			// $this->setXY($x, $y);
			// $this->cell(1, self::FS_12, 'SAMP');
		}

		// Client Information
		$x = 1.75;
		$y = 1.43;

		$this->setFont('freesans', 'B', 14);

		$this->setXY($x, $y);
		$this->cell(3.25, self::FS_14, $this->_data['License_Source']['name']);
		$y += self::FS_14;

		$this->setFont('freesans', '', 12);

		$this->setXY($x, $y);
		$this->cell(3.25, self::FS_12, sprintf('Sample: %s', $this->_data['Lab_Sample']['name']));
		$y += self::FS_12;

		$this->setXY($x, $y);
		$this->cell(3.25, self::FS_12, sprintf('Variety: %s', $this->_data['Variety']['name']));
		$y += self::FS_12;

		// $txt = sprintf('Product: %s [%s]'
		// 	, $this->_data['Product']['name']
		// 	, $this->_data['Product_Type']['name']
		// );
		$txt = sprintf('Product: %s', $this->_data['Product']['name']);
		$this->setXY($x, $y);
		$this->cell(3.25, self::FS_12, $txt);
		$y += self::FS_12;

		// $this->setXY($x, $y);
		// $this->cell(3.25, self::FS_12, sprintf('Product Type: %s'));
		// $y += self::FS_12;

		// @todo Format Quantity Better?
		$txt = sprintf('Quantity: %s %s'
			, $this->_data['Lab_Sample']['qty']
			, $this->_data['Lab_Sample']['uom']
		);

		$this->setXY($x, $y);
		$this->cell(3.25, self::FS_12, $txt);
		$y += self::FS_12;

		$this->setXY(0.50, 3.75);

	}

	/**
	 *
	 */
	public function footer()
	{
		// Disclaimer Text
		$this->setXY(0.5, 9.5);
		$this->setFont('freesans', '', 9);
		$chr0 = $this->getCellHeightRatio();
		$this->setCellHeightRatio(0.9);
		$this->multicell(4.5, self::FS_10, $this->_data['footer_text'], $opt['border'], $opt['align'], $opt['fill'], null, 0.5, 10);
		$this->setCellHeightRatio($chr0);

		// Signature
		$sig_name = $_SESSION['Contact']['fullname'];

		$x = 5.25;
		$y = 10; // 9.5;
		$this->setXY($x, $y);
		$this->setFont('cedarvillecursive', '', 18);
		// $pdf->setFont('homemadeapple');
		$this->cell(2.75, self::FS_16, $sig_name, 'B');

		$this->setXY($x, $y + (self::FS_16 * 1.25));
		$this->setFont('freesans', '', 12, '', true);
		$this->cell(2.75, self::FS_16, $sig_name);

		// 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages()
		$cp = $this->getAliasNumPage(); // Current Page
		$pc = $this->getAliasNbPages(); // Page Count
		$this->setXY($x + 2.125, $y + (self::FS_16 * 2));
		$this->cell(0.75, self::FS_16, sprintf('Page %s/%s', $cp, $pc), 0, 0, 'L');

		// Digital Signature & QR Code

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
			$this->cell(0.5, self::FS_10, UOM::nice($lrm['uom']), 0, 0, 'L');

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
	function draw_metric_qom($x, $y, $lrm) : void
	{

		$uom = $lrm['uom'];
		$qom = $lrm['qom'];
		$max = $lrm['meta']['max'];

		$this->setXY($x, $y);
		$this->cell($this->c1w, self::FS_10, $lrm['name'], 0, 0, 'L');

		switch ($uom) {
			case 'bool':
				// $qom = ($qom ? 'Pass' : 'Fail');
				// $uom = '';
				$this->setXY($x + $this->c1w, $y);
				if ($qom) {
					// PASS
					// $c0 = $this->getTextColor();
					$this->setTextColorArray([ 0x00, 0x99, 0x00 ]);
					$this->cell($this->c2w, self::FS_10, 'Pass', 0, 0, 'R');
				} else {
					// FAIL
					$this->setTextColorArray([ 0xCC, 0x00, 0x00 ]);
					$this->cell($this->c2w, self::FS_10, 'FAIL', 0, 0, 'R');
				}
				$this->setTextColorArray([ 0x00, 0x00, 0x00 ]);
				return;
				break;
		}

		switch ($qom) {
			case -1:
				$qom = 'N/A';
				break;
			case -2:
				$qom = 'N/D';
				break;
			case -3:
				$qom = 'N/T';
				break;
			default:
				switch ($uom) {
					case 'pct':
						// $qom = intval($qom);
						$qom = sprintf('%0.2f', $qom);
						break;
				}
		}

		$this->setXY($x + $this->c1w, $y);
		$this->cell(0.5, self::FS_10, $qom, 0, 0, 'R');

		if ( ! empty($max)) {
			$this->setXY($x + $this->c1w + $this->c2w, $y);
			$this->cell(0.5, self::FS_10, sprintf('%0.2f', $max['val']), 0, 1, 'C');
		}

		if ( ! empty($uom)) {
			$this->setXY($x + $this->c1w + $this->c2w + $this->c3w, $y);
			$this->cell(0.5, self::FS_10, UOM::nice($uom), 0, 0, 'C');
		}

		// if ( ! empty($max)) {
		// 	$this->setXY($x + 2.5, $y);
		// 	$this->cell(0.5, self::FS_10, sprintf('%0.2f', $max['val']), 0, 1, 'L');

	}

	/**
	 *
	 */
	function draw_metric_table_header($x, $y, $metric_name)
	{
		// @todo checkPageBreak for my expected height
		$y_want = $y + self::FS_14 + self::FS_14;
		// if ($this->checkPageBreak($y_want, $y)) {
			// We're on a new page
			// And the Header is Done?
			// $x = 0.50;
			// $y = 1.75;
			// $this->setXY($x, $y);
		// }

		$this->setFont('freesans', 'B', 14);

		$this->setXY($x, $y);
		$this->cell(7.5, self::FS_14, $metric_name);
		$y += self::FS_14;

		// Table Header - Column A
		$this->setFont('freesans', '', 10);

		$c2x = $x + $this->c1w;
		$c3x = $x + $this->c1w + $this->c2w;
		$c4x = $x + $this->c1w + $this->c2w + $this->c3w;

		$this->setXY($x, $y);
		$this->cell($this->c1w, self::FS_12, 'Metric', 'B', 0, 'L');

		$this->setXY($c2x, $y);
		$this->cell($this->c2w, self::FS_12, 'Result', 'B', 0, 'L');

		$this->setXY($c3x, $y);
		$this->cell($this->c3w, self::FS_12, 'Limit', 'B', 0, 'L');

		$this->setXY($c4x, $y);
		$this->cell($this->c4w, self::FS_12, 'UOM', 'B', 0, 'R');

		// Table Header - Column B
		$x = 4.50;
		$c2x = $x + $this->c1w;
		$c3x = $x + $this->c1w + $this->c2w;
		$c4x = $x + $this->c1w + $this->c2w + $this->c3w;

		$this->setXY($x, $y);
		$this->cell($this->c1w, self::FS_12, 'Metric', 'B', 0, 'L');

		$this->setXY($c2x, $y);
		$this->cell($this->c2w, self::FS_12, 'Result', 'B', 0, 'C');

		$this->setXY($c3x, $y);
		$this->cell($this->c3w, self::FS_12, 'Limit', 'B', 0, 'C');

		$this->setXY($c4x, $y);
		$this->cell($this->c4w, self::FS_12, 'UOM', 'B', 0, 'C');


		$this->setXY(0.5, $y + self::FS_12);


	}

	/**
	 * Draw the Two Column Metrics, Alphabetically, Across then Down
	 */
	function draw_metric_table_2_col_a_then_d($metric_name, $metric_list)
	{
		$x = 0.50;
		$y = $this->getY();

		// Eval Metric List
		$output = false;

		$idx = 0;
		$max = count($metric_list);
		if (0 == $max) {
			return(false);
		}

		$metric_list_key_list = array_keys($metric_list);
		for ($idx=0; $idx<$max; $idx+=2) {

			$keyA = $metric_list_key_list[$idx];
			$keyB = $metric_list_key_list[$idx + 1];

			$lrmA = $this->_data['Lab_Result_Metric_list'][ $keyA ];
			$lrmB = $this->_data['Lab_Result_Metric_list'][ $keyB ];

			if ( ( ! empty($lrmA['qom'])) || ( ! empty($lrmB['qom']))) {
				$output = true;
				break;
			}
		}

		if ( ! $output) {
			return(false);
		}

		// Section Header
		$this->draw_metric_table_header($x, $y, $metric_name);

		$x = $this->getX();
		$y = $this->getY();
		$this->setFont('freesans', '', 10);


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
			// $this->cell(0.5, self::FS_10, UOM::nice($lrmA['uom']), 0, 0, 'L');

			// Column 2
			$x = 4.50;
			if ( ! empty($lrmB)) {

				$this->draw_metric_qom($x, $y, $lrmB);
				// $this->setXY($x, $y);
				// $this->cell(2.5, self::FS_10, $lrmB['name'], 0, 0, 'L');

				// $this->setXY($x + 1.875, $y);
				// // $this->cell(0.5, self::FS_10, 'A/L', 0, 0, 'R');

				// $this->setXY($x + 2.5, $y);
				// $this->cell(0.5, self::FS_10, _qom_nice($lrmB['qom']), 0, 0, 'R');

				// $this->setXY($x + 3, $y);
				// $this->cell(0.5, self::FS_10, UOM::nice($lrmB['uom']), 0, 0, 'L');

			}

			$y += self::FS_10;

			// Advance to Next Line
			$this->setXY($x, $y);
			if ($this->checkPageBreak(self::FS_10 * 2)) {

				// We're on the new page
				$x = 0.50;
				$y = 1.75;
				$this->draw_metric_table_header($x, $y, $metric_name);

				$x = $this->getX();
				$y = $this->getY();
				$this->setFont('freesans', '', 10);

			}

		}

	}

}
