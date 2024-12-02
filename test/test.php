#!/usr/bin/php
<?php
/**
 * Lab Test Runner
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Test;

require_once(dirname(__DIR__) . '/boot.php');

$doc = <<<DOC
OpenTHC Lab Test Runner

Usage:
	test <command> [<command-options>...]

Commands:
	all       run all tests
	phplint   run some tests
	phpunit
	phpstan

Options:
	--phpunit-config=FILE      File to use for PHPUnit XML Configuration
	--phpunit-filter=FILTER    Filter to pass to PHPUnit
DOC;

$res = \Docopt::handle($doc, [
	'exit' => false,
	'help' => true,
	'optionsFirst' => true,
]);
var_dump($res);
$arg = $res->args;
var_dump($arg);
if ('all' == $arg['<command>']) {
	$arg['phplint'] = true;
	$arg['phpstan'] = true;
	$arg['phpunit'] = true;
} else {
	$cmd = $arg['<command>'];
	$arg[$cmd] = true;
	unset($arg['<command>']);
}
var_dump($arg);

$dt0 = new \DateTime();

define('OPENTHC_TEST_OUTPUT_BASE', \OpenTHC\Test\Helper::output_path_init());


// PHPLint
if ($arg['phplint']) {
	$tc = new \OpenTHC\Test\Facade\PHPLint([
		'output' => OPENTHC_TEST_OUTPUT_BASE
	]);
	// $res = $tc->execute();
	// var_dump($res);
}


// PHPStan
if ($arg['phpstan']) {
	$tc = new \OpenTHC\Test\Facade\PHPStan([
		'output' => OPENTHC_TEST_OUTPUT_BASE
	]);
	// $res = $tc->execute();
	// var_dump($res);
}


// PHPUnit
if ($arg['phpunit']) {
	$cfg = [
		'output' => OPENTHC_TEST_OUTPUT_BASE
	];
	$cfg_file_list = [];
	$cfg_file_list[] = sprintf('%s/phpunit.xml', __DIR__);
	$cfg_file_list[] = sprintf('%s/phpunit.xml.dist', __DIR__);
	foreach ($cfg_file_list as $f) {
		if (is_file($f)) {
			$cfg['--configuration'] = $f;
			break;
		}
	}
	// Filter?
	if ( ! empty($cli_args['--filter'])) {
		$cfg['--filter'] = $cli_args['--filter'];
	}
	$tc = new \OpenTHC\Test\Facade\PHPUnit($cfg);
	$res = $tc->execute();
	var_dump($res);
}


// Done
\OpenTHC\Test\Helper::index_create($html);


// Output Information
$origin = \OpenTHC\Config::get('openthc/lab/origin');
$output = str_replace(sprintf('%s/webroot/', APP_ROOT), '', OPENTHC_TEST_OUTPUT_BASE);

echo "TEST COMPLETE\n  $origin/$output\n";
