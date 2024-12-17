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
		'origin' => 'https://dir.openthc.example'
	],
	'lab' => [
		'id' => '/* LAB SERVICE ULID */',
		'origin' => 'https://lab.openthc.example',
		'secret' => 'lab.openthc.example-secret'
	],
	'pipe' => [
		'origin' => 'https://pipe.openthc.example'
	],
	'sso' => [
		'origin' => 'https://sso.openthc.example',
		'client-id' => '/* LAB SERVICE ULID */',
		'client-sk' => '/* LAB SERVICE CLIENT KEY */',
	],
];

return $cfg;
