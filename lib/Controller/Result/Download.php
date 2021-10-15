<?php
/**

*/

namespace App\Controller\Result;

class Download extends \OpenTHC\Controller\Base
{
	private $_fh;
	private $_eol = "\r\n";
	private $_sep = ',';

	function __construct($c)
	{
		parent::__construct($c);

		if (!empty($_GET['eol'])) {
			switch ($_GET['eol']) {
			case '\n';
				$this->_eol = "\n";
				break;
			case '\r\n';
				$this->_eol = "\r\n";
				break;
			case '<br>';
				$this->_eol = '<br>';
				break;
			}
		}

		if (!empty($_GET['sep'])) {
			switch ($_GET['sep']) {
			case '\t';
				$this->_sep = "\t";
				break;
			case ',';
				$this->_sep = ',';
				break;
			}
		}

	}

	function __invoke($REQ, $RES, $ARG)
	{

		$cre = new \OpenTHC\CRE($_SESSION['pipe-token']);
		$res = $cre->get('/qa');
		$res = $res['result'];

		// Headers
		//header('Cache-Control: must-revalidate');
		//header('Content-Description: Data Download');
		//header(sprintf('Content-Disposition: attachment; filename="%s"', $name));
		//header('Content-Transfer-Encoding: binary');
		//header('Content-Type: application/octet-stream');
		//header('Content-Type: text/csv', true);
		header('Content-Type: text/plain');

		$this->_fh = tmpfile();
		$this->_output_header($res[0]);

		foreach ($res as $rec) {
			$this->_output_record($rec);
		}

		fseek($this->_fh, 0, SEEK_SET);
		fpassthru($this->_fh);

		exit(0);
	}

//	function _ouptut_tofile()
//	{
//		$fh = tmpfile();
//
//	// Header
//	fputcsv($fh, array_values($col_spec));
//	fseek($fh, -1, SEEK_CUR);
//	fwrite($fh, "\r\n");
//
//	foreach ($res as $rec) {
//
//		$out = array();
//
//		foreach ($col_spec as $k => $x) {
//			$out[] = $rec[$k];
//		}
//
//		fputcsv($fh, array_values($out));
//
//		// Replace \n with \r\n
//		fseek($fh, -1, SEEK_CUR);
//		fwrite($fh, "\r\n");
//	}
//
//		fseek($fh, 0, SEEK_SET);
//
//		fpassthru($fh);
//	}

	function _output($row)
	{
		fputcsv($this->_fh, array_values($row), $this->_sep);

		// Replace \n with EOL
		fseek($this->_fh, -1, SEEK_CUR);
		fwrite($this->_fh, $this->_eol);

	}

	function _output_header($src)
	{
		$out = array();

		//$src = $rec;

		$out[] = 'global_id';
		$out[] = 'batch_type';
		$out[] = 'status';

		unset($src['global_id']);
		unset($src['batch_type']);
		unset($src['status']);

		$key_list = array_keys($src);
		sort($key_list);

		foreach ($key_list as $k) {
			$out[] = $k;
		}

		$this->_output($out);

	}

	function _output_record($rec)
	{
		$out = array();

		$out['global_id'] = $rec['global_id'];
		$out['batch_type'] = $rec['batch_type'];
		$out['status'] = $rec['status'];

		unset($rec['global_id']);
		unset($rec['batch_type']);
		unset($rec['status']);

		$key_list = array_keys($rec);
		sort($key_list);

		foreach ($key_list as $k) {

			$v = trim($rec[$k]);

			if (0 == strlen($v)) {
				$v = '-';
			}

			if (strpos($v, "\n") !== false) {
				die("k:$k");
			}

			$out[$k] = $v;

		}

		$this->_output($out);

	}

}
