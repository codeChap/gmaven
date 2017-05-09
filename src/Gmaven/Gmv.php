<?php

/**
 * PHP library to communicate GMaven (https://www.gmaven.com)
 *
 * @author     CodeChap
 * @license    MIT
 * @copyright  2016 CodeChap
 * 
 */

namespace CodeChap\Gmaven;

Class Gmv
{
	/**
	 * Default method
	 */
	private $method = "GET";

	/**
	 * Body parmams to parse to Gmaven
	 */
	private $params = false;

	/**
	 * Holds the team members
	 */
	private $team = false;

	/**
	 * Source fields to parse to Gmaven
	 */
	private $sourceFields = false;

	/**
	 * The Page number
	 */
	private $page = false;

	/**
	 * Results per page
	 */
	private $size = false;

	/**
	 * Sort by
	 */
	private $sortBy = false;

	/**
	 * Default config settings
	 */
	private $config = [
		'url' => "https://www.gmaven.com/api/",
		'key' => false
	];

	/** 
	 * Sets up the Gmaven Object
	 *
	 */ 
	public function __construct($config = [])
	{
  	// Order of condif addition is important, do not change this.
  	$this->config = $config + $this->config;

  	// Record the key so that we can load images later
  	$keyFile = __DIR__ . DIRECTORY_SEPARATOR . 'private/key.php';
  	$contents = '<?php return "'.$this->config['key'].'"; ?>';
  	file_put_contents($keyFile, $contents);
	}

	/** 
	 * Gets a new instance of Gmaven.
	 *
	 * @param  array  Configuration settings
	 * @return object
	 *
	 */ 
	public static function instance($config = [])
	{
    return new static($config);
	}

	/**
	 * Get all available provinces and property types
	 *
	 * @return  object
	 */
	public function getAggregates()
	{
		// Set method and endpoint
		$this->method = "POST";
		$this->endpoint = "data/default/property/aggregates";

		// Set source fields
		$this->sourceFields = [
			"basic.primaryCategory",
			"basic.province"
		];

		// Set request body
		$this->params = [
			'aggregates' => [
				'basic.primaryCategory' => 1,
				'basic.province'        => 1
			],
			'size' => -1
		];

		// Go
		$result = $this->execute();

  	// Pull types
  	$types = array_filter(
  		$result->aggregates->{'basic.primaryCategory$$distinct'}
  	);

  	// Pull provinces
  	$provinces = array_filter(
  		$result->aggregates->{'basic.province$$distinct'}
  	);

  	// Done
  	return (object) ['types' => $types, 'provinces' => $provinces];
	}

	/**
	 * Gets the suburbs of a province
	 *
	 * @param   string $province The province to pull suburbs for
	 * @return  object
	 */
	public function getSuburbsOf($province)
	{
		// Set method and endpoint
		$this->method = "POST";
		$this->endpoint = "data/default/property/aggregates";

		// Set source fields
		$this->sourceFields = [
			'id',
			'basic.suburb'
		];

		// Set request body
		$this->params = [
			'query' => [
				'basic.province' => ["\$in" => $province],
			],
			'aggregates' => [
				'basic.suburb' => 1,
			],
			'size' => -1
		];

		// Go 
		$result = $this->execute();
  	
  	// Pull suburbs
  	$suburbs = array_filter(
  		$result->aggregates->{'basic.suburb$$distinct'}
  	);

  	// Done
  	return (object) ['suburbs' => $suburbs];
	}

	/**
	 * Search Gmaven for properties
	 *
	 * @param   array  $search An array of information to search against
	 * @param   int    $page   The page number
	 * @param   int    $size   The number of results per page
	 * @return  object
	 */
	public function search($search, $page = 1, $size = 10, $sortBy = false, $desc = true)
	{
		// Set method and endpoint
		$this->method = "POST";
		$this->endpoint = "data/default/property/search";

		// Set source fields
		$this->sourceFields = "Compiled";

		/**
		 * Set request body
		 * Values that are false are removed from this array, if you want to send false to Gmaven is must be set as a string here.
		 */
		$this->params = [
			"query" => array_filter(
				[
			    "vacancy.currentVacantArea" 		=> ! empty($search->rentals) ?   ["\$gte" => 1]                                     : false, // Rentals
			    "basic.forSale" 								=> ! empty($search->sales) ?     ["\$eq" => true]                                   : ["\$in" => ["\$null", 'false']], // Sales
			    "basic.primaryCategory" 				=> ! empty($search->types) ?     ["\$in" => $search->types, "\$notNull" => 'true']  : false, // Types
			    "basic.province"								=> ! empty($search->provinces) ? ["\$in" => $search->provinces]                     : false, // Provinces
			    "basic.suburb"  								=> ! empty($search->suburbs) ?   ["\$in" => $search->suburbs]                       : false, // Suburbs
			    "basic.city" 										=> ! empty($search->cities) ?    ["\$in" => $search->cities]                        : false, // Cities
			    "sales.askingPrice" 						=> ! empty($search->sales) ?     ["\$notNull" => 'true']                            : false, // Asking price when for sale
			    "vacancy.weightedAskingRental" 	=> empty($search->sales) ?       ["\$notNull" => 'true']                            : false // Asking price when for rent
		    ]
		  )
    ];

   	// Append Size of property
   	if(isset($search->size[0])){
			if($search->size[0] > 0){
				$this->params['query']['vacancy.currentVacantArea']["\$gte"]  = $search->size[0];
			}
		}else{
			
		}

		if(isset($search->size[0])){
			if($search->size[1] > 0){
				$this->params['query']['vacancy.currentVacantArea']["\$lte"]  = $search->size[1];
			}
		}else{
			
		}

		// Set page and results per page
		$this->page = $page;
		$this->size = $size;

		// Set sort field
		if($sortBy){
			$this->sortBy = [$sortBy => $desc];
		}

		// Go 
		$result = $this->execute();

		//print "<pre>"; print_r($result); print "</pre>"; die();

		// Reset 
		$this->sourceFields = false;
		$this->query = false;
		$this->page = false;
		$this->size = 1;

		// Get the first image
		if(count($result)){
			foreach($result->list as $k => $v){
				if($images = $this->getImagesOf($v->id) and count($images) > 0){
					$result->list[$k]->first = $images[0];
				}
				else{
					$result->list[$k]->first = false;
				}
			}
		}

  	// Done
  	return (object) ['results' => $result->list, 'md' => $result->md];
	}

	/**
	 * Gets information on a property
	 *
	 * @param   string  $pid      The property id to pull associated information on
	 * @return  object
	 */
	public function property($pid)
	{
		// Set method and endpoint
		$this->method = "POST";
		$this->endpoint = "data/default/property/search";

		// Set source fields
		$this->sourceFields = 'Compiled';

		// Set request body
		$this->params = [
			"query" => array_filter([
					"id" => ["\$eq" => $pid]
		  	]
		  )
    ];

		// Go 
		$result = $this->execute()->list[0];

		// Reset 
		$this->sourceFields = false;
		$this->query = false;
		$this->page = false;
		$this->size = 1;

		// Get images
		if($images = $this->getImagesOf($result->id, 9, 5, 'medium') and count($images) > 0){
			$result->images = $images;
		}
		else{
			$result->images = [];
		}

  	// Done
  	return (object) ['result' => $result];
	}

	/**
	 * Get current team of users
	 */
	public function getTeam()
	{
		if($this->team == false){
			
			// Set method and endpoint
			$this->method = "GET";
			$this->endpoint = "cre/user/team/current/user";
			$this->sourceFields = false;
			$this->params = false;
			$this->page = false;
			$this->size = false;

			// Set team
			$this->team = (object) $this->execute();
		}
		
		// Done
		return $this->team;
	}

	/**
	 * Find the broker of the team
	 *
	 * @param Int   $pid  The property id
	 *
	 */
	public function getBrokers($pid)
	{
		// Variable to return
		$brokers = [];

		// Get the team
		$team = $this->getTeam();

		// Check that we have something to search against
		if(count($team)){

			// Set method and endpoint
			$this->method = "GET";
			$this->endpoint = "data/entity/property/".$pid."/responsibility";
			$result = $this->execute();

			// Match up
			if(count($result->list)){
				foreach($result->list as $l){
					foreach($team as $member){
						if($l->userDomainKey == $member->_id){
							$member->responsibility = $l->responsibility;
							$brokers[] = $member;
						}
					}
				}
			}

			// Format the phone numbers
			if(count($brokers)){
				foreach($brokers as $k => $b){
					
					if( ! empty($b->cell)){
						$cell = (substr($b->cell, 0, 1) == 0) ? $b->cell : '0'.$b->cell;
						$brokers[$k]->cell = preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $cell);
					}
					
					if( ! empty($b->tel)){
						$tel = (substr($b->tel, 0, 1) == 0) ? $b->tel : '0'.$b->tel;
						$brokers[$k]->tel = preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $tel);
					}
				}
			}
		}

		// Done
		return $brokers;
	}

	/**
	 * Pulls featured properties
	 */
	public function featured($search = [], $page = 1, $size = 10, $sortBy = 'vacancy.weightedAskingRental', $desc = 'false')
	{
		// Use curl
		$this->useCurl = true;

		// Set method and endpoint
		$this->method = "POST";
		$this->endpoint = "data/default/property/search";

		// Set source fields
		$this->sourceFields = "Compiled";

		/**
		 * Set request body
		 * Values that are false are removed from this array, if you want to send false to Gmaven is must be set as a string here!
		 */
		$this->params = [
			"query" => array_filter(
				[
			    //"basic.forSale" 								=> ["\$in" => ["\$null", 'false']],
			    "vacancy.weightedAskingRental" 	=> ["\$notNull" => 'true'],
			    "isArchived"										=> ["\$null" 	=> 'true']
		    ]
		  )
    ];

   	// Append Size of property
   	if(isset($search->size[0])){
			if($search->size[0] > 0){
				$this->params['query']['basic.gla']["\$gte"] = $search->size[0];
			}
		}

		if(isset($search->size[0])){
			if($search->size[1] > 0){
				$this->params['query']['basic.gla']["\$lte"] = $search->size[1];
			}
		}

		// Set page and results per page
		$this->page = $page;
		$this->size = $size;

		// Set sort field
		if($sortBy){
			$this->sortBy = [$sortBy => $desc];
		}

		// Go 
		$result = $this->execute();

		// Get the first image
		if(count($result)){
			foreach($result->list as $k => $v){
				if($images = $this->getImagesOf($v->id) and count($images) > 0){
					$result->list[$k]->first = $images[0];
				}
				else{
					
					// Non images dont belong in a featured list
					//unset($result->list[$k]);
				}
			}
		}

  	// Done
  	return (object) ['results' => $result->list, 'md' => $result->md];
	}

	/**
	 * Gets the images of a property
	 *
	 * @param   string  $pid      The property id to pull associated images
	 * @param   int     $limit    The number of images to pull
	 * @param   int     $rating   The rating of the images to pull
	 * @param   string  $size     The profile size to set the images to, ie small, medium and large
	 * @return  array
	 */
	public function getImagesOf($pid, $limit = 1, $rating = 5, $size = 'small')
	{
		// Set endpoint of images
		$this->endpoint = "data/content/entity/property/search";

		// Set request body
		$this->params = [
			'entityDomainKeys' 	=> [$pid],
			'contentCategory' 	=> 'Image',
	  	'metadata' 					=> ['Rating' => $rating],
	  	'limit' 						=> $limit
		];

		// Go
		$result = $this->execute();

		// Append URL
		foreach($result->list as $k => $v){
			$imgs[] = '{{path}}image.php?c='.$v->contentDomainKey.'&s='.$size.'&m='.round($v->updated);
		}

  	// Done
  	return isset($imgs) ? $imgs : [];
	}

	/**
	 * Pull available space for a single property
	 * BASIC EXAMPLE
		$r = `
		curl -H "gmaven.apiKey:PropData:5abd5f3137b54f8fbe600d9b958784a9zpded3d3462baf493bb9516a0a9476679a" \
		-H "Content-Type:application/json" \
		-d '{"query":{"propertyId":{"\$eq":"16c751e3-2df9-40c5-903e-f6132172f1ce"}},"sourceFields":["id", "unitDetails.gla", "unitDetails.customReferenceId", "unitDetails.primaryCategory", "vacancy.unitDetails.gmr", "vacancy.marketing.availableType"]}' \
		-X POST https://www.gmaven.cooso.devm/api/data/default/propertyUnit/search
		`;
		$r = json_decode($r);
		print json_encode($r, JSON_PRETTY_PRINT); die();
	 */
	public function getUnitsof($pid)
	{
		// Set method
		$this->method = "POST";

		// Set endpoint of images
		$this->endpoint = "data/custom/propertyUnit/search";

		// Set source fields
		//$this->sourceFields = ["id", "unitDetails.gla", "unitDetails.customReferenceId", "unitDetails.primaryCategory", "vacancy.unitDetails.gmr", "vacancy.marketing.availableType"];
		$this->sourceFields = "Unit";

		// Query
		$this->params = [
			"query" => array_filter(
				[
			    "propertyId" 	     => ["\$eq"   => $pid],
			    //"vacancy.forLease" => ["\$eq"   => 'true'],
			   	"isArchived"	     => ["\$in" => ["\$null", "false"]]
		    ]
		 )
		];

		// Go
		$result = $this->execute();

		//print "<pre>"; print_r($result); print "</pre>"; die();

		// Done
		return $result;
	}

	/**
	 * Execute the query using Guzzle
	 *
	 * @return  mixed
	 */
	public function execute()
	{
		// Check for key
		if(empty($this->config['key'])){
			throw new \Exception("Key not set.");
		}

		// Set params
		$params = [];

		// Add Query
		if($this->params){
			$params = $this->array_filter_recursive($this->params);
		}

		// Add Source fields
		if($this->sourceFields){
			if(is_array($this->sourceFields)){
				$fields = $this->sourceFields;
			}
			else if(is_string($this->sourceFields)){
				$sourceFieldFileName = ucFirst(strtolower($this->sourceFields));
				if(file_exists(__DIR__.'/Sourcefields/'.$sourceFieldFileName.'.php')){
	  			$fields = include(__DIR__.'/Sourcefields/'.$sourceFieldFileName.'.php');
	  		}else{
	  			throw new \Exception("SourceFields file (".$sourceFieldFileName.".php) not found");
	  		} 
			}
			$params['sourceFields'] = $fields;
		}

		// Add pagination
		if($this->page and $this->size){
			$params['page'] = ["number" => $this->page, "size" => $this->size];
		}

		// Sort and then add sort by
		ksort($params);

		// Set sort field
		if($this->sortBy){
			$params["sortFields"] = [[
				"field" => key($this->sortBy),
				"desc"  => (current($this->sortBy)) ? true : false
			]];
		}

		// Build array for client
		$clientDataArray = [
			'base_uri' => $this->config['url'],
			'on_stats' => function (\GuzzleHttp\TransferStats $stats) use (&$url) {
      	$url = $stats->getEffectiveUri();
    	}
		];

		// Does client require params
		if(count($params)){
			$clientDataArray['json'] = $params;
		}

		// Set client
		$client = new \GuzzleHttp\Client($clientDataArray);

		// Log perams request @todo
		if(php_sapi_name() === 'cli' ){
			//print 'METHOD:' . $this->method . PHP_EOL;
			//print 'URL:' . $this->config['url'] . $this->endpoint . PHP_EOL;
			//print json_encode($params, JSON_PRETTY_PRINT);
			//die();
		}

		// Set response
		try{
			$response = $client->request($this->method, $this->endpoint, [
				'headers' => [
					"gmaven.apiKey" => $this->config['key'],
		  		"Content-Type"  => "application/json"
				]
			]);
		}
		catch(\GuzzleHttp\Exception\ClientException $e){

			// Pull error and kick up a fuss
			$errorMessage = ['Gmaven client endpoint error'];
			$response = $e->getResponse();
			$r = $response->getBody()->getContents();
			if($jsonErrorMessage = json_decode($r)){
				$errorMessage[] = ': '.$jsonErrorMessage->Message;
			}

			// Log it
			$this->log($url, $response->getStatusCode(), implode($errorMessage));

			// Throw it
    	throw new \Exception(implode($errorMessage), $response->getStatusCode());
		}
		catch(\GuzzleHttp\Exception\ServerException $e){

			// Pull error and kick up a fuss
			$errorMessage = ['Gmaven error'];
			$response = $e->getResponse();
			$r = $response->getBody()->getContents();
			if($jsonErrorMessage = json_decode($r)){
				$errorMessage[] = ': '.$jsonErrorMessage->Message;
			}

			// Log it
			$this->log($url, $response->getStatusCode(), implode($errorMessage));

			// Throw it
    	throw new \Exception(implode($errorMessage), $response->getStatusCode());
		}

		// Get returned status code of request
		$statusCode = $response->getStatusCode();

		// Normal response
		if($statusCode == 200){

			// Clean content type
			$contentType = strtolower($response->getHeader('Content-Type')[0]);
			
			// Action by content type
			switch($contentType){
				
				// Decode Json output
				case 'application/json; charset=utf-8' :

					//print json_encode(json_decode($response->getBody()->getContents()), JSON_PRETTY_PRINT); die();

					// Done
					$finalOutput = json_decode($response->getBody()->getContents(), false);
				break;

				// Binaray content
				$finalOutput = $response->getBody()->getContents();
			}
		}

		// Error
		else{

			switch($statusCode)
			{
				// Images that dont exist return false
				case '404' :
					return false; 
				break;

				// Key is probably wrong
				case '403' :
				throw new \Exception("No permission");
				break;

				// Where did Gmaven go?
				case '502' :
				throw new \Exception("Bad Gateway");
				break;

				// Where did Gmaven go?
				case '503' :
				throw new \Exception("Gmaven service is unavailable or overloaded, please try again later.");
				break;

				case '500' :
				throw new \Exception("Gmaven service error, please report this error.");
				break;

				// Something else went wrong
				default :
				throw new \Exception($response->getBody()->getContents());
			}
		}

		// Log perams request @todo
		if(php_sapi_name() === 'cli'){
			// print PHP_EOL . "RESULT: " . PHP_EOL;
			// print_r($finalOutput);
		}

		// Get response
		return $finalOutput;
	}

	/**
	 * Fitler an array recursivley
	 *
	 * @param array $input Array to filter
	 */
	private function array_filter_recursive($input) 
  { 
    foreach ($input as &$value) {
      if (is_array($value)){
        $value = $this->array_filter_recursive($value); 
      } 
    } 
    return array_filter($input); 
  } 

	/**
	 * Log something
	 *
	 * @param string $url     The url that we are loggin against
	 * @param string $code    The error code
	 * @param string $message The error message
	 */
	private function log($url, $code, $message)
	{
		@file_put_contents(__DIR__.'/error.log', (date('d F Y h:i:s') . ': ' . $url . ': ' . $code . ' - ' . $message . PHP_EOL), FILE_APPEND);
	}

	// Prevent cloning and unserializing
	private function __clone(){}
	private function __wakeup(){}
}