<?php
/**
 * Pub Facade Helper
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Facade;

class Webhook
{
	private $url_origin;

	function __construct()
	{
		$url_origin = \OpenTHC\Config::get('webhook/origin');

		// $cfg = [
		// 	'service' => OPENTHC_SERVICE_ID,
		// 	'contact' => $_SESSION['Contact']['id'],
		// 	'company' => $_SESSION['Company']['id'],
		// 	'license' => $_SESSION['License']['id'],
		// 	'client-pk' => \OpenTHC\Config::get('openthc/lab/public'),
		// 	'client-sk' => \OpenTHC\Config::get('openthc/lab/secret'),
		// ];

		// parent::__construct($cfg);

	}

	/**
	 * Send a POST w/JSON and ignores the response
	 */
	function post(string $path, $body=null) : void {

		if (empty($this->url_origin)) {
			return;
		}

		$path = ltrim($path, '/');

		$url = sprintf('%s/%s', $this->url_origin, $path);
		$req = _curl_init($url);

		// Header
		$head = array(
			// sprintf('Authorization: Bearer %s', $this->_tok['access']),
			'content-type: application/json'
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $head);

		// Body
		if ( ! is_string($body)) {
			$body = json_encode($body);
		}
		curl_setopt($req, CURLOPT_POST, true);
		curl_setopt($req, CURLOPT_POSTFIELDS, $body);

		$res = curl_exec($req);

	}

}
