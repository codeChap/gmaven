<?php

use PHPUnit\Framework;

class syncTest extends PHPUnit_Framework_TestCase
{
	public function testProvincesAndSuburbs()
	{
		// Fire up GMV
		$g = \CodeChap\Gmv\Gmv::forge([
			'key'  => getenv('KEY')
		]);

		// Call for provinces
		$result = $g->call('data/default/property/aggregates',[
			'size'       => -1,
			'aggregates' => [
				'basic.province' => 1
			]
		]);

		// Test provinces
		$this->assertObjectHasAttribute('aggregates', $result);
		$this->assertObjectHasAttribute('basic.province$$distinct', $result->aggregates);

		// Filter out provinces
		$provinces = array_filter($result->aggregates->{'basic.province$$distinct'});

		// Loop over provinces
		foreach($provinces as $province){

			// Call for suburbs of provinces
			$r = $g->call('data/default/property/aggregates', [
				'size'  => -1,
				'query' => [
					'basic.province' => [
						'$in' => $province
					]
				],
				'aggregates' => [
					'basic.suburb' => 1
				]
			]);

			// Test
			$this->assertObjectHasAttribute('aggregates', $r);
			$this->assertObjectHasAttribute('basic.suburb$$distinct', $r->aggregates);
		}
	}
}