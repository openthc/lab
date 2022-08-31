<?php
/**
 * Lab Result
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab;

class Lab_Report extends \OpenTHC\SQL\Record
{
	// const FLAG_SYNC = 0x00100000;

	const FLAG_OUTPUT_COA  = 0x00000010;
	const FLAG_OUTPUT_CSV  = 0x00000020;
	const FLAG_OUTPUT_HTML = 0x00000040;
	const FLAG_OUTPUT_JSON = 0x00000080;

	const FLAG_PUBLIC      = 0x00000400;
	const FLAG_PUBLIC_COA  = 0x00000800;

	const FLAG_LOCK        = 0x00200000;
	const FLAG_MUTE        = 0x04000000;
	const FLAG_DEAD        = 0x08000000;


	const STAT_WAIT = 100;
	const STAT_PASS = 200;
	const STAT_PART = 206;
	const STAT_FAIL = 400;

	protected $_table = 'lab_report';

}
