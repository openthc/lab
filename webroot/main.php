<?php
/**
 * Main Controller
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
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

use Edoceo\Radix\DB\SQL;

require_once(dirname(dirname(__FILE__)) . '/boot.php');

// Slim Application
$cfg = [];
$cfg['debug'] = true;
$app = new \OpenTHC\App($cfg);


// Container
$con = $app->getContainer();
$con['DBC_Auth'] = function() {
	$cfg = \OpenTHC\Config::get('database/auth');
	$dsn = sprintf('pgsql:application_name=openthc-lab;host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
	return new \Edoceo\Radix\DB\SQL($dsn, $cfg['username'], $cfg['password']);
};
$con['DBC_Main'] = function() {
	$cfg = \OpenTHC\Config::get('database/main');
	$dsn = sprintf('pgsql:application_name=openthc-lab;host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
	return new \Edoceo\Radix\DB\SQL($dsn, $cfg['username'], $cfg['password']);
};
$con['DBC_User'] = function() {

	static $dbc;
	// $cfg = \OpenTHC\Config::get('database/main');
	// $dsn = sprintf('pgsql:application_name=openthc-lab;host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
	// return new \Edoceo\Radix\DB\SQL($dsn, $cfg['username'], $cfg['password']);
	if ( ! empty($_SESSION['dsn'])) {
		$dbc = new SQL($_SESSION['dsn']);
	// } else {
	// 	$cfg = \OpenTHC\Config::get('database/main');
	// 	$c = sprintf('pgsql:application_name=openthc-lab;host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
	// 	$u = $cfg['username'];
	// 	$p = $cfg['password'];
	// 	$dbc = new SQL($c, $u, $p);
	}

	return $dbc;

};


// API
$app->group('/api', 'OpenTHC\Lab\Module\API');


// Legacy v0
$app->get('/share/{id}', function($REQ, $RES, $ARG) {
	return $RES->withRedirect(sprintf('/pub/%s', $ARG['id']), 301);
});

// Public
$app->get('/pub/{id}.{type:html|json|pdf|png|txt}', 'OpenTHC\Lab\Controller\Pub');
$app->get('/pub/{id}/{type:ccrs.txt}', 'OpenTHC\Lab\Controller\Pub');
$app->get('/pub/{id}/{type:wcia.json}', 'OpenTHC\Lab\Controller\Pub');
$app->get('/pub/{id}', 'OpenTHC\Lab\Controller\Pub');

$app->get('/inventory/{id}', function($REQ, $RES, $ARG) {

	// Some Secret is Needed
	$cfg = \OpenTHC\Config::get('openthc/lab');

	$jwt = [];
	$jwt['head'] = [
		'alg' => 'HS256',
		'typ' => 'JWT'
	];
	$jwt['body'] = [
		'iss' => $_SERVER['SERVER_NAME'],
		'exp' => time() + 300,
		'sub' => $_SESSION['Contact']['id'],
		'company' => $_SESSION['Company']['id'],
		'license' => $_SESSION['License']['id'],
		'intent' => 'inventory/view',
		'inventory' => $ARG['id'],
	];
	$jwt['sign'] = [];

	$jwt['head_b64'] = __base64_encode_url(json_encode($jwt['head']));
	$jwt['body_b64'] = __base64_encode_url(json_encode($jwt['body']));
	$jwt['sign'] = hash_hmac('sha256', sprintf('%s.%s', $jwt['head_b64'], $jwt['body_b64']), $cfg['secret'], true);
	$jwt['sign_b64'] = __base64_encode_url($jwt['sign']);

	$url = sprintf('%s/auth/open?jwt=%s'
		, $_SESSION['OpenTHC']['app']['base']
		, sprintf('%s.%s.%s', $jwt['head_b64'], $jwt['body_b64'], $jwt['sign_b64'])
	);

	// __exit_text($url);
	return $RES->withRedirect($url);

	// __exit_text($_SESSION);
	// exit(0);
})
	->add('OpenTHC\Lab\Middleware\Auth')
	->add('OpenTHC\Lab\Middleware\Session');

// Sample Group
$app->group('/sample', 'OpenTHC\Lab\Module\Sample')
	->add('OpenTHC\Lab\Middleware\Menu')
	->add('OpenTHC\Lab\Middleware\Auth')
	->add('OpenTHC\Lab\Middleware\Session');


// Report Group
$app->group('/report', 'OpenTHC\Lab\Module\Report')
	->add('OpenTHC\Lab\Middleware\Menu')
	->add('OpenTHC\Lab\Middleware\Auth')
	->add('OpenTHC\Lab\Middleware\Session');

// Result Group
$app->group('/result', 'OpenTHC\Lab\Module\Result')
	->add('OpenTHC\Lab\Middleware\Menu')
	->add('OpenTHC\Lab\Middleware\Auth')
	->add('OpenTHC\Lab\Middleware\Session');


// Client Group
$app->group('/client', 'OpenTHC\Lab\Module\Client')
	->add('OpenTHC\Lab\Middleware\Menu')
	->add('OpenTHC\Lab\Middleware\Auth')
	->add('OpenTHC\Lab\Middleware\Session');


// Search
$app->get('/search', 'OpenTHC\Lab\Controller\Search')
	->add('OpenTHC\Lab\Middleware\Menu')
	->add('OpenTHC\Lab\Middleware\Auth')
	->add('OpenTHC\Lab\Middleware\Session');

// Config Group
$app->group('/config', 'OpenTHC\Lab\Module\Config')
	->add('OpenTHC\Lab\Middleware\Menu')
	->add('OpenTHC\Lab\Middleware\Auth')
	->add('OpenTHC\Lab\Middleware\Session');

// Sync
// $app->get('/sync', 'OpenTHC\Lab\Controller\Sync')
// 	->add('OpenTHC\Lab\Middleware\Menu')
// 	->add('OpenTHC\Lab\Middleware\Session');

// $app->post('/sync', 'OpenTHC\Lab\Controller\Sync:exec')
// 	->add('OpenTHC\Lab\Middleware\Menu')
// 	->add('OpenTHC\Lab\Middleware\Session');


// Dashboard
$app->get('/dashboard', 'OpenTHC\Lab\Controller\Dashboard')
	->add('OpenTHC\Lab\Middleware\Menu')
	->add('OpenTHC\Lab\Middleware\Auth')
	->add('OpenTHC\Lab\Middleware\Session');


// Intake
$app->map(['GET','POST'], '/intake', 'OpenTHC\Lab\Controller\Intake')
	->add('OpenTHC\Lab\Middleware\Session');


// Intent
$app->map(['GET','POST'], '/intent', 'OpenTHC\Lab\Controller\Intent')
	->add('OpenTHC\Lab\Middleware\Session');


// Authentication
$app->group('/auth', function() {
	$this->get('/open', 'OpenTHC\Lab\Controller\Auth\oAuth2\Open');
	$this->get('/connect', 'OpenTHC\Lab\Controller\Auth\Connect')->setName('auth/connect'); // would like to merge with Open or Back
	$this->get('/back', 'OpenTHC\Lab\Controller\Auth\oAuth2\Back')->setName('auth/back');
	$this->get('/init', 'OpenTHC\Lab\Controller\Auth\Init')->setName('auth/init');
	$this->get('/ping', 'OpenTHC\Controller\Auth\Ping');
	$this->get('/shut', 'OpenTHC\Controller\Auth\Shut');
})
	->add('OpenTHC\Lab\Middleware\Menu')
	->add('OpenTHC\Lab\Middleware\Session');


// Custom Middleware?
$f = sprintf('%s/Custom/boot.php', APP_ROOT);
if (is_file($f)) {
	require_once($f);
}


// Execute
$app->run();

exit(0);
