<?php
/**
 *
 */

namespace OpenTHC\Lab\Test\D_Result;

class A_Search_Test extends \OpenTHC\Lab\Test\Base
{
	function test_single()
	{
		$res = $this->get('/result/four_zero_four');
		$res = $this->assertValidResponse($res, 404);
		// $this->assertTrue(false, 'Not Implemented');
	}

	function test_single_403()
	{
		$res = $this->get('/result/four_zero_four');
		$res = $this->assertValidResponse($res, 403);
		$this->assertTrue(false, 'Not Implemented');
	}

	function test_single_404()
	{
		$res = $this->get('/result/four_zero_four');
		$res = $this->assertValidResponse($res, 404);
	}

	function test_single_405()
	{
		$res = $this->post('/result/four_zero_four');
		$res = $this->assertValidResponse($res, 405);
	}

}
