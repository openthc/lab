<?php
/**
 * Lab Metric
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab;

class Lab_Metric extends \OpenTHC\SQL\Record
{
	protected $_table = 'lab_metric';

	const FLAG_FLOWER  = 0x00000010;
	const FLAG_EDIBLE  = 0x00000020;
	const FLAG_EXTRACT = 0x00000040;

	function findAll()
	{
		$sql = sprintf('SELECT * FROM "%s" ORDER BY code', $this->_table);
		$res = $this->_dbc->fetchAll($sql);
		return $res;
	}

	function getTypes()
	{
		$res = $this->_dbc->fetchAll('SELECT id, name, stub FROM lab_metric_type ORDER BY sort, name');
		return $res;
	}

}
