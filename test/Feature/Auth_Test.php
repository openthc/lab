<?php
/**
 *
 */

namespace OpenTHC\Lab\Test\Feature;

class Auth_Test extends \OpenTHC\Lab\Test\Base {

	/**
	 * @test
	 */
	function open()
	{
		$res = $this->authViaSSO(OPENTHC_TEST_CONTACT_A_USERNAME, OPENTHC_TEST_CONTACT_A_PASSWORD, OPENTHC_TEST_COMPANY_A);

		return $this->httpClient;
	}

	/**
	 * @test
	 * @depends open
	 */
	function view_some_pages($authClient)
	{
		// Doesn't Keep the Session from Before?
		$res = $authClient->get('/sample');
		$this->assertValidResponse($res, 200, 'text/html');

		$res = $authClient->get('/result');
		$this->assertValidResponse($res, 200, 'text/html');

		$res = $authClient->get('/report');
		$this->assertValidResponse($res, 200, 'text/html');

		$res = $authClient->get('/api/sample/four_zero_four');
		$this->assertValidResponse($res, 404, 'text/html');

	}

}
