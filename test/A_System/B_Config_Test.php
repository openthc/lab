<?php
/**
 *
 */

namespace OpenTHC\Lab\Test\System;

class B_Config_Test extends \OpenTHC\Lab\Test\Base
{
	function test_sso()
	{
		$cfg = \OpenTHC\Config::get('openthc/sso');

		$key_list = [
			'hostname',
			'public',
			'secret',
		];

		foreach ($key_list as $k) {
			$this->assertArrayHasKey($cfg, $k, "SSO '$k' not set");
		}

	}
}
