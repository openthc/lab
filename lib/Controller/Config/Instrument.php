<?php
/**
 * Configure Instrument
 */

namespace OpenTHC\Lab\Controller\Config;

class Instrument extends \OpenTHC\Lab\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->loadSiteData();
		$data['Page']['title'] = 'Config :: Instruments';

		return $RES->write( $this->render('config/instrument.php', $data) );

	}
}
