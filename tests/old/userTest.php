<?php

use PHPUnit\Framework\TestCase;

class searchTest extends TestCase
{
 public function testUserResponsibility()
  {
    $config['key'] = getenv('KEY');

    // Search for property
    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->users();

    $this->assertObjectHasAttribute('result', $r);
  }
}