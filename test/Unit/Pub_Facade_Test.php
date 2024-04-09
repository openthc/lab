<?php
/**
 *
 */

namespace OpenTHC\Lab\Test\Unit;

class Pub_Facade_Test extends \OpenTHC\Lab\Test\Base {

	/**
	 * @test
	 */
	function class_load()
	{
		$pub_origin = \OpenTHC\Config::get('openthc/pub/origin');
		$this->assertNotEmpty($pub_origin);

		$pub = new \OpenTHC\Lab\Facade\Pub();

		// Reflection?
		// $this->assertClassHasAttribute($x, 'client_pk');
		$this->assertObjectHasProperty('cfg', $pub);

		$url = $pub->getURL('/lab/test/file.txt');
		$this->assertNotEmpty($url);
		$this->assertEquals($pub_origin . '/t7Zt4Ko6Hc0jUxt7ns-24q6NILnuXXRSXcBPj7PLfT0/file.txt', $url);

	}


}
