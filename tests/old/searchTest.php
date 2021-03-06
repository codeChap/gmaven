<?php

use PHPUnit\Framework\TestCase;

class searchTest extends TestCase
{
  public function testWideRange()
  {
    // Config
    $config['key'] = getenv('KEY');

    // Criteria
    $province        = 'Gauteng';
    $suburb          = ['Sandton'];
    $min             = 100;
    $max             = 50000;
    $primaryCategory = ['Office'];

    // Search Object
    $search = (object)[
      'provinces' => $province,
      'suburbs'   => $suburb,
      'types'     => $primaryCategory,
      'size'      => [$min, $max]
    ];

    // Search for property
    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->search($search, 1, 30);

    // Test entries
    foreach($r->results as $result){
      $this->assertGreaterThanOrEqual($min, $result->basic->gla);
      $this->assertLessThanOrEqual($max, $result->basic->gla);
    }
  }

  public function testFeaturedProperties()
  {
    // Config
    $config['key'] = getenv('KEY');

    // Criteria
    $province        = 'Gauteng';
    $suburb          = 'Centurion CBD';
    $primaryCategory = ['Office', 'Industrial'];

    // Search Object
    $search = (object)[
      'rentals'   => true,
      'provinces' => $province,
      'suburbs'   => $suburb,
      'types'     => $primaryCategory,
    ];

    // Search for property
    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->featured()->results[0];
  }

	public function testSearchability()
	{
    // Config
		$config['key'] = getenv('KEY');

    // Criteria
    $province        = 'Gauteng';
    $suburb          = 'Centurion CBD';
    $primaryCategory = ['Office', 'Industrial'];

  	// Search Object
  	$search = (object)[
			'rentals'   => true,
			'provinces' => $province,
      'suburbs'   => $suburb,
      'types'     => $primaryCategory,
		];

    // Search for property
    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->search($search, 1, 30);

    // Test entries
    foreach($r->results as $result){
      // Make sure we are in the set province
      $this->assertEquals($result->basic->province, $province);
      // Make sure we are in the set suburb
      $this->assertEquals($result->basic->suburb, $suburb);
      // Make sure we are in the set suburb
      $this->assertContains($result->basic->primaryCategory, $primaryCategory);
    }
  }

  public function testSizeRestriction()
  {
    // Config
    $config['key'] = getenv('KEY');

    // Criteria
    $province = 'Gauteng';
    $min      = 1000;
    $max      = 500;

    // Search Object
    $search = (object)[
      'rentals'   => true,
      'provinces' => $province,
      'size'      => [$min, $max]
    ];

    // Search for property
    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->search($search, 1, 30);

    // Test entries
    foreach($r->results as $result){
      $this->assertGreaterThanOrEqual($min, $result->basic->gla);
      $this->assertLessThanOrEqual($max, $result->basic->gla);
    }
	}
}