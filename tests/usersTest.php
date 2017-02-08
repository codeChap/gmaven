<?php

include(realpath(__DIR__)."/includes.php");

class connectionTest extends PHPUnit_Framework_TestCase
{
  public function testEquals()
  {
    $config['key'] = getenv('KEY');

    $gmaven = new CodeChap\Gmaven($config);
    $gmaven[] = new CodeChap\Request\Users();
    $r = $gmaven->execute();

    print "<pre>"; print_r($r); print "</pre>"; die();

    $this->assertObjectHasAttribute("list", $r);
  }
}