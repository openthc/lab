<?php
/**
 * oAuth2 Lands Back Here
 */

namespace App\Controller\Auth\oAuth2;

use Edoceo\Radix;
use Edoceo\Radix\Session;

class Back extends \OpenTHC\Controller\Auth\oAuth2
{
	function __invoke($REQ, $RES, $ARG)
	{
		$p = $this->getProvider();

		if (empty($_GET['code'])) {
			_exit_text('Invalid Request [CAB-019]', 400);
		}

		// Check State
		$this->checkState();

		// Try to get an access token using the authorization code grant.
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

		// Array-ify
		$tok_a = json_decode(json_encode($tok), true);

		if (empty($tok_a['access_token'])) {
			_exit_html('<h1>Invalid Access Token [CAB-041]</h1><p>Please to to <a href="/auth/shut?r=/auth/open">sign-in again</a>.</p>', 400);
		}

		if (empty($tok_a['token_type'])) {
			_exit_html('<h1>Invalid Access Token [CAB-045]</h1><p>Please to to <a href="/auth/shut?r=/auth/open">sign-in again</a>.</p>', 400);
		}

		// Using the access token, we may look up details about the
		// resource owner.
		try {

			$x = $p->getResourceOwner($tok);
			$x = $x->toArray();

			$_SESSION['Company'] = $x['Company'];
			$_SESSION['License'] = $x['License'][0];
			$_SESSION['Contact'] = $x['Contact'];

			Session::flash('info', sprintf('Signed in as: %s', $_SESSION['Contact']['username']));

			$_SESSION['uid'] = $x['Contact']['id'];

		} catch (\Exception $e) {
			unset($_SESSION['Company']);
			unset($_SESSION['License']);
			unset($_SESSION['Contact']);
			// _exit_text($e->getTraceAsString(), 500);
			// _exit_html('<h1>Authentication Exception [CAB#108]</h1><p>Please to to <a href="/auth/shut?r=/auth/open">sign-in again</a>.</p>', 500);
		}

		// Maybe?
		// $this->connectCRE();

		// Redirect
		$r = $_GET['r'];
		if (empty($r)) {
			$r = '/home';
		}

		return $RES->withRedirect($r);

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
