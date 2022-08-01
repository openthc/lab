<?php
/**
 * Configure External Integrations
 */

namespace OpenTHC\Lab\Controller\Config;

class External extends \OpenTHC\Lab\Controller\Base
{
	const CONFIG_KEY = 'qbench-auth';

	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->loadSiteData();
		$data['Page']['title'] = 'Config :: External';

		$dbc = $this->_container->DBC_User;

		// Save iT?
		switch ($_POST['a']) {
			case 'qbench-save':

				$chk = $dbc->fetchOne('SELECT id FROM base_option WHERE key = :k', [ ':k' => self::CONFIG_KEY ]);
				if (empty($chk)) {
					$dbc->insert('base_option', [
						'id' => _ulid(),
						'key' => self::CONFIG_KEY,
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

		$cfg = $dbc->fetchOne('SELECT val FROM base_option WHERE key = :k', [ ':k' => self::CONFIG_KEY ]);
		$cfg = json_decode($cfg, true);
		$data = [
			'qbench-server-url' => $cfg['server-url'],
			'qbench-public-key' => $cfg['public-key'],
			'qbench-secret-key' => $cfg['secret-key'],
		];

		return $RES->write( $this->render('config/external.php', $data) );

	}
}
