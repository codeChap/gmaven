<?php

use PHPUnit\Framework;

class basicTest extends PHPUnit_Framework_TestCase
{
	public function testPartial()
	{
		$config = [
			'key' => getenv('KEY')
		];

		$g = \CodeChap\Gmv\Gmv::forge([
			'key'  => $config['key'],
			'host' => '127.0.0.1',
			'user' => 'root',
			'pass' => 'Friday24a4',
			'base' => 'dev_jhi',
			'pfx'  => 'jhi_'
		]);

		// Partial sync
		$g->partial();
	}
}