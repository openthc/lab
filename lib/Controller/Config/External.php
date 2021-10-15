<?php
/**
 * Configure External Integrations
 */

namespace App\Controller\Config;

class External extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->loadSiteData();
		$data['Page']['title'] = 'Config :: External';

		// Save iT?
		switch ($_POST['a']) {
			case 'qbench-save':
				$dbc = $this->_container->DBC_User;

				$chk = $dbc->fetchOne('SELECT id FROM base_option WHERE key = :k', [ ':k' => 'qbench-api' ]);
				if (empty($chk)) {
					$dbc->insert('base_option', [
						'id' => _ulid(),
						'key' => 'qbench-api',
						'val' => json_encode([
							'server-url' => $_POST['qbench-server-url']
							, 'public-key' => $_POST['qbench-public-key']
							, 'secret-key' => $_POST['qbench-secret-key']
						]),
					]);
				} else {
					$update['val'] = json_encode([
						'server-url' => $_POST['qbench-server-url']
						, 'public-key' => $_POST['qbench-public-key']
						, 'secret-key' => $_POST['qbench-secret-key']
					]);
					$filter = [
						'id' => $chk
					];
					$dbc->update('base_option', $update, $filter);
				}

				return $RES->withRedirect('/config');

		}

		return $RES->write( $this->render('config/external.php', $data) );

	}
}
