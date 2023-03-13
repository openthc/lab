<?php
/**
 * Initialise an Authenticated Session
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Lab\Controller\Auth;

use Edoceo\Radix\Session;

use OpenTHC\License;

class Init extends \OpenTHC\Controller\Auth\oAuth2
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$RES = $this->loadCompany($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		$RES = $this->loadContact($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		$RES = $this->loadLicense($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		if (empty($_SESSION['tz'])) {
			$_SESSION['tz'] = 'America/Los_Angeles';
		}

		$ret = $_GET['r'];
		if (empty($ret)) {
			$ret = '/dashboard';
		}

		Session::flash('info', sprintf('Signed in as: %s', $_SESSION['Contact']['username']));

		return $RES->withRedirect($ret);

	}

	/**
	 *
	 */
	function loadCompany($RES) : object
	{
		$dbc_auth = _dbc('auth');
		$dbc_main = _dbc('main');

		$c0 = $_SESSION['Company']['id'];

		// Lookup Main Company
		$Company0 = $dbc_main->fetchRow('SELECT * FROM company WHERE id = :c0', [
			':c0' => $c0
		]);
		if (empty($Company0['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'note' => sprintf('Invalid Company "%s" [CAC-067]', $this->_Company_Auth['id']) ],
			], 400);
		}

		// Lookup Auth Company
		$Company1 = $dbc_auth->fetchRow('SELECT * FROM auth_company WHERE id = :c0', [
			':c0' => $c0
		]);
		switch ($Company1['stat']) {
			case 200:
				// OK
				break;
			default:
				_exit_html_fail('<h1>Invalid Company Profile [CAI-080]', 403);
		}
		if (empty($Company1['dsn'])) {
			_exit_html_fail('<h1>Invalid Company Profile [CAI-083]', 403);
		}

		$Company = array_merge($Company0, $Company1);
		$_SESSION['dsn'] = $Company['dsn'];

		unset($Company['dsn']);

		$_SESSION['Company'] = $Company;

		return $RES;
	}

	/**
	 *
	 */
	function loadContact($RES) : object
	{
		$contact_id = $_SESSION['Contact']['id'];

		$dbc_auth = _dbc('auth');
		$dbc_main = _dbc('main');

		// Lookup Contact
		$sql = 'SELECT id, flag, name AS fullname, email, phone FROM contact WHERE id = :ct0';
		$arg = [ ':ct0' => $contact_id ];
		$Contact0 = $dbc_main->fetchRow($sql, $arg);
		if (empty($Contact0['id'])) {
			// Throw Error?
			$RES = $RES->withStatus(403);
			return $RES;
		}

		// Lookup Auth_Contact
		$sql = 'SELECT * FROM auth_contact WHERE id = :ct0';
		$arg = [ ':ct0' => $contact_id ];
		$Contact1 = $dbc_auth->fetchRow($sql, $arg);

		$Contact = array_merge($Contact0, $Contact1);

		unset($Contact['acl']);
		unset($Contact['auth_company_id']);
		unset($Contact['company_id']);
		unset($Contact['created_at']);
		unset($Contact['id_int8']);
		unset($Contact['pin']);
		unset($Contact['rbe_auth']);
		unset($Contact['ts_sign_in']);

		$_SESSION['Contact'] = $Contact;

		return $RES;

	}

	/**
	 *
	 */
	function loadLicense($RES) : object
	{
		$dbc_main = _dbc('main');
		$dbc_user = $this->_container->DBC_User;
		// if (empty($dbc_user)) {

		// }

		// Set default license if none provided
		if (empty($_SESSION['License']['id'])) {

			// Find Default License
			$sql = 'SELECT id FROM license WHERE flag & :f0 = 0 AND flag & :f1 != 0';
			$arg = [
				':f0' => License::FLAG_DEAD,
				':f1' => License::FLAG_MINE
			];
			$_SESSION['License']['id'] = $dbc_user->fetchOne($sql, $arg);

		}

		// Look Base License
		$sql = 'SELECT * FROM license WHERE company_id = :c0 AND id = :l0';
		$arg = [
			':c0' => $_SESSION['Company']['id'],
			':l0' => $_SESSION['License']['id'],
		];
		$License0 = $dbc_main->fetchRow($sql, $arg);
		if (empty($License0['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'note' => sprintf('Invalid License "%s" [CAI-133]', $_SESSION['License']['id']) ],
			], 400);
		}

		// User Specific License Data
		$sql = 'SELECT * FROM license WHERE id = :l0';
		$arg = [
			':l0' => $_SESSION['License']['id'],
		];
		$License1 = $dbc_user->fetchRow($sql, $arg);
		if (empty($License1['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'note' => sprintf('Invalid License "%s" [CAI-147]', $_SESSION['License']['id']) ],
			], 400);
		}

		$License = array_merge($License0, $License1);

		unset($License['address_full']);
		unset($License['address_meta']);
		unset($License['city']);
		unset($License['company_id']);
		unset($License['cre_meta_hash']);
		unset($License['created_at']);
		unset($License['deleted_at']);
		unset($License['ftsv']);
		unset($License['geo_lat']);
		unset($License['geo_lon']);
		unset($License['name_code']);
		unset($License['name_cre']);
		unset($License['name_dba']);
		unset($License['tags']);
		unset($License['updated_at']);
		unset($License['weblink_meta']);

		$_SESSION['License'] = $License;

		return $RES;

	}

}
