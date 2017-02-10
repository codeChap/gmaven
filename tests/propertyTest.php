<?php

class connectionTest extends PHPUnit_Framework_TestCase
{
  public function testProperty()
  {
    

    // Test
    $this->assertObjectHasAttribute("basic", $r);
  }
}