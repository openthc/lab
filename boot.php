<?php
/**
 * OpenTHC Lab Application Bootstrap
 *
 * This file is part of OpenTHC Laboratory Portal
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

error_reporting(E_ALL & ~ E_NOTICE);

require_once(APP_ROOT . '/vendor/autoload.php');

\OpenTHC\Config::init(APP_ROOT);
