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

	function test_defined()
	{
		$key_list = [
			'OPENTHC_TEST_ORIGIN',
			'OPENTHC_TEST_SERVICE_A',
			'OPENTHC_TEST_COMPANY_A',
			'OPENTHC_TEST_LICENSE_A',
			'OPENTHC_TEST_CONTACT_A',
			'OPENTHC_TEST_CONTACT_A_USERNAME',
			'OPENTHC_TEST_CONTACT_A_PASSWORD',
		];

		foreach ($key_list as $k) {
			$this->assertTrue(defined($k), "CONST '$k' is not defined");
			$this->assertNotEmpty(constant($k), "CONST '$k' is empty");
		}
	}

}
