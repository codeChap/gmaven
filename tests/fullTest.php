<?php

use PHPUnit\Framework\TestCase;

class fullTest extends TestCase
{
	public function testFull()
	{
		$g = \CodeChap\Gmv\Gmv::forge([
			'key'  => 'CweWeb:d7d4f40pf4de89863amzcbcd2e3o6bf2c08ee48m293ff432183bd13w05bc1bf188nee29',
			'host' => '127.0.0.1',
			'user' => 'root',
			'pass' => 'Friday24a4',
			'base' => 'dev_jhi',
			'pfx'  => 'jhi_'
		]);

		// Partial sync
		$g->sync();
	}
}