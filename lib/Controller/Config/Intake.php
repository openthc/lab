<?php
/**
 * Configure Metrics
 */

namespace App\Controller\Config;

use App\Lab_Metric;

class Intake extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->loadSiteData();
		$data['menu'] = $this->_container->view['menu'];
		$data['Page']['title'] = 'Config :: Intake';

		$data['intake_link'] = sprintf('https://%s/intake?c=%s&amp;l=%s'
			, $_SERVER['SERVER_NAME']
			, $_SESSION['Company']['id']
			, $_SESSION['License']['id']
		);

		return $RES->write( $this->render('config/intake', $data) );

	}
}
