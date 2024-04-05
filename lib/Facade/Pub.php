<?php
/**
 * Pub Facade Helper
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Facade;

class Pub extends \OpenTHC\Service\Pub
{
	function __construct()
	{
		$cfg = [
			'service' => OPENTHC_SERVICE_ID,
			'contact' => $_SESSION['Contact']['id'],
			'company' => $_SESSION['Company']['id'],
			'license' => $_SESSION['License']['id'],
			'client-pk' => \OpenTHC\Config::get('openthc/lab/public'),
			'client-sk' => \OpenTHC\Config::get('openthc/lab/secret'),
		];

		parent::__construct($cfg);

	}

}
