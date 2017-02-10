<?php

use PHPUnit\Framework\TestCase;

class baseTest extends TestCase
{
  public function testGetAggregates()
  {
    $config['key'] = getenv('KEY');

    $g = CodeChap\Gmv::instance($config);
    $r = $g->getAggregates();

    $this->assertObjectHasAttribute('types', $r);
    $this->assertObjectHasAttribute('provinces', $r);
  }

  public function testGetSuburbsOf()
  {
    $config['key'] = getenv('KEY');

    $g = CodeChap\Gmv::instance($config);
    $r = $g->getSuburbsOf('KwaZulu-Natal');

    $this->assertObjectHasAttribute('suburbs', $r);
    $this->assertContains('Pinetown', $r->suburbs);
  }

  public function testSearch()
  {
  	$config['key'] = getenv('KEY');

  	// Search Object
  	$search = (object)[
			'rentals'   => true,
			'sales' 		=> false,
			'types' 		=> false,
			'provinces' => 'Gauteng',
			'suburbs' 	=> 'Centurion',
			'cities' 		=> false,
			'size' 			=> false,
		];

		// Search for property
    $g = CodeChap\Gmv::instance($config);
    $r = $g->search($search);
    $this->assertObjectHasAttribute('results', $r);

    // Pull property details
    $r = $g->property($r->results[0]->id);
    $this->assertObjectHasAttribute('result', $r);
  }

  public function testFeatured()
  {
  	$config['key'] = getenv('KEY');

		// Search for property
    $g = CodeChap\Gmv::instance($config);
    $r = $g->featured();
    $this->assertObjectHasAttribute('results', $r);
  }
}