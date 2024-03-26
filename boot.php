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

use OpenTHC\Sodium;

define('APP_ROOT', __DIR__);
define('APP_SALT', sha1('$PUT_YOUR_SECRET_VALUE_HERE'));
define('APP_BUILD', '420.23.255');

openlog('openthc-lab', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

error_reporting(E_ALL & ~ E_NOTICE & ~ E_WARNING);

require_once(APP_ROOT . '/vendor/autoload.php');

if ( ! \OpenTHC\Config::init(APP_ROOT) ) {
	_exit_html_fail('<h1>Invalid Application Configuration [ALB-035]</h1>', 500);
}

define('OPENTHC_SERVICE_ID', \OpenTHC\Config::get('openthc/lab/id'));
define('OPENTHC_SERVICE_ORIGIN', \OpenTHC\Config::get('openthc/lab/origin'));

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
	$uom = $lm['metric']['uom'] ?: $lm['metric']['meta']['uom'] ?: $lm['meta']['uom'];

?>
	<div class="lab-metric-item">
		<div class="input-group">
			<div class="input-group-text"><?= __h($lm['name']) ?></div>
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
			<div class="input-group-text"><?= __h($lm['name']) ?></div>
			<select class="form-control" name="<?= sprintf('lab-metric-%s', $lm['id']) ?>">
				<option>-empty-</option>
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
	<div class="input-group">
	<div class="input-group-text">Result:</div>
	<select class="form-control lab-metric-qom-bulk">
		<option value="OK">OK</option>
		<option value="N/A">N/A</option>
		<option value="N/D">N/D</option>
		<option value="N/T">N/T</option>
	</select>
	</div>
	HTML;
	return $html;
}


/**
 * Draw UOM Picker
 */
function _draw_unit_pick()
{
	$html = [];
	$html[] = '<div class="input-group">';
	$html[] = '<div class="input-group-text">UOM:</div>';
	$html[] = '<select class="form-control lab-metric-uom-bulk">';
	foreach (\OpenTHC\Lab\UOM::$uom_list as $k => $v) {
		$html[] = sprintf('<option data-uom="%s" value="%s">%s</option>'
			, $k
			, $k
			, $v
		);
	}
	$html[] = '</select>';
	$html[] = '</div>';

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
			return 'In Progress';
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

/**
 * @param $path should be something like /$PK/$FILE
 * @param $body should be a bunch of bytes
 * @param $type should tell me what kind of bytes $body is
 */
function _openthc_pub($path, $body=null, $type='application/json')
{
	// Construct Message
	$msg = [];
	$msg['name'] = basename($path);
	$msg['path'] = dirname($path);

	if ( ! empty($body)) {
		switch ($type) {
			case 'application/json':
			case 'application/pdf':
			case 'text/html':
			case 'text/plain':
				$msg['type'] = $type;
				break;
			default:
				throw new \Exception('Invalid Media Type for OpenTHC/Pub');
		}
	}

	$client_pk = \OpenTHC\Config::get('openthc/lab/public');
	$client_sk = \OpenTHC\Config::get('openthc/lab/secret');
	$server_pk = \OpenTHC\Config::get('openthc/pub/public');

	// Create Predictable Location
	$hkey = sodium_crypto_generichash($client_sk, '', SODIUM_CRYPTO_GENERICHASH_KEYBYTES);
	$seed = sodium_crypto_generichash($path, $hkey, SODIUM_CRYPTO_GENERICHASH_KEYBYTES);
	$msg['kp'] = sodium_crypto_box_seed_keypair($seed);
	$msg['pk'] = sodium_crypto_box_publickey($msg['kp']);
	$msg['sk'] = sodium_crypto_box_secretkey($msg['kp']);

	$msg['id'] = sprintf('%s/%s', Sodium::b64encode($msg['pk']), $msg['name']);

	$msg['auth'] = Sodium::b64encode($msg['pk']);
	$msg['auth'] = Sodium::encrypt($msg['auth'], $msg['sk'], $server_pk);
	$msg['auth'] = Sodium::b64encode($msg['auth']);

	$req_auth = json_encode([
		'service' => OPENTHC_SERVICE_ID,
		'contact' => $_SESSION['Contact']['id'],
		'company' => $_SESSION['Company']['id'],
		'license' => $_SESSION['License']['id'],
		'message' => $msg['auth']
	]);

	$req_auth = Sodium::encrypt($req_auth, $client_sk, $server_pk);
	$req_auth = Sodium::b64encode($req_auth);

	$url = sprintf('%s/%s', \OpenTHC\Config::get('openthc/pub/origin'), $msg['id']);
	$req = _curl_init($url);
	curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($req, CURLOPT_POSTFIELDS, $body);
	curl_setopt($req, CURLOPT_HTTPHEADER, [
		sprintf('authorization: OpenTHC %s.%s', $client_pk, $req_auth),
		sprintf('content-type: %s', $msg['type']),
	]);

	$res = curl_exec($req);
	// echo "<<<\n$res\n###\n";
	$res = json_decode($res, true);
	$inf = curl_getinfo($req);

	$ret = [];
	$res['code'] = $inf['http_code'];
	$ret['data'] = $res['data'];
	$ret['meta'] = $res['meta'];

	return $ret;

}
