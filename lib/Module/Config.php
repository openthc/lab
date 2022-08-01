<?php
/**
 * Wraps all the Routing for the Config Module
 */

namespace OpenTHC\Lab\Module;

class Config extends \OpenTHC\Module\Base
{
	function __invoke($a)
	{
		$a->get('', 'OpenTHC\Lab\Controller\Config');
		$a->map([ 'GET', 'POST' ], '/metric', 'OpenTHC\Lab\Controller\Config\Metric');
		$a->map([ 'GET', 'POST' ], '/coa-layout', 'OpenTHC\Lab\Controller\Config\COA');
		$a->map([ 'GET', 'POST' ], '/external', 'OpenTHC\Lab\Controller\Config\External');
		$a->map([ 'GET', 'POST' ], '/instrument', 'OpenTHC\Lab\Controller\Config\Instrument');
		$a->map([ 'GET', 'POST' ], '/intake', 'OpenTHC\Lab\Controller\Config\Intake');
		$a->map([ 'GET', 'POST' ], '/sample', 'OpenTHC\Lab\Controller\Config\Sample');
	}
}
