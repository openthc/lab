<?php
/**
 *
 */

namespace OpenTHC\Lab\Test\Unit;

class Config_Test extends \OpenTHC\Lab\Test\Base
{
	function test_sso()
	{
		$cfg = \OpenTHC\Config::get('openthc/sso');

		$key_list = [
			'origin',
			'public',
			'secret',
		];

		foreach ($key_list as $k) {
			$this->assertArrayHasKey($k, $cfg, "SSO '$k' not set");
		}

	}
}
