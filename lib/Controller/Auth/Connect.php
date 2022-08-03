<?php
/**
 * Inbound Connection from Registered Application
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Auth;

class Connect extends \OpenTHC\Controller\Auth\Connect
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		if ( ! empty($_GET['jwt'])) {

			$jwt = explode('.', $_GET['jwt']);
			$jwt = array_combine([ 'head_b64', 'body_b64', 'sign_b64' ], $jwt);
			$jwt['head'] = json_decode(__base64_decode_url($jwt['head_b64']), true);
			$jwt['body'] = json_decode(__base64_decode_url($jwt['body_b64']), true);
			$jwt['sign'] = __base64_decode_url($jwt['sign_b64']);

			return $this->jwt($jwt, $RES);

		}

		$RES = parent::__invoke($REQ, $RES, $ARG);

		$x = $RES->getStatusCode();
		switch ($x) {
		case 200:
		case 301:
		case 302:
			// OK
			break;
		default:
			return $RES;
			// $buf = $RES->getBody()->getContents();
			_exit_text(sprintf('Invalid Session State "%d" [CAC-022]', $x), 500);
			return $RES;
		}

		// Action
		switch ($_GET['action']) {
		case 'share-transfer':

			throw new \Exception('Invalid Request');
			// @todo should this be _connect_info ?
			$out_link = '/transfer/import/' . $tmp_auth['transfer']['guid'];

			break;

		case 'share':
		case 'share-all': // @deprecated
		case 'share-one': // @deprecated

			$_SESSION['intent'] = 'share-all';

			if (!empty($this->_connect_info['lab-result'])) {
				$x = $this->_connect_info['lab-result']['guid'];
				if (!empty($x)) {
					$_SESSION['intent'] = 'share-one';
					$_SESSION['intent-data'] = $x;
				}

			}
			break;

		}

		// User Specifed Redirect?
		return $RES->withRedirect('/auth/init?' . http_build_query([
			'r' => $_GET['r']
		]));

	}

	/**
	 * Handle a JWT based Connect
	 */
	function jwt($jwt, $RES)
	{
		// Auth Database Connection
		$dbc_auth = _dbc('auth');

		// Lookup Program
		$sql = 'SELECT * FROM auth_service WHERE code = ?';
		$arg = array($jwt['body']['iss']);
		$App = $dbc_auth->fetchRow($sql, $arg);
		if (empty($App['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Service [CAC-106]'],
			], 400);
		}

		// Only Live Service
		if (200 != $App['stat']) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Service [CAC-114]'],
			], 400);
		}

		// Verify Signature
		$sig1 = hash_hmac('sha256', sprintf('%s.%s', $jwt['head_b64'], $jwt['body_b64']), $App['hash'], true);
		if ( ! hash_equals($jwt['sign'], $sig1)) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Signature [CAC-123]'],
			], 400);
		}

		$_SESSION = [
			'OpenTHC' => [
				'app' => [
					'base' => sprintf('https://%s', $App['code']),
				]
			],
			'Contact' => [
				'id' => $jwt['body']['sub']
			],
			'Company' => [
				'id' => $jwt['body']['company'],
			],
			'License' => [
				'id' => $jwt['body']['license'],
			]
		];

		return $RES->withRedirect('/auth/init?r=' . $_GET['r']);

	}

}
