<?php

use PHPUnit\Framework;

class agentTest extends PHPUnit_Framework_TestCase
{
	public function testFind()
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

		$t = $g->call('cre/user/team/current/user');

		print "<pre>"; print_r($t); print "</pre>";

		// Find all units in freestate
		$r = $g->call('data/entity/property/4646f33e-b232-492a-982e-de0986bfe95f/responsibility');

		print "<pre>"; print_r($r); print "</pre>"; die();
	}
}