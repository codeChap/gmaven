<?php

use PHPUnit\Framework;

class hatfieldTest extends TestCase
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

		$from = [];

		$query = [
			'isArchived' => [
				"\$in" => ["\$null", "false"]
			],
			'basic.suburb' => [
				"\$in" => ["Hatfield", "hatfield"]
			]
		];

		// Find all units in freestate
		$r = $g->call('data/default/property/search', [
			'sourceFields' => [
				'id',
				'_updated',
				'isArchived',
				'basic.name',
				'basic.province',
				'basic.suburb',
				'basic.city',
				'basic.displayAddress',
				'basic.primaryCategory',
				'basic.marketingBlurb',
				'basic.forSale',
				'basic.gla',
				'basic.customReferenceId',
				'office.amenities._key',
				'office.amenities.exists',
				'geo.lat',
				'geo.lon',
				'vacancy.currentVacantArea',
				'vacancy.weightedAskingRental',
				'sales.askingPrice',
				'sales.valueM2'
			],
			'query' => $query + $from,
			'page'  => ['number' => 1, 'size' => 1000]
		]);

		print "<pre>"; print_r($r); print "</pre>"; die();
	}
}