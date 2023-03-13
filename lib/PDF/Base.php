<?php
/**
 * A PDF Base Class
 */

namespace OpenTHC\Lab\PDF;

// specify default font for TCPDF
// define('PDF_FONT_NAME_MAIN', 'freesans');
// define('K_PATH_FONTS', sprintf('%s/var/fonts/', APP_ROOT));

class Base extends \TCPDF
{
	/**
	 * Import Helper
	 * Not sure why we were doing this, which is to re-process PDFs into PDFs?
	 * Was this just to sanatise them?
	 */
	static function import($pdf_source, $pdf_output=null)
	{
		// Default Behaviour is to Overwrite
		if (empty($pdf_output)) {
			$pdf_output = $pdf_source;
		}

		// Working Copy
		$pdf_middle = sprintf('%s-middle.pdf', $pdf_output);

		// Convert
		$cmd = [];
		$cmd[] = '/usr/bin/gs';
		$cmd[] = '-dBATCH';
		$cmd[] = '-dNOPAUSE';
		$cmd[] = '-sDEVICE=pdfwrite';
		$cmd[] = sprintf('-sOutputFile=%s', escapeshellarg($pdf_middle));
		$cmd[] = escapeshellarg($pdf_source);
		$cmd[] = '>/dev/null';
		$cmd[] = '2>&1';
		$cmd = implode(' ', $cmd);
		$buf = shell_exec($cmd);

		// Check Output
		if (is_file($pdf_middle) && (filesize($pdf_middle) > 4096)) {
			rename($pdf_middle, $pdf_output);
			return $pdf_output;
		}

		// Failed
		return false;
	}


	/**
	 *
	 */
	function __construct($orientation='P', $unit='in', $format='LETTER', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false)
	{
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

		// set document information
		$this->setAuthor('openthc.com');
		$this->setCreator('openthc.com');
		$this->setDisplayMode('fullwidth');
		//$this->setTitle($name);
		//$this->setSubject($name);
		//$this->setKeywords($this->name);

		// set margins
		$this->setMargins(0, 0, 0, true);
		$this->setHeaderMargin(1.75);
		$this->setPrintHeader(true);
		$this->setFooterMargin(0);
		$this->setPrintFooter(true);

		// set auto page breaks
		$this->setAutoPageBreak(true, 2);

		// set image scale factor
		$this->setImageScale(0);

		// set default font subsetting mode
		$this->setFontSpacing(0);
		$this->setFontStretching(100);
		$this->setFontSubsetting(true);

		$this->setFont('freesans', '', 12, '', true);
		$this->setHeaderFont(array('freesans', '', 12));
		$this->setFooterFont(array('freesans', '', 12));

		// Set viewer preferences
		$arg = array(
			'HideToolbar' => true,
			'HideMenubar' => true,
			'HideWindowUI' => true,
			'FitWindow' => true,
			'CenterWindow' => true,
			'DisplayDocTitle' => true,
			'NonFullScreenPageMode' => 'UseNone', // UseNone, UseOutlines, UseThumbs, UseOC
			'ViewArea' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
			'ViewClip' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
			'PrintArea' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
			'PrintClip' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
			'PrintScaling' => 'None', // None, AppDefault
			'Duplex' => 'Simplex', // Simplex, DuplexFlipShortEdge, DuplexFlipLongEdge
			//'PickTrayByPDFSize' => true,
			//'PrintPageRange' => array(1,1,2,3),
			//'NumCopies' => 2
		);

		$this->setViewerPreferences($arg);

	}

	/**
	*/
	public function addQRCode($data, $x, $y, $w, $h)
	{
		$style = array(
			'border' => false,
			'padding' => 0,
			'hpadding' => 0,
			'vpadding' => 0,
			'fgcolor' => array(0x00, 0x00, 0x00),
			'bgcolor' => null,
			'position' => null,
		);

		$align = 'N';

		$distort = false;

		$this->write2DBarcode($data, 'QRCODE,H', $x, $y, $w, $h, $style, $align, $distort);

	}

}
