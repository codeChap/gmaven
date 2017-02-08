<?php

include(realpath(__DIR__)."/includes.php");

class connectionTest extends PHPUnit_Framework_TestCase
{
  public function testProperty()
  {
    $config['key'] = getenv('KEY');

    // Find a property
    $gmaven = new CodeChap\Gmaven($config);
    $gmaven[] = (new CodeChap\Request\Search())->province('Gauteng');
    $s = $gmaven->execute();

    // Find first prop and set pid
    $p = current(current($s));
    $pid = $p->id;

    // Get current propery
    $gmaven = new CodeChap\Gmaven($config);
    $gmaven[] = (new CodeChap\Request\Property())->get($pid);
    $r = $gmaven->execute();

    // Check for image
    $c = current($r->images);
    print "<pre>"; print_r( $c->binary->execute() ); print "</pre>"; die();

    // Test
    $this->assertObjectHasAttribute("basic", $r);
  }
}