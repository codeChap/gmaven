<?php

use PHPUnit\Framework;

class contactTest extends PHPUnit_Framework_TestCase
{
	public function testContact()
	{
		//print shell_exec("curl -X POST https://www.gmaven.com/api/data/default/businessEntity/search -H content-type:'application/json' -H postman-token:'9a069e5f-197b-bbbf-fb75-b533b4205d5b' -H gmaven.apikey:\"DipulaWeb:246a465adb3640bead4c26be918c17d28ae555f409eb4e7c930d346b10b932a7\" -d '{ \"query\" : {\"id\" :{ \"\$eq\" : \"61050173-3b4d-4b19-b15c-94dfaa712cd9\"}},\"sourceFields\": [\"id\"]}'");
		//print "curl -X POST https://www.gmaven.com/api/data/default/businessEntity/search -H content-type:application/json -H gmaven.apikey:\"DipulaWeb:246a465adb3640bead4c26be918c17d28ae555f409eb4e7c930d346b10b932a7\" -d '{ \"query\" : {\"id\" :{ \"\$eq\" : \"61050173-3b4d-4b19-b15c-94dfaa712cd9\"}},\"sourceFields\": [\"id\"]}'";
		//die();

		$config = [
			'key' => getenv('KEY')
		];

		$g = \CodeChap\Gmv\Gmv::forge([
			'key'  => $config['key'],
			//'host' => '127.0.0.1',
			//'user' => 'root',
			//'pass' => 'Friday24a4',
			//'base' => 'dev_jhi',
			//'pfx'  => 'jhi_'
		]);

		// Query a building for available contacts
		$query = [
			'id' => [
				"\$eq" => "61050173-3b4d-4b19-b15c-94dfaa712cd9"
			]
		];
		
		$r = $g->call('data/default/property/search', [
			'sourceFields' => [
				'contacts._id'
			],
			'query' => $query,
			'page'  => ['number' => 1, 'size' => 1]
		]);

		// Pull out all the ids
		foreach($r->list as $objArr){
			foreach($objArr as $obj){
				foreach($obj as $contact){
					$arr[] = $contact->_id;
				}
			}
		}

		// Query
		$query = [
			"id" => [
				"\$eq" => $arr[0]
			]
		];

		$r = $g->call('data/default/contact/search', [
			'sourceFields' => [
				'id',
				'name',
				'tel',
				'cell',
				'email'
			],
			'query' => $query,
			'page'  => ['number' => 1, 'size' => 1]
		]);

		// Test
		$this->assertObjectHasAttribute('list', $r);
	}
}