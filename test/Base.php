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
		$cfg = [
			'base_uri' => sprintf('%s/api', OPENTHC_TEST_ORIGIN),
			'headers' => [
				'authorization' => sprintf('Bearer ...'),
			]
		];

		$c = $this->getGuzzleClient($cfg);

		return $c;

	}


	/**
	*/
	protected function auth(string $p = null, string $c = null, string $l = null) {

		$res = $this->httpClient->get('/auth/open');
		$this->assertEquals(302, $res->getStatusCode());
		$this->assertNotEmpty($res->getHeaderLine('location'));
		$loc = $res->getHeaderLine('location');

		//
		$tmp = parse_url($loc);
		$this->assertNotEmpty($tmp['scheme']);
		$this->assertNotEmpty($tmp['host']);
		$sso_origin = sprintf('%s://%s', $tmp['scheme'], $tmp['host']);
		$sso_client = $this->getGuzzleClient([
			'base_uri' => $sso_origin,
		]);

		$tmp = parse_url($loc);

		// echo "\nget1($loc)\n";
		$res = $sso_client->get($loc);
		$this->assertEquals(302, $res->getStatusCode());
		$this->assertNotEmpty($res->getHeaderLine('location'));
		$loc = $res->getHeaderLine('location');

		// echo "\nget2($loc)\n";
		// resolve to full URL based on above $loc
		$res = $sso_client->get($loc);
		$this->assertEquals(200, $res->getStatusCode());

		$res_html = $this->assertValidResponse($res, 200, 'text/html');
		// echo "\nres_html:$res_html\n";

		$tmp_csrf = preg_match('/name="CSRF".+?hidden.+?value="([\w\-]+)">/', $res_html, $m) ? $m[1] : '';
		// var_dump($tmp_csrf);

		$res = $sso_client->post($loc, [ 'form_params' => [
			'CSRF' => $tmp_csrf,
			'username' => OPENTHC_TEST_CONTACT_A_USERNAME,
			'password' => OPENTHC_TEST_CONTACT_A_PASSWORD,
			'a' => 'account-open',
		]]);

		$this->assertValidResponse($res, 302, 'text/html');
		$this->assertNotEmpty($res->getHeaderLine('location'));
		$loc = $res->getHeaderLine('location');
		// var_dump($loc);

		// echo "\nget3($loc)\n";
		$this->assertMatchesRegularExpression('/auth\/init/', $loc);
		$res = $sso_client->get($loc);
		$res_html = $this->assertValidResponse($res, 300, 'text/html');
		$tmp_csrf = preg_match('/name="CSRF".+?hidden.+?value="([\w\-]+)">/', $res_html, $m) ? $m[1] : '';

		$res = $sso_client->post($loc, [ 'form_params' => [
			'CSRF' => $tmp_csrf,
			'company_id' => OPENTHC_TEST_COMPANY_A,
		]]);
		$this->assertValidResponse($res, 302, 'text/html');
		$this->assertNotEmpty($res->getHeaderLine('location'));
		$loc = $res->getHeaderLine('location');

		// $this->assertNotEmpty($res->getHeaderLine('location'));
		// $loc = $res->getHeaderLine('location');

		$this->assertMatchesRegularExpression('/oauth2\/authorize/', $loc);
		// echo "\nget4($loc)\n";
		$res = $sso_client->get($loc);
		$res_html = $this->assertValidResponse($res, 200, 'text/html');
		$loc = preg_match('/id="oauth2-authorize-permit".+?href="(.+?)".+title="Yes/ms', $res_html, $m) ? $m[1] : '';
		$this->assertNotEmpty($loc);

		// echo "\nget5($loc)\n";
		$res = $sso_client->get($loc);
		$res_html = $this->assertValidResponse($res, 200, 'text/html');
		$loc = preg_match('/id="oauth2-permit-continue".+?href="(.+?)"/ms', $res_html, $m) ? $m[1] : '';
		$this->assertNotEmpty($loc);

		// Should be back to Lab
		// echo "\nget6($loc)\n";
		$res = $this->httpClient->get($loc);
		$this->assertValidResponse($res, 302, 'text/html');
		$this->assertNotEmpty($res->getHeaderLine('location'));
		$loc = $res->getHeaderLine('location');
		$this->assertNotEmpty($loc);

		// echo "\nget7($loc)\n";
		$res = $this->httpClient->get($loc);
		$this->assertValidResponse($res, 302, 'text/html');
		$this->assertNotEmpty($res->getHeaderLine('location'));
		$loc = $res->getHeaderLine('location');
		$this->assertNotEmpty($loc);

		// echo "\nget8($loc)\n";
		$res = $this->httpClient->get($loc);
		$this->assertValidResponse($res, 200, 'text/html');

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
