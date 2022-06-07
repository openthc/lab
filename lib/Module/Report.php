<?php
/**
 * Wraps all the Routing for the Result Module
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Module;

use Edoceo\Radix\DB\SQL;

class Report extends \OpenTHC\Module\Base
{
	function __invoke($a)
	{
		$a->get('', 'OpenTHC\Lab\Controller\Report\Main');

		// $a->map(['GET','POST'], '/sync', 'App\Controller\Result\Sync');
		// $a->map(['GET','POST'], '/{id}/sync', 'App\Controller\Result\Sync');

		// $a->get('/create', 'App\Controller\Result\Create');
		// $a->post('/create', 'App\Controller\Result\Create:save');

		// $a->map(['GET','POST'], '/upload', 'App\Controller\Result\Upload');
		// $a->get('/upload/preview', 'App\Controller\Result\Upload:preview');
		// $a->map(['GET','POST'], '/upload/queue', 'App\Controller\Result\Queue');

		$a->map([ 'GET', 'POST'], '/{id}', 'OpenTHC\Lab\Controller\Report\Single');

		// $a->get('/{id}/download', 'App\Controller\Result\Download');

		// $a->get('/{id}/update', 'App\Controller\Result\Update');
		// $a->post('/{id}/update', 'App\Controller\Result\Update:post');

	}
}
