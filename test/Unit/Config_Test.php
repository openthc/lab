<?php
/**
 *
 */

namespace OpenTHC\Lab\Test\Unit;

class Config_Test extends \OpenTHC\Lab\Test\Base
{
	function test_config()
	{
		$key_list = [
			'database/auth/hostname',
			'database/auth/username',
			'database/auth/password',
			'database/auth/database',
			'database/main/hostname',
			'database/main/username',
			'database/main/password',
			'database/main/database',
			'openthc/dir/origin',
			'openthc/lab/origin',
			'openthc/lab/public',
			'openthc/lab/secret',
			// 'openthc/pipe/origin',
			'openthc/pub/origin',
			'openthc/pub/public',
		];

		foreach ($key_list as $k) {
			$chk = \OpenTHC\Config::get($k);
			$this->assertNotEmpty($chk, "Config '$k' not set");
		}

	}
}
