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
	function get($u)
	{
		$res = $this->httpClient->get($u);
		return $res;
	}


	/**
	*/
	protected function post($u, $a)
	{
		$res = $this->httpClient->post($u, [ 'form_params' => $a ]);
		return $res;
	}


	/**
	*/
	protected function post_json($u, $a)
	{
		$res = $this->httpClient->post($u, [ 'json' => $a ]);
		return $res;
	}

}
