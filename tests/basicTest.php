<?php

use PHPUnit\Framework\TestCase;

class basicTest extends TestCase
{
  public function testGmaven()
  {
    $g = CodeChap\Gmv::forge([]);
    $g->pull();
   //$r = $g->install();
  }
}