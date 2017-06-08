<?php

use PHPUnit\Framework\TestCase;

class searchTest extends TestCase
{
	public function testSearchability()
	{
    // Config
		$config['key'] = getenv('KEY');

    // Criteria
    $province        = 'KwaZulu-Natal';
    $suburb          = 'Sunningdale - KZN';
    $primaryCategory = ['Retail'];

  	// Search Object
  	$search = (object)[
			'rentals'   => true,
			'provinces' => $province,
      'suburbs'   => $suburb,
      //'types'     => $primaryCategory,
		];

    // Search for property
    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->search($search, 1, 30);

    print "<pre>"; print_r($r); print "</pre>"; die();
  }
}
?>