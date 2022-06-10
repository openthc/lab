<?php
/**
 * Wraps all the Routing for the Result Module
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace App\Module;

use Edoceo\Radix\DB\SQL;

class Result extends \OpenTHC\Module\Base
{
	function __invoke($a)
	{
		$a->get('', 'App\Controller\Result\Main');

		// $a->map(['GET','POST'], '/sync', 'App\Controller\Result\Sync');
		// $a->map(['GET','POST'], '/{id}/sync', 'App\Controller\Result\Sync');

		$a->get('/create', 'App\Controller\Result\Create');
		$a->post('/create', 'App\Controller\Result\Create:save');

		$a->map(['GET','POST'], '/upload', 'App\Controller\Result\Upload');
		$a->get('/upload/preview', 'App\Controller\Result\Upload:preview');
		$a->map(['GET','POST'], '/upload/queue', 'App\Controller\Result\Queue');

		$a->map([ 'GET', 'POST'], '/{id}', 'App\Controller\Result\View');

		$a->get('/{id}/download', 'App\Controller\Result\Download');

		$a->get('/{id}/update', 'App\Controller\Result\Update');
		$a->post('/{id}/update', 'App\Controller\Result\Update:post');

	}
}
