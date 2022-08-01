<?php
/**
 * Authentication Middleware
 */

namespace OpenTHC\Lab\Middleware;

use OpenTHC\Company;
use OpenTHC\Contact;

class Auth extends \OpenTHC\Middleware\Base
{
	public function __invoke($REQ, $RES, $NMW)
	{
		// If we have a valid session, use that
		if (!empty($_SESSION['Contact']['id'])) {
			return $NMW($REQ, $RES);
		}

		$auth = trim($_SERVER['HTTP_AUTHORIZATION']);

		$chk = preg_match('/^basic (.+)$/i', $auth, $m) ? $m[1] : null;
		if (!empty($chk)) {
			$RES = $this->_basic($REQ, $RES, $chk);
			if (200 == $RES->getStatusCode()) {
				return $NMW($REQ, $RES);
			}
		}

		$chk = preg_match('/^bearer (.+)$/i', $auth, $m) ? $m[1] : null;
		if (!empty($chk)) {
			$RES = $this->_bearer($REQ, $RES, $chk);
			if (200 == $RES->getStatusCode()) {
				return $NMW($REQ, $RES);
			}
		}

		// return $RES->withJSON(array(
		// 	'data' => [],
		// 	'meta' => [ 'detail' => '' ],
		// ), 403);

		return $RES;

	}

	/**
		@param $RES Response
		@param $tok The Basic Token
	*/
	protected function _basic($REQ, $RES, $tok)
	{
		$tok = base64_decode($tok, true);

		if (empty($tok)) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Authentication [AMA#104]' ]
			], 403);
		}

		// Basic Token should be Two Parts, which may also be in these PHP vars
		$service_key = trim($_SERVER['PHP_AUTH_USER']);
		$company_key = trim($_SERVER['PHP_AUTH_PW']);

		$dbc = $this->_container->DBC_Auth;

		// Should be a Software Vendor
		$sql = 'SELECT * FROM auth_context_ticket WHERE id = ?';
		$arg = array($service_key);
		$res = $dbc->fetchRow($sql, $arg);
		if (empty($res['id'])) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'Invalid Authentication [AMA#119]'
			), 403);
		}
		$data = json_decode($res['json'], true);
		$Company = new Company($dbc, $data['company_id']);
		if (empty($Company['id'])) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'Invalid Authentication [AMA#127]'
			), 403);
		}

		$REQ = $REQ->withAttribute('Company_Vendor', $Company);

		// Should be a Licensed Operator
		$sql = 'SELECT * FROM auth_context_ticket WHERE id = ?';
		$arg = array($company_key);
		$res = $dbc->fetchRow($sql, $arg);
		if (empty($res['id'])) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'Invalid Authentication [AMA#140]'
			), 403);
		}
		$data = json_decode($res['json'], true);
		$Company = new Company($dbc, $data['company_id']);

		if (empty($Company['id'])) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'Invalid Authentication [AMA#149]'
			), 403);
		}

		$REQ = $REQ->withAttribute('Company_Client', $Company);

	}

	/**
		@param $RES Response
	*/
	protected function _bearer($REQ, $RES, $tok)
	{
		$dbc = $this->_container->DBC_Auth;

		// Find Directly Supplied Hash
		$res = $dbc->fetchRow('SELECT * FROM auth_context_ticket WHERE id = :hash', array($tok));
		if (!empty($res)) {

			$data = json_decode($res['meta'], true);
			if ('lab' == $data['scope']) {
				return $RES;
			}
			// $Company = new Company($dbc, $res['company_id']);
			// $Contact = new Contact($dbc, $res['uid']);

			// if (empty($Company['id'])) {
			// 	return $RES->withJSON(array(
			// 		'status' => 'failure',
			// 		'detail' => 'MWA#068: Invalid Auth',
			// 		// '_res' => $res,
			// 	), 403);
			// }

			// if (empty($Contact['id'])) {

			// $REQ = $REQ->withAttribute('Company', $Company);
			// $REQ = $REQ->withAttribute('Contact', $Contact);
		}

		return $RES->withJSON([
			'data' => null,
			'meta' => 'Bearer Token Not Valid [AMA#147]',
		], 403);

	}

}
