<?php

use PHPUnit\Framework\TestCase;

class searchTest extends TestCase
{
	public function testSortOrder()
	{
		$config['key'] = getenv('KEY');

  	// Search Object
  	$search = (object)[
			'rentals'   => true,
			'provinces' => 'KwaZulu-Natal',
		];

		// Search for property
    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->search($search, 1, 10, 'basic.gla', true);

    //print "<pre>"; print_r($r); print "</pre>"; die();

    // Same first id?
    $this->assertEquals($firstId, $secondId);
	}

	/*
  public function testConsistancy()
  {
  	$config['key'] = getenv('KEY');

  	// Search Object
  	$search = (object)[
			//'rentals'   => false,
			'sales' 		=> true,
			'types' 		=> false,
			'provinces' => 'Gauteng',
			//'suburbs' 	=> 'Centurion',
			'cities' 		=> false,
			'size' 			=> false,
		];

		// Search for property
    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->search($search);
    $firstId = $r->results[0]->id;

		// Search for property
    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->search($search);
    $secondId = $r->results[0]->id;

    // Same first id?
    $this->assertEquals($firstId, $secondId);
  }
  */
}