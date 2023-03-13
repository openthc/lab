<?php
/**
 * Lab Sample
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab;

use Edoceo\Radix\DB\SQL;

use OpenTHC\Company;

class Lab_Sample extends \OpenTHC\SQL\Record
{
	const FLAG_ACTIVE = 0x00000001;
	const FLAG_RESULT = 0x00000002;

	const FLAG_PASSED = 0x00000010;
	const FLAG_FAILED = 0x00000020;
	const FLAG_REJECT = 0x00000040;

	const FLAG_FILE_IMAGE = 0x00000100;
	const FLAG_FILE_CERT  = 0x00000200;
	const FLAG_FILE_DATA  = 0x00000400;

	const FLAG_DONE = 0x01000000;
	const FLAG_VOID = 0x04000000;
	const FLAG_DEAD = 0x08000000;

	const STAT_OPEN = 100;
	const STAT_LIVE = 200;
	const STAT_WAIT = 203;
	const STAT_DONE = 302;
	const STAT_VOID = 410;

	protected $_table = 'lab_sample';

	// public $_Inventory;
	public $_Company;
	public $_License;

	/**
	 *
	 */
	function __construct($dbc=null, $rec=null)
	{
		parent::__construct($dbc, $rec);

		// $sql = 'SELECT * FROM lab_sample WHERE guid = ?';
		// $arg = array($oid);
		// //Radix::dump($sql);
		// //Radix::dump($arg);
		// $res = SQL::fetch_row($sql, $arg);
		//
		// $this->_data = $res;

		// $this->_Inventory = $this->_data;
		//$this->_Inventory['guid'] = $oid;

		// Radix::dump($this->_Inventory);

		// $this->_Inventory['meta'] = json_decode($res['meta'], true);

		// if (!empty($this->_Inventory['id'])) {
		// 	//$this->_Company = // From Main
		// 	//$this->_License = // From Main
		// }

		$this->_Company = array();
		$this->_License = array();

		//Radix::dump($arg);
		// if (!empty($this->_Inventory['id'])) {
		// 	$this->_inflate_inventory();
		// }
		// _find_lab_sample_in_biotrack_wa($this->_Inventory['guid']);

		if (!empty($this->_data['company_id'])) {
			$this->_Company = new Company($this->_data['company_id']);
		}

	}

	/**
	 *
	 */
	function addFile()
	{

	}

	/**
	 *
	 */
	function getFiles() : array
	{
		$img_path = sprintf('%s/var/%s/sample/%s.*', APP_ROOT, $_SESSION['Company']['id'], $this->_data['id']);
		$img_list = glob($img_path);
		// sprintf('%s/var/%s/sample/%s.%s', APP_ROOT, $_SESSION['Company']['id'], $Lab_Sample['id'], $output_type);
		return $img_list;
	}

	/**
	 *
	 */
	function getImageFile() : ?string
	{
		$img_list = $this->getFiles();
		$img_file = $img_list[0];
		return $img_file;
	}

	/**
	 *
	 */
	function getStatHTML()
	{
		switch ($this->_data['stat']) {
			// case 0:
			case self::STAT_OPEN:
				return 'Open';
				break;
			case self::STAT_LIVE:
				return 'Live';
				break;
			case self::STAT_DONE:
				return 'Done';
				break;
			case self::STAT_VOID:
				return '<span class="text-danger">VOID</span>';
				break;
		}

		return '-unknown-';
	}

}
