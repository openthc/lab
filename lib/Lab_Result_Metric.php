<?php
/**
 * Lab Result Metric
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab;

class Lab_Result_Metric extends \OpenTHC\SQL\Record
{
	const STAT_OPEN = 100;
	const STAT_PASS = 200;
	const STAT_WAIT = 203;
	const STAT_FAIL = 400;

	/**
	 *
	 */
	function isFailed()
	{
		var_dump($this);
	}

	/**
	 *
	 */
	function isPassed()
	{
		var_dump($this);
	}

}
