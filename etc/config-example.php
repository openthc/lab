<?php
/**
 * lab.openthc
 * Application Configuration
 */

$cfg = [];
$cfg['database'] = [
	'auth' => [
		'hostname' => 'sql0',
		'database' => 'openthc_auth',
		'username' => 'openthc_auth',
		'password' => 'openthc_auth',
	],
	'main' => [
		'hostname' => 'sql0',
		'database' => 'openthc_main',
		'username' => 'openthc_main',
		'password' => 'openthc_main'
	],
];

$cfg['openthc'] = [
	'dir' => [
		'hostname' => 'dir.openthc.dev'
	],
	'lab' => [
		'hostname' => 'lab.openthc.dev',
		'secret' => 'lab.openthc.dev-secret'
	],
	'pipe' => [
		'hostname' => 'pipe.openthc.dev'
	],
	'sso' => [
		'hostname' => 'sso.openthc.dev',
		'public' => 'lab.openthc.dev',
		'secret' => 'lab.openthc.dev-secret'
	],
];

return $cfg;
