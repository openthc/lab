<?php
/**
 * Wraps all the Routing for the Result Module
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Module;

use Edoceo\Radix\DB\SQL;

class Result extends \OpenTHC\Module\Base
{
	function __invoke($a)
	{
		$a->get('', 'OpenTHC\Lab\Controller\Result\Main');

		// $a->map(['GET','POST'], '/sync', 'OpenTHC\Lab\Controller\Result\Sync');
		// $a->map(['GET','POST'], '/{id}/sync', 'OpenTHC\Lab\Controller\Result\Sync');

		$a->get('/create', 'OpenTHC\Lab\Controller\Result\Create');
		$a->post('/create', 'OpenTHC\Lab\Controller\Result\Update:post');

		$a->map(['GET','POST'], '/upload', 'OpenTHC\Lab\Controller\Result\Upload');
		$a->get('/upload/preview', 'OpenTHC\Lab\Controller\Result\Upload:preview');
		$a->map(['GET','POST'], '/upload/queue', 'OpenTHC\Lab\Controller\Result\Queue');

		$a->map([ 'GET', 'POST'], '/{id}', 'OpenTHC\Lab\Controller\Result\View');

		$a->get('/{id}/download', 'OpenTHC\Lab\Controller\Result\Download');

		$a->get('/{id}/update', 'OpenTHC\Lab\Controller\Result\Update');
		$a->post('/{id}/update', 'OpenTHC\Lab\Controller\Result\Update:post');

	}
}
