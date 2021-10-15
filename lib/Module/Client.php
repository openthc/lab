<?php
/**
 * (c) 2018 OpenTHC, Inc.
 * This file is part of OpenTHC Lab Portal released under GPL-3.0 License
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * Wraps all the Routing for the Client Module
 */

namespace App\Module;

class Client extends \OpenTHC\Module\Base
{
	function __invoke($a)
	{
		$a->get('', 'App\Controller\Client\Main');
		$a->map([ 'GET', 'POST'], '/{id}', 'App\Controller\Client\View');
	}
}
