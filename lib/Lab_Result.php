<?php
/**
 * Lab Result
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab;

class Lab_Result extends \OpenTHC\SQL\Record
{
	const FLAG_SYNC = 0x00100000;
	const FLAG_LOCK = 0x00200000;
	const FLAG_MUTE = 0x04000000;
	const FLAG_DEAD = 0x08000000;

	const FLAG_PUBLIC     = 0x00000400;
	const FLAG_PUBLIC_COA = 0x00000800;

	const STAT_WAIT = 100;
	const STAT_PASS = 200;
	const STAT_PART = 206;
	const STAT_FAIL = 400;

	protected $_table = 'lab_result';

	/**
	 * Get the Lab Result Metrics
	 */
	function getMetrics()
	{
		$sql = <<<SQL
SELECT lab_result_metric.*
, lab_metric.name AS lab_metric_name
, lab_metric.type AS lab_metric_type
, lab_metric.lab_metric_type_id AS lab_metric_type_id
, lab_metric.meta AS lab_metric_meta
FROM lab_result_metric
JOIN lab_metric ON lab_result_metric.lab_metric_id = lab_metric.id
WHERE lab_result_metric.lab_result_id = :lr0
ORDER BY lab_metric.type, lab_metric.sort, lab_metric.name
SQL;

		$arg = [
			':lr0' => $this->_data['id']
		];

		$res = $this->_dbc->fetchAll($sql, $arg);

		$ret = [];
		foreach ($res as $rec) {
			$ret[ $rec['lab_metric_id'] ] = $rec;
		}

		return $ret;
	}

	/**
	 * Returns a List of Metric Groups w/a list of lab_metric IDs in that group
	 */
	function getMetricListGrouped()
	{
		$sql = <<<SQL
		SELECT lab_metric.id
		  , lab_metric.lab_metric_type_id
		  , lab_metric.sort
		  , lab_metric.name
		FROM lab_metric
		JOIN lab_metric_type ON lab_metric.lab_metric_type_id = lab_metric_type.id
		ORDER BY lab_metric.sort, lab_metric.name
		SQL;

		$res = $this->_dbc->fetchAll($sql);
		$ret = [];
		foreach ($res as $rec) {
			if (empty($ret[ $rec['lab_metric_type_id'] ])) {
				$ret[ $rec['lab_metric_type_id'] ] = [];
			}
			$ret[ $rec['lab_metric_type_id'] ][] = $rec;
		}

		return $ret;

	}

	/**
	 * Get All the Metrics in Groups
	 */
	function getMetrics_Grouped()
	{

		$ret_lab_metric_group = [];

		// $metricTab
		$lab_metric_section_list = $this->_dbc->fetchAll('SELECT * FROM lab_metric_type ORDER BY sort, stub');

		// Spin Sections Up
		foreach ($lab_metric_section_list as $lms) {

			$lms['meta'] = json_decode($lms['meta'], true);
			$lms['metric_list'] = [];

			$ret_lab_metric_group[ $lms['id'] ] = $lms;
		}

		// Index the Lab Metric List
		$lab_metric_list = [];
		$res = $this->_dbc->fetchAll('SELECT * FROM lab_metric WHERE stat = 200 ORDER BY sort, type, name');
		foreach ($res as $lm) {
			$lm['meta'] = json_decode($lm['meta'], true);
			$lab_metric_list[ $lm['id'] ] = $lm;
		}

		// Now Merge Results into Lab Metric List
		$res_lab_result_metric = $this->_dbc->fetchAll('SELECT * FROM lab_result_metric WHERE lab_result_id = :lr0', [
			':lr0' => $this->_data['id']
		]);
		foreach ($res_lab_result_metric as $lrm) {
			$lab_metric_list[ $lrm['lab_metric_id'] ]['metric'] = $lrm;
		}

		// Merge Into the Groups
		foreach ($lab_metric_list as $lm) {
			$ret_lab_metric_group[ $lm['lab_metric_type_id'] ]['metric_list'][ $lm['id'] ] = $lm;
		}

		return $ret_lab_metric_group;

	}

	/**
	 *
	 */
	function getMetricsOpenTHC($data)
	{
		$metric_field_data = file_get_contents(sprintf('%s/etc/lab-result.json', APP_ROOT));
		$metric_field_data = json_decode($metric_field_data, true);

		$data['metric_type_list'] = array_reduce($metric_field_data, function($prev, $item) {
			$prev[ $item['type'] ] = $item['type'];
			return $prev;
		});
		ksort($data['metric_type_list']);

		$lrm = $data['Result']['meta'];

		foreach ($metric_field_data as $i => $mf) {
			$k = $mf['leafdata']['path'];
			if ($lrm[$k] !== null) {
				$mf['qom'] = $lrm[$k];
				$data['MetricList'][ $mf['type'] ][ $mf['id'] ] = $mf;
			}

			$data['metric_list'][] = $mf;

		}

		// $data['MetricList']['General'][''] = [
		// 	'name' => '',
		// 	'qom'  => $lrm['medically_compliant_status']
		// ];

		// @todo Here we should evaluate LRM to find junk data
		unset($data['Result']['meta']['for_inventory']);

		// __exit_text($data);

		return $data;

	}


	/**
	 * Returns the COA File Path for this Lab Result
	 * @return [type] [description]
	 */
	function getCOAFile()
	{
		if (empty($this->_data['id'])) {
			throw new \Exception('Invalid Result [ALR-044]');
		}

		$id = $this->_data['id'];

		// One True Method
		$coa_hash = implode('/', str_split(sprintf('%08x', crc32($id)), 2));
		$coa_file = sprintf('%s/coa/%s/%s.pdf', APP_ROOT, $coa_hash, $id);

		return $coa_file;

	}

	/**
	 * Set the given PDF document as the COA
	 */
	function setCOAFile($coa_source)
	{
		$pdf_output = $this->getCOAFile();
		$png_ouptut = preg_replace('/\.pdf$/', '.png', $pdf_output);

		$dir_output = dirname($pdf_output);
		if ( ! is_dir($dir_output)) {
			mkdir($dir_output, 0755, true);
		}

		// Check Type
		$mime = mime_content_type($coa_source);
		switch ($mime) {
		case 'application/pdf':
			// OK
			break;
		// case 'image/jpeg':
		// case 'image/png':
			// @todo should we auto-convert or keep these?
		default:
			throw new \Exception('COA File Type Not Supported [LLR-096]');
		}

		$x = rename($coa_source, $pdf_output);
		if ($x) {
			$this->_data['coa_file'] = $pdf_output;
		}

		// @todo Inspect the document

		// /usr/bin/pdf2txt
		// Then evaluate Text Content?

		// Evaluate PDF
		// $cmd = array();
		// $cmd[] = '/usr/bin/pdftk';
		// $cmd[] = escapeshellarg($coa_file);
		// $cmd[] = 'dump_data';
		// $buf = shell_exec(implode(' ', $cmd));

		// PageMediaRect: 0 0 612 792
		// PageMediaDimensions: 612 792
		// if (preg_match('//')) {
		// }

		// Extract information with GS
		// Fix the PageSize to be Letter if it's too small (like from CA)
		// See http://milan.kupcevic.net/ghostscript-ps-pdf/#refs
		// $cmd = array();
		// $cmd[] = '/usr/bin/gs';
		// $cmd[] = escapeshellarg($pdf_output);
		// $buf = shell_exec(implode(' ', $cmd));
		// $pdf_info = _pdf_get_info($pdf_output);
		// if ($pdf_info['MediaBox'] < 629)

		// Resize the Document?
		$cmd = array();
		$cmd[] = '/usr/bin/gs';
		$cmd[] = '-dNumRenderingThreads=4';
		$cmd[] = '-dNOPAUSE';
		$cmd[] = '-sDEVICE=pdfwrite';
		$cmd[] = '-sPAPERSIZE=letter';
		$cmd[] = '-dFIXEDMEDIA';
		$cmd[] = '-dPDFFitPage';
		$cmd[] = '-dCompatibilityLevel=1.4';
		$cmd[] = '-o';
		$cmd[] = escapeshellarg($pdf_output); //  /tmp/coa-output-final.pdf';
		// -sOutputFile=
		$cmd[] = escapeshellarg($pdf_middle);
		$cmd[] = '2>&1';
		// $buf = shell_exec(implode(' ', $cmd));
		// var_dump($buf); exit;
		// rename($pdf_middle, $pdf_output);

		// Create PNG Preview
		// Capture Page 1 of DOCUMENT.pdf into DOCUMENT.png as Thumbnail
		$cmd = [];
		$cmd[] = '/usr/bin/convert';
		$cmd[] = escapeshellarg(sprintf('%s[0]', $pdf_output));
		$cmd[] = '-resize 240x240';
		$cmd[] = escapeshellarg($png_ouptut);
		$cmd[] = '2>&1';
		$cmd = implode(' ', $cmd);
		$buf = shell_exec($cmd);

		return true;

	}

	/**
	 * Try to Import the COA
	 * @param $coa_source URL or FILE Path/Handle or Bytes of PDF as String
	 */
	function importCOA($coa_source)
	{
		if (empty($coa_source)) {
			return(false);
		}

		$pdf_output = $this->getCOAFile();
		$pdf_source = sprintf('%s/var/%s.pdf', APP_ROOT, _ulid());

		if (is_string($coa_source)) {
			$type = strtolower(substr($coa_source, 0, 4));
			switch ($type) {
				case '%pdf':
					// Very likely PDF bytes
					file_put_contents($pdf_source, $coa_source);
					break;
				case 'http':
					// Fetch This
					$req = __curl_init($coa_source);
					$raw = curl_exec($req);
					$inf = curl_getinfo($req);
					if (200 != $inf['http_code']) {
						return [
							'code' => $inf['http_code'],
							'data' => null,
							'meta' => [ 'detail' => 'Invalid Response Code' ]
						];
					}
					if ('application/pdf' != $inf['content_type']) {
						return [
							'code' => 400,
							'data' => null,
							'meta' => [ 'detail' => sprintf('Invalid Content Type "%s"', $inf['content_type']) ]
						];
					}

					file_put_contents($pdf_source, $raw);

					break;

				default:
					// Ok, Likely a File String?
					if (is_file($coa_source)) {
						$pdf_source = $coa_source;
					}
					break;
			}
		}

		$dir = dirname($pdf_output);
		if ( ! is_dir($dir)) {
			mkdir($dir, 0755, true);
		}

		if ( ! rename($pdf_source, $pdf_output) ) {
			return [
				'code' => 400,
				'data' => null,
				'meta' => [ 'detail' => sprintf('Failed to Create "%s"', $pdf_output) ]
			];
		}

		return [
			'code' => 200,
			'data' => $pdf_output,
			'meta' => []
		];

	}

	/**
	 * Updates the Lab_Metric values from new Totals
	 */
	function updateCannabinoids()
	{
		$sql = <<<SQL
		SELECT id, lab_metric_id, qom, uom
		FROM lab_result_metric
		WHERE lab_result_id = :lr0
		  AND lab_metric_id IN (SELECT id FROM lab_metric WHERE lab_metric_type_id = :lmt0)
		SQL;
		$res = $this->_dbc->fetchAll($sql, [
			':lr0' => $this->_data['id'],
			':lmt0' => '018NY6XC00LMT0HRHFRZGY72C7',
		]);
		if (empty($res)) {
			return(false);
		}

		$sum_all = 0;
		$sum_cbd = 0;
		$sum_thc = 0;
		$uom_list = [];

		foreach ($res as $rec) {
			switch ($rec['lab_metric_id']) {
				case '018NY6XC00DEEZ41QBXR2E3T97': // total-cbd
				case '018NY6XC00PXG4PH0TXS014VVW': // total-thc
				case '018NY6XC00V7ACCY94MHYWNWRN':
				case '018NY6XC00SAE8Q4JSMF40YSZ3':
					break 2;
				case '018NY6XC00LM49CV7QP9KM9QH9': // d9-thc
					$sum_thc += $rec['qom'];
					break;
				// case '018NY6XC00LM877GAKMFPK7BMC': // d8-thc
				case '018NY6XC00LMB0JPRM2SF8F9F2': // thca
					$sum_thc += ($rec['qom'] * 0.877);
					break;
				case '018NY6XC00LMK7KHD3HPW0Y90N': // cbd
					$sum_cbd += $rec['qom'];
					break;
				case '018NY6XC00LMENDHEH2Y32X903': // cbda
					$sum_cbd += ($rec['qom'] * 0.877);
					break;
			}
			$sum_all += $rec['qom'];
			$uom_list[] = $rec['uom'];
		}

		// Special Case Lab Metrics for Up-Scaling to the Lab Result

		// Update
		$sql = <<<SQL
		INSERT INTO lab_result_metric (lab_result_id, lab_metric_id, qom, uom)
		VALUES (:lr0, :lm0, :q0, :u0)
		ON CONFLICT (lab_result_id, lab_metric_id)
		DO UPDATE SET qom = :q0, uom = :u0
		SQL;

		// Activated Total THC
		$this->_dbc->query($sql, [
			':lr0' => $this->_data['id'],
			':lm0' => '018NY6XC00PXG4PH0TXS014VVW',
			':q0' => $sum_thc,
			':u0' => 'pct',
		]);

		// Activated Total CBD
		$this->_dbc->query($sql, [
			':lr0' => $this->_data['id'],
			':lm0' => '018NY6XC00DEEZ41QBXR2E3T97',
			':q0' => $sum_cbd,
			':u0' => 'pct',
		]);

		// Total THC+CBD
		$this->_dbc->query($sql, [
			':lr0' => $this->_data['id'],
			':lm0' => '018NY6XC00V7ACCY94MHYWNWRN',
			':q0' => $sum_thc + $sum_cbd,
			':u0' => 'pct',
		]);


		// Total Cannabinoids
		$this->_dbc->query($sql, [
			':lr0' => $this->_data['id'],
			':lm0' => '018NY6XC00SAE8Q4JSMF40YSZ3',
			':q0' => $sum_all,
			':u0' => 'pct',
		]);

	}

	/**
	 * Get Status as HTML or Text
	 */
	function getStat($f='html')
	{
		$ret = $this->_data['stat'];

		switch ($this->_data['stat']) {
			case self::STAT_WAIT:
				$ret = 'Working';
				break;
			case self::STAT_PASS:
				$ret = '<span class="text-success">Passed</span>';
				break;
			case self::STAT_PART:
				$ret = '<span class="text-warning">Partial</span>';
				break;
			case self::STAT_FAIL:
				$ret = '<span class="text-danger">Failed</span>';
				break;
		}

		if ('text' == $f) {
			$ret = strip_tags($ret);
		}

		return $ret;
	}

}
