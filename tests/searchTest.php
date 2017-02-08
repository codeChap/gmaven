<?php

include(realpath(__DIR__)."/includes.php");

class connectionTest extends PHPUnit_Framework_TestCase
{
  public function testEquals()
  {
    $config['key'] = getenv('KEY');

		// Setup Gmaven Search
		$search = new CodeChap\Request\Search();
		$search
			->rent()
			->province('Gauteng')
			->suburb("Sandton")
			->types(
				array(
					"Office",
					"Industrial", 
					"Retail"
				)
			)
		;

		// Execute Gmaven
		$gmaven = new CodeChap\Gmaven($config);
    $gmaven[] = $search;
    $result = $gmaven->execute();
    
    foreach($result as $p){
    	//print "<pre>"; print_r($p); print "</pre>"; die();
    }

    $this->assertObjectHasAttribute("list", $result);
  }
}