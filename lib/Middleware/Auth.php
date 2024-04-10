<?php
/**
 * Authentication Middleware
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Middleware;

use OpenTHC\Company;
use OpenTHC\Contact;

class Auth extends \OpenTHC\Middleware\Base
{
	public function __invoke($REQ, $RES, $NMW)
	{
		// If we have a valid session, use that
		if ( ! empty($_SESSION['Company']['id'])
			&& ! empty($_SESSION['Contact']['id'])
			&& ! empty($_SESSION['License']['id'])
			) {
			return $NMW($REQ, $RES);
		}

		$auth = trim($_SERVER['HTTP_AUTHORIZATION']);

		$chk = preg_match('/^Bearer (.+)$/', $auth, $m) ? $m[1] : null;
		if ( ! empty($chk)) {
			$RES = $this->_bearer($REQ, $RES, $chk);
			if (200 == $RES->getStatusCode()) {
				return $NMW($REQ, $RES);
			}
		}

		$type_want = strtok($_SERVER['HTTP_ACCEPT'], ',');
		if ('text/html' == $type_want) {
			_exit_html_warn('Access Denied [AMA-042]', 403);
		}

		return $RES->withJSON(array(
			'data' => [],
			'meta' => [ 'note' => 'Access Denied [AMA-042]' ],
		), 403);

		return $RES;

	}

	/**
	 * @param $RES Response
	 */
	protected function _bearer($REQ, $RES, $tok)
	{
		$dbc = $this->_container->DBC_Auth;

		// Find Directly Supplied Hash
		$sql = 'SELECT * FROM auth_context_ticket WHERE id = :a1';
		$act = $dbc->fetchRow($sql, [ ':a1' => $tok ]);
		if (empty($act['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'note' => 'Bearer Token Not Valid [AMA-147]' ],
			], 403);
		}

		$act = json_decode($act['meta'], true);
		// if ('lab' == $data['scope']) {
		// 	return $RES;
		// }

		// Set on Session?
		$Contact = new Contact($dbc, $act['contact_id']);
		$Company = new Company($dbc, $act['company_id']);
		// $License = new Company($dbc, $res['company_id']);

		// if (empty($Company['id'])) {
		// 	return $RES->withJSON(array(
		// 		'status' => 'failure',
		// 		'detail' => 'MWA#068: Invalid Auth',
		// 		// '_res' => $res,
		// 	), 403);
		// }

		return $RES;
	}

}
