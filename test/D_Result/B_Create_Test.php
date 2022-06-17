<?php
/**
 *
 */

namespace OpenTHC\Lab\Test\Result;

class B_Create_Test extends \OpenTHC\Lab\Test\Base
{
	function test_single()
	{
		$res = $this->get('/result/four_zero_four');
		$this->assertTrue(false, 'Not Implemented');
	}

	function test_single_403()
	{
		$res = $this->get('/result/four_zero_four');
	}

	function test_single_404()
	{
		$res = $this->get('/result/four_zero_four');
	}

	function test_single_405()
	{
		$res = $this->get('/result/four_zero_four');
	}

}
