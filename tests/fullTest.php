<?php

use PHPUnit\Framework\TestCase;

class fullTest extends TestCase
{
	public function testFull()
	{
		$config = [
			'key' => getenv('KEY')
		];

		$g = \CodeChap\Gmv\Gmv::forge([
			'key'  => $config['key'],
			'host' => 'localhost',
			'user' => 'website',
			'pass' => 'Friday24a4',
			'base' => 'dev_jhi',
			'pfx'  => 'jhi_'
		]);

		// Partial sync
		$g->sync();
	}
}