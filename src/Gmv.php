<?php

/**
 * PHP library to communicate GMaven (https://www.gmaven.com)
 *
 * @author     CodeChap
 * @license    MIT
 * @copyright  2017 CodeChap
 * 
 */

namespace CodeChap;

class Gmv extends Arc\Singleton
{
	public $endPoint = false;
	
	public $post = true;
	
	public $sourceFields = [];
	
	public $aggregates = [];

	public $query = [];

	public $size = false;
	
	public $pageSize = false;
	
	public $pageNumber = false;

	public $time = false;

	public $cli = false;



	public $client = false;
	public $headers = [];

	/**
	 * Constructor
	 */
	public function __construct($config)
	{		
		// Setup climate and record current time
		$this->cli = new \League\CLImate\CLImate;
		$this->time = time();

		// Call parent constructor
		parent::__construct($config);
	}

	/**
	 * Give info
	 */
	public function __destruct()
	{
		$this->cli->green('Took ' . (time() - $this->time) . ' seconds');
	}

	/**
	 * Start merging with Gmaven
	 */
	public function Sync()
	{
		// Build required tables
		if(true){
			$b = new Build($this->get_config());
			$b->tables();
		}

		// Start fetching aggregates data
		if(true){
			$this->getCategories();
			$this->getProvinces();
			$this->getSuburbs();
			$this->getCities();
		}

		// Start fetching property data
		if(true){
			$this->getProperties();
		}

		// Start fetching unit data
		if(true){
			$this->getUnits();
		}

		// Start fetching images
		if(true){
			$this->getImages();
		}

		// Start fetching images
		if(false){
			$this->getBrokers();
		}
	}

	/**
	 * Get all categories
	 */
	private function getCategories()
	{
		// Info
		$this->cli->green('Fetching categories');

		// Call Gmaven
		$r = $this->post('data/default/property/aggregates', [
			'size' => -1,
			'aggregates' => [
				'basic.primaryCategory' => 1
			]
		]);

		// Gather data
		$data = array_filter($r->aggregates->{'basic.primaryCategory$$distinct'});

		// Progress bar
		$progress = $this->cli->progress()->total(count($data));

		// Insert
		$db = Db::forge($this->get_config());
		$db->query("TRUNCATE TABLE `#gmaven_categories`")->exec();
		foreach($data as $i => $category){
			$db->query("INSERT INTO `#gmaven_categories` (`category`, `updated_at`) VALUES('".addslashes($category)."', ".$this->time.")")->exec();
			$progress->current($i);
		}
	}

	/**
	 * Get all provinces
	 */
	private function getProvinces()
	{
		// Info
		$this->cli->green('Fetching provinces');

		// Call Gmaven
		$r = $this->post('data/default/property/aggregates', [
			'size' => -1,
			'aggregates' => [
				'basic.province' => 1
			]
		]);

		// Gather data
		$data = array_filter($r->aggregates->{'basic.province$$distinct'});

		// Progress bar
		$progress = $this->cli->progress()->total(count($data));

		// Insert
		$db = Db::forge($this->get_config());
		$db->query("TRUNCATE TABLE `#gmaven_provinces`")->exec();
		foreach($data as $i => $province){
			$db->query("INSERT INTO `#gmaven_provinces` (`province`, `updated_at`) VALUES('".addslashes($province)."', ".$this->time.")")->exec();
			$progress->current($i);
		}
	}

	/**
	 * Get all suburbs
	 */
	private function getSuburbs()
	{
		// Database
		$db = Db::forge($this->get_config());

		// Clear out table
		$db->query("TRUNCATE TABLE `#gmaven_suburbs`")->exec();

		// We need a list of property ids
		$provinces = $db->query("SELECT `id`, `province` FROM `#gmaven_provinces`")->get();

		// Call Gmaven on each province
		foreach($provinces as $p){

			// Info
			$this->cli->green('Fetching suburbs of ' . $p['province']);

			// Call
			$r = $this->post('data/default/property/aggregates', [
				'size' => -1,
				'query' => [
					'basic.province' => [
						'$in' => $p['province']
					]
				],
				'aggregates' => [
					'basic.suburb' => 1
				]
			]);
		
			// Gather data
			$data = array_filter($r->aggregates->{'basic.suburb$$distinct'});

			// Progress bar
			$progress = $this->cli->progress()->total( count($data) );

			// Insert
			foreach($data as $i => $suburb){
				$db->query("
					INSERT INTO `#gmaven_suburbs`
					(`suburb`, `province_id`, `updated_at`)
					VALUES(
						'".addslashes($suburb)."',
						".$p['id'].",
						".$this->time."
					)
				")->exec();
				
				// Update progress
				$progress->current($i);
			}
		}
	}

	/**
	 * Get all cities
	 */
	private function getCities()
	{
		// Info
		$this->cli->green('Fetching cities');

		// Call Gmaven
		$r = $this->post('data/default/property/aggregates', [
			'size' => -1,
			'aggregates' => [
				'basic.city' => 1
			]
		]);

		// Gather data
		$data = array_filter($r->aggregates->{'basic.city$$distinct'});

		// Progress bar
		$progress = $this->cli->progress()->total(count($data));

		// Insert
		$db = Db::forge($this->get_config());
		$db->query("TRUNCATE TABLE `#gmaven_cities`")->exec();
		foreach($data as $i => $city){
			$db->query("INSERT INTO `#gmaven_cities` (`city`, `updated_at`) VALUES('".addslashes($city)."', ".$this->time.")")->exec();
			$progress->current($i);
		}
	}

	/**
	 * Get all properties and re-insert them into the database
	 *
	 * Total results may differ from what you see in CRE, Gmaven apply filters to the API to ensure that no obviously "incomplete" 
	 * properties are displayed (e.g. ones that're missing critical location or price information) which would account for this 
	 * difference.
	 *
	 * @return Boolean
	 */
	private function getProperties()
	{
		// Call Gmaven to get total properties
		$r = $this->post('data/default/property/search', [
			'sourceFields' => ['id'],
			'query'	       => ['isArchived'	=> ["\$in" => ["\$null", "false"]]],
			'page'	       => ['number' => 1, 'size' => 1]
		]);
		$t = $r->md->totalResults;

		//print "<pre>"; print_r($r); print "</pre>"; die();

		// Info
		$this->cli->green('Fetching '.$t.' properties.');

		// Now pull everything!
		$r = $this->post('data/default/property/search', [
			'sourceFields' => [
				'id',
				'_updated',
				'basic.name',
				'basic.province',
				'basic.suburb',
				'basic.city',
				'basic.displayAddress',
				'basic.primaryCategory',
				'basic.marketingBlurb',
				'basic.forSale',
				'basic.gla',
				'basic.customReferenceId',
				'office.amenities._key',
				'office.amenities.exists',
				'geo.lat',
				'geo.lon',
				'vacancy.currentVacantArea',
				'vacancy.weightedAskingRental',
				'sales.askingPrice',
				'sales.valueM2'
			],
			'query'	      => ['isArchived'	=> ["\$in" => ["\$null", "false"]]],
			'page'	      => ['number' => 1, 'size' => $t]
		]);

		// Progress bar
		$progress = $this->cli->progress()->total($t);

		// Forge database connection
		$db = Db::forge($this->get_config());

		// Clear out existing entries
		$db->query("TRUNCATE TABLE `#gmaven_properties`")->exec();
		$db->query("TRUNCATE TABLE `#gmaven_property_details`")->exec();

		// Loop over results
		foreach($r->list as $i => $p){

			//print "<pre>"; print_r($p); print "</pre>";

			// Find province, city, suburb and category id
			$catId	= $db->query("SELECT `id` FROM `#gmaven_categories` WHERE `category`	= '".(addslashes($p->basic->primaryCategory))."'")->get_one('id');
			$pid	  = $db->query("SELECT `id` FROM `#gmaven_provinces` WHERE `province`	  = '".(addslashes($p->basic->province))."'")->get_one('id');
			$cid	  = $db->query("SELECT `id` FROM `#gmaven_cities` WHERE `city`	        = '".(addslashes($p->basic->city))."'")->get_one('id');
			$sid	  = $db->query("SELECT `id` FROM `#gmaven_suburbs` WHERE `suburb`	      = '".(addslashes($p->basic->suburb))."'")->get_one('id');

			// Insert data
			$q = "
	      BEGIN;

	      INSERT INTO `#gmaven_property_details`
	      (`gmv_id`, `name`, `customReferenceId`, `displayAddress`, `marketingBlurb`)
	      VALUES (
	        '".addslashes($p->id)."',
	        '".addslashes($p->basic->name)."',
	        '".addslashes($p->basic->customReferenceId)."',
	        '".addslashes($p->basic->displayAddress)."',
	        '".addslashes($p->basic->marketingBlurb)."'
	      );

	      INSERT INTO `#gmaven_properties`
	      (`did`, `lon`, `lat`, `gla`, `currentVacantArea`, `weightedAskingRental`, `for_sale`, `category_id`, `province_id`, `city_id`, `suburb_id` ,`updated_at`, `gmv_updated`)
	      VALUES (
	      LAST_INSERT_ID(),
	        ".($p->geo->lon	== 0 ? 'NULL' : $p->geo->lon).",
	        ".($p->geo->lat	== 0 ? 'NULL' : $p->geo->lat).",
	        ".(!empty($p->basic->gla)	                    ? $p->basic->gla	                  : 0).",
	        ".(!empty($p->vacancy->currentVacantArea)	    ? $p->vacancy->currentVacantArea	  : 0).",
	        ".(!empty($p->vacancy->weightedAskingRental)	? $p->vacancy->weightedAskingRental	: 'NULL').",
	        ".(!empty($p->basic->forSale)	                ? $p->basic->forSale	              : 0).",
	        ".$catId.",
	        ".$pid.",
	        ".$cid.",
	        ".$sid.",
	        ".$this->time.",
	        ".$p->_updated."
	      );

	      COMMIT;
			";

			// Insert
			$db->query($q)->exec();

			// Update progress bar
			$progress->current($i);
		}

		// Done
		return true;
	}

	/**
	 * 
	 */
	public function getUnits()
	{
		// Call Gmaven to get total properties
		$r = $this->post('data/custom/propertyUnit/search', [
			'sourceFields' => ['id'],
			'query'	       => ['isArchived' => ["\$in" => ["\$null", "false"]]],
			'page'	       => ['number' => 1, 'size' => 1]
		]);
		$t = $r->md->totalResults;

		// Info
		$this->cli->green('Fetching '.$t.' units.');

		// Now pull everything
		$r = $this->post('data/custom/propertyUnit/search', [
			'sourceFields' => [
				'id',
				'_updated',
				'propertyId',
				'unitDetails.unitId',
				'unitDetails.customReferenceId',
				'unitDetails.gla',
				'unitDetails.primaryCategory',
				'vacancy.marketing.availableType',
				'vacancy.marketing.availableFrom',
				'vacancy.marketing.noticePeriod',
				'vacancy.unitDetails.gmr',
				'vacancy.unitDetails.netAskingRental',
				'vacancy.sales.marketingHeading',
				'vacancy.sales.description',
				'vacancy.unitManagement.status'
			],
			'query'	=> [
				//"propertyId" => ["\$eq"   => '8775507b-098f-4862-a9cc-00bea4e39be6'],
				'isArchived' => ["\$in" => ["\$null", "false"]]
			],
			'page'	=> ['number' => 1, 'size' => $t]
		]);

		// Progress bar
		$progress = $this->cli->progress()->total($t);

		// Forge database connection
		$db = Db::forge($this->get_config());

		// Clear out existing entries
		$db->query("TRUNCATE TABLE `#gmaven_units`")->exec();

		// Loop over results
		foreach($r->list as $i => $u){

			if(isset($u->propertyId) and !empty($u->propertyId) ){

				$propertyId = addslashes($u->propertyId);

				// Find category id
				$catId	= $db->query("SELECT `id` FROM `#gmaven_categories` WHERE `category`	= '".(addslashes($u->unitDetails->primaryCategory))."'")->get_one('id');

				// Find Property id
				$pid = $db->query("SELECT `id` FROM `#gmaven_property_details` WHERE `gmv_id`	= '".$u->propertyId."'")->get_one('id');

				// Insert data
				$q = "
		      INSERT INTO `#gmaven_units`
		      (`pid`, `gmv_id`, `propertyId`, `unitId`, `customReferenceId`, `category_id`, `gla`, `gmr`, `availableType`, `availableFrom`, `updated_at`, `gmv_updated`)
		      VALUES (
		        ".$pid.",
		        '".addslashes($u->id)."',
		        '".$propertyId."',
		        '".(isset($u->unitDetails->unitId) ? addslashes($u->unitDetails->unitId) : 'NULL')."',
		        '".addslashes($u->unitDetails->customReferenceId)."',
		        ".$catId.",
		        ".$u->unitDetails->gla.",
		        ".$u->vacancy->unitDetails->gmr.",
		        '".addslashes($u->vacancy->marketing->availableType)."',
		        ".(isset($u->vacancy->marketing->availableFrom) ? $u->vacancy->marketing->availableFrom : 'NULL').",
		        ".$this->time.",
		        ".$u->_updated."
		      );
				";

				// Insert
				$db->query($q)->exec();
			}

			// Update progress bar
			$progress->current($i);
		}
	}

	/**
	 * 
	 */
	public function getImages()
	{
		// Call Gmaven to get total properties
		$r = $this->post('data/content/entity/property/search', [
			'contentCategory' 	=> 'Image'
		]);
		$t = count($r->list);

		// Info
		$this->cli->green('Fetching '.$t.' images.');

		// Progress bar
		$progress = $this->cli->progress()->total($t);

		// Forge database connection
		$db = Db::forge($this->get_config());

		// Clear out existing entries
		$db->query("TRUNCATE TABLE `#gmaven_images`")->exec();

		// Loop over results
		foreach($r->list as $i => $img){

			// Insert data
			$q = "
	      INSERT INTO `#gmaven_images`
	      (`entityDomainKey`, `contentDomainKey`, `rating`, `updated_at`, `gmv_updated`)
	      VALUES (
	        '".$img->entityDomainKey."',
	        '".$img->contentDomainKey."',
	        ".( isset($img->metadata->Rating) ? $img->metadata->Rating : 0 ).",
	        ".$this->time.",
	        ".$img->updated."
	      );
			";

			// Insert
			$db->query($q)->exec();

			// Update progress bar
			$progress->current($i);
		}
	}

	/**
	 * 
	 */
	public function getBrokers()
	{
		// Gather team
		$team = $this->get('cre/user/team/current/user');

		// Forge database connection
		$db = Db::forge($this->get_config());

		// We need a list of property ids
		$list = $db->query(
			"
			SELECT D.`gmv_id`, P.`id`  FROM `#gmaven_property_details`D
			LEFT JOIN `#gmaven_properties` P ON P.`did` = D.`id`
			"
		)->get();

		// Clear out existing entries
		$db->query("TRUNCATE TABLE `#gmaven_brokers`")->exec();

		// Info and progress
		$this->cli->green('Match brokers to properties');
		$progress = $this->cli->progress()->total(count($list));

		// Loop over each property
		foreach($list as $i => $p){

			// Create or reset broker array
			$brokers = [];

			// Fetch info
			$r = $this->get('data/entity/property/'.$p['gmv_id'].'/responsibility');

			// Loop over everything and mach up
			if(isset($r->list) and count($r->list)){
				foreach($r->list as $l){
					foreach($team as $member){
						if($l->userDomainKey == $member->_id){
							$this->brokerInset($member, $p);
						}
					}
				}
			}

			// Update progress bar
			$progress->current($i);
		}
	}

	/**
	 * 
	 */
	public function brokerInset($member, $p)
	{
		// Forge database connection
		$db = Db::forge($this->get_config());

		// Check  if the broker exists
		if($db->query("SELECT * FROM `#gmaven_brokers` WHERE `gmv_id` = '".$member->_id."'")->get_one('id', false) == false){

			// Inset new broker
			$q = "
			INSERT INTO `#gmaven_brokers`
			(`gmv_id`, `name`, `tel`, `email`, `updated_at`)
			VALUES (
			  '".$member->_id."',
			  '".$member->name."',
			  '".$member->tel."',
			  '".$member->email."',
			  ".$this->time."
			);
			";
			$db->query($q)->exec();
		}

		// Get broker id 
		$bid = $db->query("SELECT `id` FROM `#gmaven_brokers` WHERE `gmv_id` = '".$member->_id."'")->get_one('id');

		// Match up
		$db->query("UPDATE `#gmaven_properties` SET `bid` = ".$bid." WHERE `id` = ".$p['id'].";")->exec();
	}

	/**
	 * Get data
	 */
	private function get($endPoint)
	{
		// Set and filter post data
		$clientDataArray = [
			'base_uri' => 'https://www.gmaven.com/api/'
		];

		// Go guzzle
		//try{
			// Setup Guzzle
			$client = new \GuzzleHttp\Client($clientDataArray);
			$response = $client->request('get', $endPoint, [
				'headers' => [
					'gmaven.apiKey' => $this->get_config('key'),
					'Content-Type'  => 'application/json'
				]
			]);
		//}
		//catch(\GuzzleHttp\Exception\ClientException $e){
		//	$this->handleError($e);
		//}
		//catch(\GuzzleHttp\Exception\ServerException $e){
		//	$this->handleError($e);
		//}

		// Return response data
		return $this->getResponse($response); 
	}

	/**
	 * Post data
	 */
	private function post($endPoint, $postFields = [])
	{
		// Clean array
		$postFields = array_filter($postFields);

		//print(json_encode($postFields, JSON_PRETTY_PRINT)); die();

		// Set and filter post data
		$clientDataArray = [
			'base_uri' => 'https://www.gmaven.com/api/',
			'json'     => $postFields
		];

		// Go guzzle
		//try{
			// Setup Guzzle
			$client = new \GuzzleHttp\Client($clientDataArray);
			$response = $client->request('post', $endPoint, [
				'headers' => [
					'gmaven.apiKey' => $this->get_config('key'),
					'Content-Type'  => 'application/json'
				]
			]);
		//}
		//catch(\GuzzleHttp\Exception\ClientException $e){
		//	$this->handleError($e);
		//}
		//catch(\GuzzleHttp\Exception\ServerException $e){
		//	$this->handleError($e);
		//}

		// Return response data
		return $this->getResponse($response); 
	}

	/**
	 * Format and return a request
	 *
	 * @param Object Guzzle Response Object
	 */
	private function getResponse($response)
	{
		// Get returned status code of request
		$s = $response->getStatusCode();

		// Normal response
		if($s == 200){

			// Clean content type
			$contentType = strtolower($response->getHeader('Content-Type')[0]);

			// Action by content type
			switch($contentType){
				
				// Json
				case 'application/json; charset=utf-8' :
				return json_decode($response->getBody()->getContents(), false);
				
				// Unknown
				default :
				return $response->getBody()->getContents();
			}
		}

		else{

			// Something went wrong
			switch($s){
	      // Images that dont exist return false
	      case '404' : return false; break;
	      // Key is probably wrong
	      case '403' : $this->cli->error("No permission"); break;
	      // Where did Gmaven go?
	      case '502' : $this->cli->error("Bad Gateway"); break;
	      // Where did Gmaven go?
	      case '503' : $this->cli->error("Gmaven service is unavailable or overloaded, please try again later."); break;
	      case '500' : $this->cli->error("Gmaven service error."); break;
	      // Something else went wrong
	      default : $this->cli->error($r);
	    }
		}
	}

	/**
	 * Log and show errors
	 */
	private function handleError($e){
		return false;
	}
}