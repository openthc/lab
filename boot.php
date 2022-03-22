<?php
/**
 * (c) 2018 OpenTHC, Inc.
 *
 * This file is part of OpenTHC Lab Portal released under GPL-3.0 License
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * OpenTHC Lab Application Bootstrap
 *
 * OpenTHC Laboratory Portal is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published by
 * the Free Software Foundation.
 *
 * OpenTHC Laboratory Portal is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OpenTHC Laboratory Portal.  If not, see <https://www.gnu.org/licenses/>.
 */

define('APP_ROOT', __DIR__);
define('APP_SALT', sha1('$PUT_YOUR_SECRET_VALUE_HERE'));
define('APP_BUILD', '420.19.123');

openlog('openthc-lab', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

error_reporting(E_ALL & ~ E_NOTICE & ~ E_WARNING);

require_once(APP_ROOT . '/vendor/autoload.php');

if ( ! \OpenTHC\Config::init(APP_ROOT) ) {
	_exit_html_fail('<h1>Invalid Application Configuration [ALB-035]</h1>', 500);
}

/**
 * Database Connection Getter
 */
function _dbc($dsn=null)
{
	static $dbc_list = [];

	if (empty($dsn)) {
		throw new \Exception('Invalid Data Source Name [ABS-045]');
	}

	$dbc = $dbc_list[$dsn];
	if ( ! empty($dbc)) {
			return $dbc;
	}

	switch ($dsn) {
	case 'auth':
	case 'base':
	case 'cic':
	case 'corp':
	case 'main':
	case 'root':
	case 'ops':

		// @v2 URL Based Connection
		$cfg = \OpenTHC\Config::get(sprintf('database/%s', $dsn));
		if (empty($cfg['database'])) {
				_exit_text('Invalid Database Configuration [AFD-052]', 500);
		}

		$c = sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
		$dbc_list[$dsn] = new \Edoceo\Radix\DB\SQL($c, $cfg['username'], $cfg['password']);

		return $dbc_list[$dsn];

		break;

	default:
		$ret = new \Edoceo\Radix\DB\SQL($dsn);
		return $ret;
	}

}


/**
 * Make a Nicer Looking ID
 */
function _nice_id($x0, $x1=null)
{
	$r = $x0;

	if (!empty($x1)) {
		$r = $x1;
		$r = preg_replace('/\w+:\/\//', '', $r);
	}

	return $r;

}
