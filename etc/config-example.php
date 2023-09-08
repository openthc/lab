<?php
/**
 * OpenTHC Lab Configuration Example
 */

// Init
$cfg = [];

// Database
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

// OpenTHC
$cfg['openthc'] = [
	'dir' => [
		'origin' => 'dir.openthc.example.com'
	],
	'lab' => [
		'id' => '',
		'origin' => 'lab.openthc.example.com',
		'secret' => 'lab.openthc.example.com-secret'
	],
	'pipe' => [
		'origin' => 'pipe.openthc.example.com'
	],
	'sso' => [
		'origin' => 'sso.openthc.example.com',
		'public' => 'lab.openthc.example.com',
	],
];

return $cfg;
