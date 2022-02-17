<?php
/**
 * Configure Instrument
 */

namespace App\Controller\Config;

class Instrument extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->loadSiteData();
		$data['Page']['title'] = 'Config :: Instruments';

		return $RES->write( $this->render('config/instrument.php', $data) );

	}
}
