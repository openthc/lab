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
		'mg/g' => 'mg/g',
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
	static function nice($k)
	{
		if (empty($k)) {
			return '?';
		}

		$r = self::$uom_list[$k];
		if (empty($r)) {
			$r = '-';
		}

		// switch ($uom) {
		// 	case 'pct':
		// 		return '&percnt;';
		// 	case 'mg_g':
		// 		return 'mg/g';
		// 	case 'cfu/g':
		// 		return 'cfu/g';
		// 	case 'aw':
		// 		return 'a<sub>w</sub>';
		// 	case 'ppm':
		// 		return 'ppm';
		// }

		// return '-unknown-';

		return $r;
	}

}
