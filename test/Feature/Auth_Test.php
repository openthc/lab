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
		$res = $this->auth();
	}

	/**
	 * @test
	 * @depends open
	 */
	function view_some_pages()
	{
		$res = $this->auth();

		$res = $this->httpClient->get('/sample');
		$this->assertValidResponse($res, 200, 'text/html');

		$res = $this->httpClient->get('/result');
		$this->assertValidResponse($res, 200, 'text/html');

		$res = $this->httpClient->get('/report');
		$this->assertValidResponse($res, 200, 'text/html');

		$res = $this->httpClient->get('/api/sample/four_zero_four');
		$this->assertValidResponse($res, 400, 'text/html');

	}

}
