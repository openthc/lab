<?php
/**
 * Sample Intake Portal
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Config;

use OpenTHC\Lab\Lab_Metric;

class Intake extends \OpenTHC\Lab\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->loadSiteData();
		$data['Page']['title'] = 'Config :: Intake';

		$data['intake_link'] = sprintf('https://%s/intake?c=%s&l=%s'
			, $_SERVER['SERVER_NAME']
			, $_SESSION['Company']['id']
			, $_SESSION['License']['id']
		);

		return $RES->write( $this->render('config/intake', $data) );

	}
}
