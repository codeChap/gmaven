<?php

use PHPUnit\Framework\TestCase;

class vacantSpaceTest extends TestCase
{
  public function testVacantUnit()
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
      'rentals'   => true
    ];

    // Search for property and find id
    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->search($search, 1, 1);
    $pid = (current($r->results))->id;

    // Take id and pull vacant space
    $r = $g->getUnitsof($pid);

    // Check for list
    $this->assertObjectHasAttribute('list', $r);
  }
}