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
$app->group('/api', 'App\Module\API');


// Legacy v0
$app->get('/share/{id}', function($REQ, $RES, $ARG) {
	return $RES->withRedirect(sprintf('/pub/%s', $ARG['id']), 301);
});

// Public
$app->get('/pub/{id}.{type:html|json|pdf|png|txt}', 'App\Controller\Pub');
$app->get('/pub/{id}/{type:wcia.json}', 'App\Controller\Pub');
$app->get('/pub/{id}', 'App\Controller\Pub');


// Sample Group
$app->group('/sample', 'App\Module\Sample')
	->add('App\Middleware\Menu')
	->add('App\Middleware\Auth')
	->add('App\Middleware\Session');


// Result Group
$app->group('/result', 'App\Module\Result')
	->add('App\Middleware\Menu')
	->add('App\Middleware\Auth')
	->add('App\Middleware\Session');


// Client Group
$app->group('/client', 'App\Module\Client')
	->add('App\Middleware\Menu')
	->add('App\Middleware\Auth')
	->add('App\Middleware\Session');


// Search
$app->get('/search', 'App\Controller\Search')
	->add('App\Middleware\Menu')
	->add('App\Middleware\Auth')
	->add('App\Middleware\Session');

// Config Group
$app->group('/config', 'App\Module\Config')
	->add('App\Middleware\Menu')
	->add('App\Middleware\Auth')
	->add('App\Middleware\Session');

// Sync
// $app->get('/sync', 'App\Controller\Sync')
// 	->add('App\Middleware\Menu')
// 	->add('App\Middleware\Session');

// $app->post('/sync', 'App\Controller\Sync:exec')
// 	->add('App\Middleware\Menu')
// 	->add('App\Middleware\Session');


// Dashboard
$app->get('/dashboard', 'App\Controller\Dashboard')
	->add('App\Middleware\Menu')
	->add('App\Middleware\Auth')
	->add('App\Middleware\Session');


// Intent
$app->map(['GET','POST'], '/intent', 'App\Controller\Intent')
	->add('App\Middleware\Session');


// Authentication
$app->group('/auth', function() {
	$this->get('/open', 'App\Controller\Auth\oAuth2\Open');
	$this->get('/connect', 'App\Controller\Auth\Connect')->setName('auth/connect'); // would like to merge with Open or Back
	$this->get('/back', 'App\Controller\Auth\oAuth2\Back')->setName('auth/back');
	$this->get('/init', 'App\Controller\Auth\Init')->setName('auth/init');
	$this->get('/ping', 'OpenTHC\Controller\Auth\Ping');
	$this->get('/shut', 'OpenTHC\Controller\Auth\Shut');
})
	->add('App\Middleware\Menu')
	->add('App\Middleware\Session');


// Custom Middleware?
$f = sprintf('%s/Custom/boot.php', APP_ROOT);
if (is_file($f)) {
	require_once($f);
}


// Execute
$app->run();

exit(0);
