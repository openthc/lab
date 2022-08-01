<?php
/**
 * Wraps all the Routing for the Transfer Module
 */

namespace OpenTHC\Lab\Module;

class Transfer extends \OpenTHC\Module\Base
{
	function __invoke($a)
	{
		$a->get('', 'OpenTHC\Lab\Controller\Transfer\Home');

		$a->get('/create', 'OpenTHC\Lab\Controller\Transfer\Create');
		$a->post('/create', 'OpenTHC\Lab\Controller\Transfer\Create:post');

		$a->map(['GET','POST'], '/sync', 'OpenTHC\Lab\Controller\Transfer\Sync');
		$a->map(['GET', 'POST'], '/{id}/sync', 'OpenTHC\Lab\Controller\Transfer\Sync');

		$a->get('/{id}', 'OpenTHC\Lab\Controller\Transfer\View');
		$a->post('/{id}', 'OpenTHC\Lab\Controller\Transfer\View');

		$a->get('/{id}/accept', 'OpenTHC\Lab\Controller\Transfer\Accept');
		$a->post('/{id}/accept', 'OpenTHC\Lab\Controller\Transfer\Accept:accept');

	}
}
