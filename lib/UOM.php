<?php
/**
 * Support for Different UOMs
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace App;

class UOM
{
	public static $uom_list = [
		'pct' => '%',
		'mg'  => 'mg',
		'mgg' => 'mg/g',
		'mgs' => 'mg/s',
		'aw' => 'a/w',
		'cfu' => 'cfu',
		'ppm' => 'ppm',
		'ppb' => 'ppb',
		'bool' => 'Pass/Fail',
	];

	/**
	 *
	 */
	function nice($k)
	{
		$r = self::$uom_list[$k];
		if (empty($r)) {
			$r = '-';
		}

		return $r;
	}

}
