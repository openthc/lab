<?php
/**
 * oAuth2 Returns Here
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace App\Controller\Auth\oAuth2;

use Edoceo\Radix\Session;

class Back extends \OpenTHC\Controller\Auth\oAuth2
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$p = $this->getProvider();

		if (empty($_GET['code'])) {
			_exit_text('Invalid Request [AOB-017]', 400);
		}

		// Check State
		$this->checkState();

		// Try to get an access token using the authorization code grant.
		$tok = null;
		try {
			$tok = $p->getAccessToken('authorization_code', [
				'code' => $_GET['code']
			]);
		} catch (\Exception $e) {
			_exit_html('<h1>Invalid Access Token [CAB-030]</h1><p>Please to to <a href="/auth/shut?r=/auth/open">sign-in again</a>.</p>', 400);
		}

		if (empty($tok)) {
			_exit_html('<h1>Invalid Access Token [CAB-034]</h1><p>Please to to <a href="/auth/shut?r=/auth/open">sign-in again</a>.</p>', 400);
		}

		// Token Data Verify
		$x = json_decode(json_encode($tok), true);
		if (empty($x['access_token'])) {
			_exit_html('<h1>Invalid Access Token [CAB-041]</h1><p>Please to to <a href="/auth/shut?r=/auth/open">sign-in again</a>.</p>', 400);
		}
		if (empty($x['token_type'])) {
			_exit_html('<h1>Invalid Access Token [CAB-045]</h1><p>Please to to <a href="/auth/shut?r=/auth/open">sign-in again</a>.</p>', 400);
		}

		// Using the access token, we may look up details about the
		// resource owner.
		try {

			$x = $p->getResourceOwner($tok);
			$Profile = $x->toArray();

			$_SESSION['Contact'] = $Profile['Contact'];
			$_SESSION['Company'] = $Profile['Company'];
			$_SESSION['License'] = $Profile['License'][0];

			$dbc_auth = $this->_container->DBC_Auth;
			$_SESSION['dsn'] = $dbc_auth->fetchOne('SELECT dsn FROM auth_company WHERE id = :c', [ ':c' => $_SESSION['Company']['id'] ]);

			Session::flash('info', sprintf('Signed in as: %s', $_SESSION['Contact']['username']));

		} catch (\Exception $e) {

			unset($_SESSION['dsn']);
			unset($_SESSION['Company']);
			unset($_SESSION['License']);
			unset($_SESSION['Contact']);

			__exit_html('<h1>Authentication Exception [CAB-066]</h1><p>Please try to <a href="/auth/shut?r=/auth/open">sign-in again</a>.</p>', 500);

		}

		// Redirect to Init
		return $RES->withRedirect(sprintf('/auth/init?%s', http_build_query([
			'r' => $_GET['r']
		])));

	}

	/**
	 * Attempt to Connect via PIPE
	 */
	function connectCRE()
	{
		if (empty($_SESSION['Contact']['meta']['cre'])) {
			return(null);
		}

		$_SESSION['cre'] = $_SESSION['Contact']['meta']['cre'];
		$_SESSION['cre-auth'] = $_SESSION['Contact']['meta']['cre-auth'];

		try {
			// Authenticate via PIPE
			$cre = new \OpenTHC\CRE();
			$cfg = array(
				'cre' => $_SESSION['cre'],
				'license' => $_SESSION['cre-auth']['license'],
				'license-key' => $_SESSION['cre-auth']['license-key'],
			);
			$res = $cre->auth($cfg);
			if (!empty($res['data'])) {
				$_SESSION['pipe-token'] = $res['data'];
			} else {
				_exit_text('CRE Connection Failure. Please contact support [AOB-092]', 500);
			}

		} catch (\Exception $e) {
			unset($_SESSION['cre']);
			unset($_SESSION['cre-auth']);
		}

	}

}
