<?php

use PHPUnit\Framework\TestCase;

class searchTest extends TestCase
{
	public function testProperty()
	{
    // Config
		$config['key'] = getenv('KEY');

    // Search Object
    $search = (object)[
      'rentals'   => true,
      'provinces' => 'Gauteng'
    ];

    // Search for property
    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->search($search, 1, 1);

    // Attempt to find an agent
    foreach($r->results as $result){
      $p = $g->property($result->id);
    }
    

    /*

    // Criteria
    $province        = 'Gauteng';
    $suburb          = 'Centurion CBD';

  	// Search Object
  	$search = (object)[
			'rentals'   => true,
			'provinces' => $province,
      'suburbs'   => $suburb
		];

    // Search for property
    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->search($search, 1, 1);

    // Test entries
    foreach($r->results as $result){
      
      $p = CodeChap\Gmaven\Gmv::instance($config);
      $property = $p->property($result->id);

      print "<pre>"; print_r($property); print "</pre>";



    }

    */
  }
}