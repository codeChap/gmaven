<?php

use PHPUnit\Framework\TestCase;

class searchTest extends TestCase
{
  public function testUnits()
  {
    // Config
    $config['key'] = getenv('KEY');

    // Search for property
    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->getUnitsof();

    print "<pre>"; print_r($r); print "</pre>"; die();

    // Attempt to find an agent
    foreach($r->results as $result){
      $p = $g->property($result->id);
    }
    
  }

  /*
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

    //print "<pre>"; print_r($r); print "</pre>"; die();

    // Attempt to find an agent
    foreach($r->results as $result){
      $p = $g->property($result->id);
    }
  }
  */
}
?>