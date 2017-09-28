<?php

use PHPUnit\Framework;

class unitTest extends PHPUnit_Framework_TestCase
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
			'propertyId' => [
				"\$eq" => "115b5e47-e2e8-4141-915d-921be13611fd"
			]
		];

		// Find all units in freestate
		$r = $g->call('data/custom/propertyUnit/search', [
			'sourceFields' => [
				'id',
				'_updated',
				'isArchived',
				'propertyId',
				'unitDetails.unitId',
				'unitDetails.customReferenceId',
				'unitDetails.gla',
				'unitDetails.primaryCategory',
				'vacancy.marketing.availableType',
				'vacancy.marketing.availableFrom',
				'vacancy.marketing.noticePeriod',
				'vacancy.unitDetails.gmr',
				'vacancy.unitDetails.netAskingRental',
				'vacancy.sales.marketingHeading',
				'vacancy.sales.description',
				'vacancy.unitManagement.status'
			],
			'query' => $query + $from,
			'page'  => ['number' => 1, 'size' => 1000]
		]);

		print "<pre>"; print_r($r); print "</pre>"; die();
	}
}