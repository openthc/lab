<?php
/**
 * Configure Metrics
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace App\Controller\Config;

use App\Lab_Metric;

class COA extends \App\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{

		// getPath?

		// Examine COA File
		switch ($_POST['a']) {
			case 'config-coa-save':

				var_dump($_POST);

				exit;


				// Company Option?

			case 'save':
				var_dump($_POST);
				var_dump($_FILES);
				exit(0);
		}

		$data = $this->loadSiteData();

		$data['coa_list'] = [];
		$data['coa_list'] = $this->_container->DBC_User->fetchAll('SELECT * FROM lab_layout');

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
