<?php
/**
 *
 */

namespace OpenTHC\Lab\Test\System;

class A_System_Test extends \OpenTHC\Lab\Test\Base
{
	public function test_dependencies()
	{
		$dir = sprintf('%s/webroot', APP_ROOT);
		$this->assertTrue(is_dir($dir), 'webroot is missing');
	}

	/**
	 *
	 */
	function test_var()
	{
		// Var Path
		$var = sprintf('%s/var', APP_ROOT);
		$this->assertTrue(is_dir($var), 'var is missing');
		$var_stat = stat($var);

		$o = posix_getpwuid($var_stat[4]);
		$this->assertIsArray($o);
		$this->assertEquals('www-data', $o['name']);

		$g = posix_getgrgid($var_stat[5]);
		$this->assertIsArray($g);
		$this->assertEquals('www-data', $g['name']);

		$m = ($var_stat[2] & 0x0fff);
		$this->assertEquals($m, 0755); // Perms in OCTAL

	}

	public function test_webroot()
	{
		$dir = sprintf('%s/webroot', APP_ROOT);
		$this->assertTrue(is_dir($dir));

		// Webroot Output Path
		$dir = sprintf('%s/webroot/output', APP_ROOT);
		$dir_stat = stat($dir);

		$o = posix_getpwuid($dir_stat[4]);
		$this->assertIsArray($o);
		$this->assertEquals('www-data', $o['name']);

		$g = posix_getgrgid($dir_stat[5]);
		$this->assertIsArray($g);
		$this->assertEquals('www-data', $g['name']);

		$m = ($dir_stat[2] & 0x0fff);
		$this->assertEquals($m, 0755); // Perms in OCTAL

		// @todo Check all the other directories/files to be owned by 'openthc'

		// $this->assertIsTrue();
	}

	function test_convert()
	{
		$f = '/usr/bin/convert';
		$this->assertTrue(is_file($f), "'$f' not found");
		$this->assertTrue(is_executable($f), "'$f' not executable");
	}

	function test_gs()
	{
		$f = '/usr/bin/gs';
		$this->assertTrue(is_file($f), "'$f' not found");
		$this->assertTrue(is_executable($f), "'$f' not executable");

	}

	function test_pdftotext()
	{
		$f = '/usr/bin/pdftotext';
		$this->assertTrue(is_file($f), "'$f' not found");
		$this->assertTrue(is_executable($f), "'$f' not executable");

	}

}
