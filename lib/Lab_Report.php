<?php
/**
 * Lab Result
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab;

class Lab_Report extends \OpenTHC\SQL\Record
{
	const FLAG_OUTPUT_COA  = 0x00000010;
	const FLAG_OUTPUT_CSV  = 0x00000020;
	const FLAG_OUTPUT_HTML = 0x00000040;
	const FLAG_OUTPUT_JSON = 0x00000080;

	const FLAG_PUBLIC      = 0x00000400;
	const FLAG_PUBLIC_COA  = 0x00000800;

	const FLAG_LOCK        = 0x00200000;
	const FLAG_MUTE        = 0x04000000;
	const FLAG_DEAD        = 0x08000000;


	const STAT_OPEN = 100;
	const STAT_WAIT = 102;
	const STAT_PASS = 200;
	const STAT_PART = 206;
	const STAT_FAIL = 400;

	protected $_table = 'lab_report';

	/**
	 * COA is a Special Document, PDF
	 */
	function getCOA()
	{
		$sql = <<<SQL
		SELECT id, stat, flag, name, size, type
		FROM lab_report_file
		WHERE lab_report_id = :lr0
		  AND type = 'application/pdf'
		  AND flag & :f1 != 0
		SQL;
		$arg = [
			':lr0' => $this->_data['id'],
			':f1' => 0x00000001
		];
		$res = $this->_dbc->fetchAll($sql, $arg);
		foreach ($res as $rec) {
			$ret = $rec;
		}

		return $ret;
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
