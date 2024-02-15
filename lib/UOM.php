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
		'num' => '#',
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
		'bool' => [
			'code' => 'bool',
			'name' => 'Pass/Fail'
		],
		'cfu' => [
			'code' => 'cfu',
			'name' => 'Coliform Forming Units',
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
		'num' => [
			'code' => '#',
			'name' => 'Number',
		],
		'pct' => [
			'code' => '%',
			'name' => 'Percent',
		],
		'ppb' => [
			'code' => 'ppb',
			'name' => 'Parts per Billion',
		],
		'ppm' => [
			'code' => 'ppm',
			'name' => 'Parts per Million',
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

		return $r;

	}

}
