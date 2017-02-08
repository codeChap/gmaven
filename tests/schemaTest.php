<?php

include(realpath(__DIR__)."/includes.php");

class connectionTest extends PHPUnit_Framework_TestCase
{
  public function testSearch()
  {
    $config['key'] = getenv('KEY');

    $gmaven = new CodeChap\Gmaven($config);
    $gmaven[] = new CodeChap\Request\Schema();
    $r = $gmaven->execute();

    
    $export = fopen("p.txt", "w") or die("Unable to open file!");
    foreach($r->property as $k => $v){
    	if($k == "basic.primaryCategory"){
    		print "<pre>"; print_r($v); print "</pre>";
				//fwrite($export, $sk . PHP_EOL);
    	}
    }
    fclose($export);
    
    $this->assertObjectHasAttribute("property", $r);
  }
}