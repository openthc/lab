<?php
/**
 * Sesssion
 */

namespace OpenTHC\Lab\Middleware;

class Session extends \OpenTHC\Middleware\Session
{
	public function __invoke($REQ, $RES, $NMW)
	{
		parent::open();

		$x = \Edoceo\Radix\Session::flash();

		// Upscale Radix Style to Bootstrap
		$x = str_replace('<div class="good">', '<div class="alert alert-success alert-dismissible" role="alert">', $x);
		$x = str_replace('<div class="info">', '<div class="alert alert-info alert-dismissible" role="alert">', $x);
		$x = str_replace('<div class="warn">', '<div class="alert alert-warning alert-dismissible" role="alert">', $x);
		$x = str_replace('<div class="fail">', '<div class="alert alert-danger alert-dismissible" role="alert">', $x);
		$x = str_replace('</div>', '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>', $x);

		$this->_container->view['alert'] = $x;

		$RES = $NMW($REQ, $RES);

		return $RES;
	}
}
