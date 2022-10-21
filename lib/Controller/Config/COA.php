<?php
/**
 * Configure Metrics
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Config;

use OpenTHC\Lab\Lab_Metric;

class COA extends \OpenTHC\Lab\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{

		// Options
		$opt_list = [];
		$opt_list[] = 'coa/address/line/1';
		$opt_list[] = 'coa/address/line/2';
		$opt_list[] = 'coa/email';
		$opt_list[] = 'coa/phone';
		$opt_list[] = 'coa/website';
		$opt_list[] = 'coa/icon';
		$opt_list[] = 'coa/footer';

		$dbc = $this->_container->DBC_User;

		// Save It
		switch ($_POST['a']) {

			case 'config-coa-save':

				foreach ($opt_list as $k) {

					$dbc->query('INSERT INTO base_option (key, val) VALUES (:k0, :v1) ON CONFLICT (key) DO UPDATE SET val = :v1', [
						':k0' => $k,
						':v1' => json_encode($_POST[$k])
					]);

				}

				break;

		}

		$data = $this->loadSiteData();

		// $data['coa_list'] = [];
		// $data['coa_list'] = $this->_container->DBC_User->fetchAll('SELECT * FROM lab_layout');

		$sql = 'SELECT val FROM base_option WHERE key = :k0';

		foreach ($opt_list as $k) {
			$arg = [ ':k0' => $k ];
			$data[$k] = json_decode($dbc->fetchOne($sql, $arg), true);
		}

		return $RES->write( $this->render('config/coa.php', $data) );

	}

	/**
	 *
	 */
	function _verify_coa_template($f)
	{
		$mime = mime_content_type($f);
		switch ($mime) {
			case 'the right one':
			break;
			default:
				_exit_text("Invalid Mime: '$mime'");
		}

		// unzip

		// Load Content

		// Eval Content -- Look for some {} enclosed stuff

		// Save File to Good Location?

		return(true);

	}

}
