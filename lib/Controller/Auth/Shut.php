<?php
/**
 * Shut the Session
 */

namespace OpenTHC\Lab\Controller\Auth;

class Shut extends \OpenTHC\Controller\Auth\Shut
{
	function __invoke($REQ, $RES, $ARG)
	{
		parent::__invoke($REQ, $RES, $ARG);
		return $RES->withRedirect('/auth/open');
	}
}
