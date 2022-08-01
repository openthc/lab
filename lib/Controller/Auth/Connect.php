<?php
/**
 * Inbound Connection from Registered Application
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Auth;

use Edoceo\Radix\DB\SQL;

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
		if (!empty($_GET['r'])) {
			if (preg_match('/^\/(pub|result|share)\/\w+/', $_GET['r'])) {
				return $RES->withRedirect($_GET['r']);
			}
		}

		return $RES->withRedirect('/auth/init');

	}

	/**
	 * Handle a JWT based Connect
	 */
	function jwt($jwt, $RES)
	{
		// Auth Database Connection
		$cfg = \OpenTHC\Config::get('database/auth');
		if (empty($cfg)) {
			return $RES->withJSON([
				'data' => [],
				'meta' => [ 'detail' => 'Fatal Database Error [CAC-024]'],
			], 500);
		}

		$dbc_auth = new SQL(sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']), $cfg['username'], $cfg['password']);

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

		$_SESSION['OpenTHC']['app']['base'] = sprintf('https://%s', $App['code']);

		// Lookup Auth_Contact
		$sql = 'SELECT * FROM auth_contact WHERE id = :ct';
		$arg = [ ':ct' => $jwt['body']['sub'] ];
		$this->_Contact_Auth = $dbc_auth->fetchRow($sql, $arg);

		$this->_Company_Auth = $dbc_auth->fetchRow('SELECT * FROM auth_company WHERE id = :c0', [
			':c0' => $jwt['body']['company']
		]);

		if (empty($this->_Contact_Auth['id']) || empty($this->_Company_Auth['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Company or Contact [CAC-109]' ]
			], 403);
		}

		// Main Database Connection
		$cfg = \OpenTHC\Config::get('database/main');
		if (empty($cfg)) {
			return $RES->withJSON([
				'meta' => [ 'detail' => 'Fatal Database Error [CAC-125]'],
				'data' => [],
			], 500);
		}

		$dbc_main = new SQL(sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']), $cfg['username'], $cfg['password']);

		// Lookup Main Company
		$sql = 'SELECT * FROM company WHERE id = :c0';
		$Company = $dbc_main->fetchRow($sql, [ ':c0' => $this->_Company_Auth['id'] ]);
		if (empty($Company['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => sprintf('Invalid Company "%s" [CAC-067]', $this->_Company_Auth['id']) ],
			], 400);
		}

		// Lookup License
		$sql = 'SELECT * FROM license WHERE company_id = ? AND id = ?';
		$arg = array($Company['id'], $jwt['body']['license']);
		$License = $dbc_main->fetchRow($sql, $arg);
		if (empty($License['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => sprintf('Invalid License "%s" [CAC-076]', $jwt['body']['license']) ],
			], 400);
		}

		// Lookup Contact
		$sql = 'SELECT id, flag, email, phone FROM contact WHERE id = :ct0';
		$arg = [ ':ct0' => $this->_Contact_Auth['id'] ];
		$this->_Contact_Base = $dbc_main->fetchRow($sql, $arg);
		if (empty($this->_Contact_Base['id'])) {
			// Throw Error?
		}

		// Primary Objects
		$_SESSION['Contact'] = array_merge($this->_Contact_Base, $this->_Contact_Auth);
		$_SESSION['Company'] = $Company;
		$_SESSION['License'] = $License;

		return $RES->withRedirect('/auth/init?r=' . $_GET['r']);
	}

}
