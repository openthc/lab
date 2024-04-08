<?php
/**
 *
 */

namespace OpenTHC\Lab\Test;

class Base extends \OpenTHC\Test\Base {

	protected $httpClient; // API Guzzle Client

	protected function setUp() : void
	{
		$this->httpClient = $this->_api();
	}

	/**
	*/
	protected function _api()
	{
		// $c = $this->getGuzzleClient(OPENTHC_TEST_ORIGIN);

		// create our http client (Guzzle)
		$c = new \GuzzleHttp\Client(array(
			'base_uri' => OPENTHC_TEST_ORIGIN,
			'allow_redirects' => false,
			'debug' => defined('OPENTHC_TEST_HTTP_DEBUG'),
			'request.options' => array(
				'exceptions' => false,
			),
			'http_errors' => false,
			'cookies' => true,
		));

		return $c;
	}


	/**
	*/
	protected function auth(string $p = null, string $c = null, string $l = null) {

		$res = $this->httpClient->get('/auth/open');
		$this->assertEquals(302, $res->getStatusCode());
		$this->assertNotEmpty($res->getHeaderLine('location'));
		$loc = $res->getHeaderLine('location');

		$res = $this->httpClient->get($loc);
		$this->assertEquals(302, $res->getStatusCode());
		$this->assertNotEmpty($res->getHeaderLine('location'));
		$loc = $res->getHeaderLine('location');

		$res = $this->httpClient->get($loc);
		$this->assertEquals(200, $res->getStatusCode());

		$res_html = $this->assertValidResponse($res, 200, 'text/html');

		$res = $this->httpClient->post($loc, [ 'form_params' => [
			'CSRF' => (preg_match('/CSRF.+/', $res_html, $m) ? $m[1] : ''),
			'username' => OPENTHC_TEST_CONTACT_A_USERNAME,
			'password' => OPENTHC_TEST_CONTACT_A_PASSWORD,
			'a' => 'account-open',
		]]);

		// var_dump($res);
		// exit;
		// , $body = [
		// 	'form_params' => [
		// 		'service' => $p ?: OPENTHC_TEST_SERVICE_A,
		// 		'company' => $c ?: OPENTHC_TEST_COMPANY_A,
		// 		'license' => $l ?: OPENTHC_TEST_LICENSE_A,
		// 	],
		// ]);

		$this->assertValidResponse($res);

	}

	/**
	*/
	function get($u)
	{
		$res = $this->httpClient->get($u);
		return $res;
	}


	/**
	*/
	protected function _post($u, $a)
	{
		$res = $this->httpClient->post($u, [ 'form_params' => $a ]);
		return $res;
	}


	/**
	*/
	protected function _post_json($u, $a)
	{
		$res = $this->httpClient->post($u, [ 'json' => $a ]);
		return $res;
	}

}
