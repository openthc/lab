<?php
/**
 * Class to generate sequences
 */

namespace OpenTHC\Lab;

class Sequence extends \OpenTHC\SQL\Sequence
{

	function __construct($namespace = null, $dbc = null) {
		if (empty($namespace)) $namespace = $_SESSION['Company']['id'];
		if (empty($dbc)) $dbc = _dbc('user');
		parent::__construct($namespace, $dbc);
	}

}
