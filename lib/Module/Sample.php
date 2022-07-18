<?php
/**
 * Wraps all the Routing for the Sample Module
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace App\Module;

class Sample extends \OpenTHC\Module\Base
{
	function __invoke($a)
	{
		$a->get('', 'App\Controller\Sample\Main');

		$a->map(['GET','POST'], '/create', 'App\Controller\Sample\Create');

		$a->map(['GET','POST'], '/sync', 'App\Controller\Sample\Sync');
		$a->map(['GET', 'POST'], '/{id}/sync', 'App\Controller\Sample\Sync');

		$a->get('/{id}.png', 'App\Controller\Sample\View:image');
		$a->get('/{id}.jpeg', 'App\Controller\Sample\View:image');
		// $a->get('/{id}/media', 'App\Controller\Sample\Media');

		$a->get('/{id}', 'App\Controller\Sample\View');
		$a->post('/{id}', 'App\Controller\Sample\View');

	}
}
