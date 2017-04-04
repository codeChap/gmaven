<?php

use PHPUnit\Framework\TestCase;

class baseTest extends TestCase
{
  public function testGetAggregates()
  {
    $config['key'] = getenv('KEY');

    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->getAggregates();

    $this->assertObjectHasAttribute('types', $r);
    $this->assertObjectHasAttribute('provinces', $r);
  }
  
  public function testGetSuburbsOf()
  {
    $config['key'] = getenv('KEY');

    $g = CodeChap\Gmaven\Gmv::instance($config);
    $r = $g->getSuburbsOf('KwaZulu-Natal');

    $this->assertObjectHasAttribute('suburbs', $r);
    $this->assertContains('Pinetown', $r->suburbs);
  }
}