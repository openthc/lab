<?php
/**
 * Support for Different UOMs
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab;

class UOM
{
	/**
	 * v0 UOM List
	 */
	public static $uom_list = [
		'pct' => '%',
		'mg'  => 'mg',
		'mgg' => 'mg/g',
		'mgp' => 'mg/p',
		'mgs' => 'mg/s',
		'aw' => 'a/w',
		'cfu' => 'cfu',
		'ppm' => 'ppm',
		'ppb' => 'ppb',
		'bool' => 'Pass/Fail',
	];

	/**
	 * v1 UOM List
	 */
	public static $uom_list_2 = [
		'aw' => [
			'code' => 'a/w',
			'name' => '',
		],
		'pct' => [
			'code' => '%',
			'name' => 'Percent',
		],
		'mg' => [
			'code' => 'mg',
			'name' => 'Milligrams'
		],
		'mgg' => [
			'code' => 'mg/g',
			'name' => 'Milligrams per Gram'
		],
		'mgp' => [
			'code' => 'mg/p',
			'name' => 'Milligrams per Piece',
		],
		'mgs' => [
			'code' => 'mg/s',
			'name' => 'Milligrams per Serving'
		],
		'cfu' => [
			'code' => 'cfu',
			'name' => 'Coliform Forming Units',
		],
		'ppm' => [
			'code' => 'ppm',
			'name' => 'Parts per Million',
		],
		'ppb' => [
			'code' => 'ppb',
			'name' => 'Parts per Billion',
		],
		'bool' => [
			'code' => 'bool',
			'name' => 'Pass/Fail'
		],
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
