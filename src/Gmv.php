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

	/**
	 * Constructor
	 */
	public function __construct($config)
	{
		// Things we need set
		$this->cli = new \League\CLImate\CLImate;
		$this->time = time();

		// Call parent constructor
		parent::__construct($config);
	}

	/**
	 * Give info
	 */
	public function __desctruct()
	{
		$this->cli->green('Took ' . time() - $this->time . ' seconds');
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
		if(false){
			$this->getCategories();
			$this->getProvinces();
			$this->getSuburbs();
			$this->getCities();
		}

		// Start fetching property data
		if(false){
			$this->resetProperties();
		}

		// Start fetching unit data
		if(false){
			$this->resetUnits();
		}

		// Start fetching images
		if(true){
			$this->resetImages();
		}

		// Get updated properties
		if(false){
			$this->updateProperties();
		}
	}

	/**
	 * Get all categories
	 */
	private function getCategories()
	{
		$this->endPoint = 'data/default/property/aggregates';
		$this->post = true;
		$this->size = -1;
		$this->aggregates = [
			'basic.primaryCategory' => 1
		];
		$data = $this->call();
		$data = array_filter($data->aggregates->{'basic.primaryCategory$$distinct'});

		// Insert
		$db = Db::forge($this->get_config());
		$db->query("TRUNCATE TABLE `#gmaven_categories`")->exec();
		foreach($data as $category){
			$db->query("INSERT INTO `#gmaven_categories` (`category`, `updated_at`) VALUES('".$category."', ".$this->time.")")->exec();
		}
	}

	/**
	 * Get all provinces
	 */
	private function getProvinces()
	{
		$this->endPoint = 'data/default/property/aggregates';
		$this->post = true;
		$this->size = -1;
		$this->aggregates = [
			'basic.province' => 1
		];
		$data = $this->call();
		$data = array_filter($data->aggregates->{'basic.province$$distinct'});

		// Insert
		$db = Db::forge($this->get_config());
		$db->query("TRUNCATE TABLE `#gmaven_provinces`")->exec();
		foreach($data as $province){
			$db->query("INSERT INTO `#gmaven_provinces` (`province`, `updated_at`) VALUES('".$province."', ".$this->time.")")->exec();
		}
	}

	/**
	 * Get all suburbs
	 */
	private function getSuburbs()
	{
		$this->endPoint = 'data/default/property/aggregates';
		$this->post = true;
		$this->size = -1;
		$this->aggregates = [
			'basic.suburb' => 1
		];
		$data = $this->call();
		$data = array_filter($data->aggregates->{'basic.suburb$$distinct'});

		// Insert
		$db = Db::forge($this->get_config());
		$db->query("TRUNCATE TABLE `#gmaven_suburbs`")->exec();
		foreach($data as $suburb){
			$db->query("INSERT INTO `#gmaven_suburbs` (`suburb`, `updated_at`) VALUES('".$suburb."', ".$this->time.")")->exec();
		}
	}

	/**
	 * Get all cities
	 */
	private function getCities()
	{
		$this->endPoint = 'data/default/property/aggregates';
		$this->post = true;
		$this->size = -1;
		$this->aggregates = [
			'basic.city' => 1
		];
		$data = $this->call();
		$data = array_filter($data->aggregates->{'basic.city$$distinct'});

		// Insert
		$db = Db::forge($this->get_config());
		$db->query("TRUNCATE TABLE `#gmaven_cities`")->exec();
		foreach($data as $city){
			$db->query("INSERT INTO `#gmaven_cities` (`city`, `updated_at`) VALUES('".$city."', ".$this->time.")")->exec();
		}
	}

	/**
	 * Get all properties and re-insert them into the database 
	 */
	private function resetProperties($page = 1)
	{
		// Get total pages first
		$this->endPoint = 'data/default/property/search';
		$this->post = true;
		$this->sourceFields = ['id'];
		$this->pageNumber = 1;
		$this->pageSize = 1;
		$r = $this->call();
		$t = $r->md->totalResults;

		// Info
		$this->cli->green('Fetching '.$t.' properties and associated units.');

		// Now pull everything!
		$this->sourceFields = include(__DIR__.'Fields'.DIRECTORY_SEPARATOR.'Properties.php');
		$this->pageSize = $t;
		$r = $this->call();

		// Check
		if(count($r) == 0){
			$this->cli->error("Nothing from Gmaven...");
			die();
		}

		// Progress bar
		$progress = $this->cli->progress()->total($t);

		// Forge database connection
		$db = Db::forge($this->get_config());

		// Clear out existing entries
		$db->query("TRUNCATE TABLE `#gmaven_properties`")->exec();
		$db->query("TRUNCATE TABLE `#gmaven_property_details`")->exec();

		// Loop over results
		foreach($r->list as $i => $p){

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
	}

	/**
	 * 
	 */
	public function resetUnits()
	{
		// Get total pages first
		$this->endPoint = 'data/custom/propertyUnit/search';
		$this->post = true;
		$this->sourceFields = ['id'];
		$this->query = ["isArchived" => ["\$in" => ["\$null", "false"]]];
		$this->pageNumber = 1;
		$this->pageSize = 1;
		$r = $this->call();
		$t = $r->md->totalResults;

		// Info
		$this->cli->green('Fetching '.$t.' units.');

		// Now pull everything
		$this->sourceFields = include(__DIR__.DIRECTORY_SEPARATOR.'Fields'.DIRECTORY_SEPARATOR.'Units.php');
		$this->pageSize = $t;
		$r = $this->call();

		// Check
		if(count($r) == 0){
			$this->cli->error("Nothing from Gmaven...");
			die();
		}

		// Progress bar
		$progress = $this->cli->progress()->total($t);

		// Forge database connection
		$db = Db::forge($this->get_config());

		// Clear out existing entries
		$db->query("TRUNCATE TABLE `#gmaven_units`")->exec();

		// Loop over results
		foreach($r->list as $i => $u){

			//print "<pre>"; print_r($u); print "</pre>"; die();

			// Find province, city, suburb and category id
			$catId	= $db->query("SELECT `id` FROM `#gmaven_categories` WHERE `category`	= '".(addslashes($u->unitDetails->primaryCategory))."'")->get_one('id');

			// Insert data
			$q = "
	      INSERT INTO `#gmaven_units`
	      (`gmv_id`, `propertyId`, `customReferenceId`, `category_id`, `gla`, `gmr`, `availableType`, `updated_at`, `gmv_updated`)
	      VALUES (
	        '".addslashes($u->id)."',
	        '".(isset($u->propertyId) ? addslashes($u->propertyId) : 'NULL')."',
	        '".addslashes($u->unitDetails->customReferenceId)."',
	        ".$catId.",
	        ".$u->unitDetails->gla.",
	        ".$u->vacancy->unitDetails->gmr.",
	        '".addslashes($u->vacancy->marketing->availableType)."',
	        ".$this->time.",
	        ".$u->_updated."
	      );
			";

			// Insert
			$db->query($q)->exec();

			// Update progress bar
			$progress->current($i);
		}
	}

	public function resetImages()
	{
		// Get total pages first
		$this->endPoint = 'data/content/entity/property/search';
		$r = $this->call();
		$t = count($r);

		// Info
		$this->cli->green('Fetching '.$t.' images.');

		foreach($r as $i => $img){

			print "<pre>"; print_r($img); print "</pre>"; die();

		}
	}

	/**
	 * @todo
	 */
	public function updateProperties()
	{
		$time = strtotime('-1 day');

		// Get total pages first
		$this->endPoint = 'data/default/property/search';
		$this->post = true;
		$this->sourceFields = ['id'];
		$this->query = ['_updated' => ['$gte' => $time] ];
		$this->pageNumber = 1;
		$this->pageSize = 1;
		$r = $this->call();
		$t = $r->md->totalResults;

		// Info
		$this->cli->green('Checking '.$t.' properties and associated units for updates.');

		// Pull everything!
		$this->endPoint = 'data/default/property/search';
		$this->post = true;
		$this->sourceFields = 
		$this->pageNumber = 1;
		$this->pageSize = 1;
		$r = $this->call();

		print "<pre>"; print_r($r); print "</pre>"; die();

		// Loop over results
		foreach($r->list as $i => $p){

			print "<pre>"; print_r($p); print "</pre>";
		}
	}

	/**
	 * Execute a call to Gmaven
	 */
	private function call()
	{
		// Filter post data
		$postFields = array_filter([
	  	'sourceFields' => $this->sourceFields,
	  	'aggregates'	 => $this->aggregates,
	  	'query'	       => $this->query,
	  	'size'         => $this->size,
	  	'page'         => ['number' => $this->pageNumber, 'size' => $this->pageSize]
		]);

		//print "<pre>"; print_r($postFields); print "</pre>"; die();

		// Get cURL resource
		$curl = curl_init();
		
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, [
			CURLOPT_URL	            => 'https://www.gmaven.com/api/'.$this->endPoint,
	    CURLOPT_RETURNTRANSFER	=> 1,
	    CURLOPT_POST	          => $this->post,
	    CURLOPT_POSTFIELDS	    => json_encode($postFields), 
	    CURLOPT_HTTPHEADER	    => [
	    	'gmaven.apiKey: '.$this->get_config('key'),
	    	'Content-Type: application/json'
	    ]
		]);
		
		// Send the request & get status code
		$r = curl_exec($curl);
		$s = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		// Close request to clear up some resources
		curl_close($curl);

		// Return as array if ok
		if($s == 200){
			return json_decode($r);
		}

		// Something went wrong
		switch($s){
			
			// Images that dont exist return false
			case '404' :
				return false; 
			break;

			// Key is probably wrong
			case '403' :
			$this->cli->error("No permission");
			break;

			// Where did Gmaven go?
			case '502' :
			$this->cli->error("Bad Gateway");
			break;

			// Where did Gmaven go?
			case '503' :
			$this->cli->error("Gmaven service is unavailable or overloaded, please try again later.");
			break;

			case '500' :
			$this->cli->error("Gmaven service error, please report this error.");
			break;

			// Something else went wrong
			default :
			$this->cli->error($r);
		}

		return false;

		// Done
		//return json_encode(json_decode($resp, true), JSON_PRETTY_PRINT);
	}
}