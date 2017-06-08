<?php

use PHPUnit\Framework\TestCase;

class menlynTest extends TestCase
{
	public function testSearchability()
	{
    // Config
		$config['key'] = getenv('KEY');

    // Criteria
    $province        = 'Gauteng';
    $suburb          = 'Menlyn';
    $city            = 'Pretoria';

  	// Search Object
  	$search = (object)[
			'provinces' => $province,
      'suburbs'   => $suburb,
      'cities'    => $city,
		];

    // Search for property
    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->search($search, 1, 30);

    print "<pre>"; print_r($r); print "</pre>"; die();
  }
}
?>