<?php
/**
 * Wraps all the Routing for the Sample Module
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Module;

class Sample extends \OpenTHC\Module\Base
{
	function __invoke($a)
	{
		$a->get('', 'OpenTHC\Lab\Controller\Sample\Main');

		$a->map(['GET','POST'], '/create', 'OpenTHC\Lab\Controller\Sample\Create');

		$a->map(['GET','POST'], '/sync', 'OpenTHC\Lab\Controller\Sample\Sync');
		$a->map(['GET', 'POST'], '/{id}/sync', 'OpenTHC\Lab\Controller\Sample\Sync');

		$a->get('/{id}.png', 'OpenTHC\Lab\Controller\Sample\View:image');
		$a->get('/{id}.jpeg', 'OpenTHC\Lab\Controller\Sample\View:image');
		// $a->get('/{id}/media', 'OpenTHC\Lab\Controller\Sample\Media');

		$a->get('/{id}', 'OpenTHC\Lab\Controller\Sample\View');
		$a->post('/{id}', 'OpenTHC\Lab\Controller\Sample\View');

	}
}
