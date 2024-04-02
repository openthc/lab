#!/usr/bin/php
<?php
/**
 * QBench Data Importer
 *
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * @see https://junctionconcepts.zendesk.com/hc/en-us/articles/360030760992-QBench-REST-API-v1-0-Documentation-Full-
 *
 */

use Edoceo\Radix\DB\SQL;

use OpenTHC\Product;
use OpenTHC\Product_Type;
use OpenTHC\Variety;

use OpenTHC\CRE\Base as CRE_Base;

use OpenTHC\Lab\Lab_Result;
use OpenTHC\Lab\Lab_Result_Metric;
use OpenTHC\Lab\Lab_Sample;

require_once(dirname(dirname(__FILE__)) . '/boot.php');
require_once(APP_ROOT . '/vendor/openthc/cre-adapter/lib/QBench.php');

openlog('openthc-lab', LOG_ODELAY | LOG_PERROR | LOG_PID, LOG_LOCAL0);

// Company ID from Arg?
$cli_args = _cli_options();

$dbc_auth = _dbc('auth');

$dsn = $dbc_auth->fetchOne('SELECT dsn FROM auth_company WHERE id = :c0', [ ':c0' => $opt['--company'] ]);
$dbc = new SQL($dsn);

$_SESSION['Company'] = $dbc->fetchRow('SELECT * FROM auth_company WHERE id = :c0', [ ':c0' => $opt['--company'] ]);
$_SESSION['License'] = $dbc->fetchRow('SELECT * FROM license WHERE id = :l0', [ ':l0' => $opt['--license'] ]);
if (empty($_SESSION['Company']['id'])) {
	echo "Invalid Company\n";
	exit(1);
}
if (empty($_SESSION['License']['id'])) {
	echo "Invalid License\n";
	exit(1);
}

$cfg = $dbc->fetchOne("SELECT val FROM base_option WHERE key = 'qbench-auth'");
if (empty($cfg)) {
	echo "Invalid QBench Configuration\n";
	exit(1);
}
$cfg = json_decode($cfg, true);

$qbc = new \OpenTHC\CRE\QBench($cfg);
$res = $qbc->auth();


switch ($cli_args['--object']) {
	case 'sample':
	case 'lab-sample':

		// Works Perfect
		$lab_sample = $dbc->fetchRow('SELECT * FROM lab_sample WHERE id = :ls0', [
			':ls0' => $cli_args['--object-id']
		]);
		$lab_sample['meta'] = json_decode($lab_sample['meta'], true);
		var_dump($lab_sample);
		exit;

		$res = $qbc->post(sprintf('/api/v1/sample/%s', $lab_sample['meta']['id']), [
			'WCIAdataLINK' => 'https://openthc.pub/p2ITcMW3Rz2WRXR8UCDdMhQMH9Pb_WAjx9Cqwp8kLAY/wcia.json',
		]);

		var_dump($res);

		exit;

}




/**
 * Parse CLI Options
 */
function _cli_options()
{
	$doc = <<<DOC
	Lab QBench Export
	Usage:
		qbench-export --company=<ID> --license=<ID> --object=<TYPE> --object-id=<ID>

	Options:
		--company=<ID>
		--license=<ID>
		--object=<TYPE>
		--object-id=<ID>
	DOC;

	$res = Docopt::handle($doc, [
		'help' => true,
		'optionsFirst' => true,
	]);
	$opt = $res->args;

	return $opt;

}
