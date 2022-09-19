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
define('APP_BUILD', '420.22.240');

openlog('openthc-lab', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

error_reporting(E_ALL & ~ E_NOTICE & ~ E_WARNING);

require_once(APP_ROOT . '/vendor/autoload.php');

if ( ! \OpenTHC\Config::init(APP_ROOT) ) {
	_exit_html_fail('<h1>Invalid Application Configuration [ALB-035]</h1>', 500);
}

_error_handler_init();

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


/**
 *
 */
function _draw_metric($lm)
{
	$uom = $lm['meta']['uom'] ?: $lm['metric']['meta']['uom'] ?: $lm['metric']['uom'];

?>
	<div class="lab-metric-item">
		<div class="input-group">
			<div class="input-group-prepend">
				<div class="input-group-text"><?= __h($lm['name']) ?></div>
			</div>
			<input
				autocomplete="off"
				class="form-control r lab-metric-qom"
				data-auto-sum="1"
				id="<?= sprintf('lab-metric-%s', $lm['id']) ?>"
				name="<?= sprintf('lab-metric-%s', $lm['id']) ?>"
				placeholder="<?= __h($lm['name']) ?>"
				value="<?= $lm['metric']['qom'] ?>">
			<select
				class="form-control lab-metric-uom"
				name="<?= sprintf('lab-metric-%s-uom', $lm['id']) ?>"
				style="flex: 0 1 5em; width: 5em;"
				tabindex="-1">
			<?php
			foreach (\OpenTHC\Lab\UOM::$uom_list as $v => $n) {
				$sel = ($v == $uom ? ' selected' : null);
				printf('<option%s value="%s">%s</option>', $sel, $v, $n);
			}
			?>
			</select>
		</div>
	</div>
<?php
}


/**
 *
 */
function _draw_metric_select_pass_fail($lm)
{
	// $sel =  == 1 ? 'pass' : 'fail';
	$sel = $lm['metric']['qom'];

?>
	<div class="lab-metric-item">
		<div class="input-group">
			<div class="input-group-prepend">
				<div class="input-group-text"><?= __h($lm['name']) ?></div>
			</div>
			<select class="form-control" name="<?= sprintf('lab-metric-%s', $lm['id']) ?>">
				<option <?= ($sel == '-1' ? 'selected' : null) ?> value="-1">n/a</option>
				<option <?= ($sel == '0' ? 'selected' : null) ?> value="0">Fail</option>
				<option <?= ($sel == '1' ? 'selected' : null) ?> value="1">Pass</option>
			</select>
		</div>
	</div>
<?php
}


/**
 * Draw Status Picker
 */
function _draw_stat_pick()
{
	$html = <<<HTML
	<select class="form-control form-control-sm lab-metric-qom-bulk">
	<option value="OK">OK</option>
	<option value="N/A">N/A</option>
	<option value="N/D">N/D</option>
	<option value="N/T">N/T</option>
	</select>
	HTML;
	return $html;
}


/**
 * Draw UOM Picker
 */
function _draw_unit_pick()
{
	$html = [];
	$html[] = '<select class="form-control form-control-sm lab-metric-uom-bulk">';
	foreach (\OpenTHC\Lab\UOM::$uom_list as $k => $v) {
		$html[] = sprintf('<option data-uom="%s" value="%s">%s</option>'
			, $k
			, $k
			, $v
		);
	}
	$html[] = '</select>';

	return implode('', $html);

}

/**
 *
 */
function _lab_result_status_nice($x)
{
	switch ($x) {
		case 0:
		case 100:
			return 'Pending';
			break;
		case 1: // @todo update all stat=1 lab_result to stat=200
		case 200:
			return '<span class="text-success">Passed</span>';
			break;
		case 400:
			return '<span class="text-danger">Failed</span>';
			break;
		default:
			return sprintf('<span class="text-warning" title="Status: %s">-unknown-</span>', $x);
			break;
	}
}
