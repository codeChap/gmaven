<?php

/**
 * PHP library to communicate GMaven (https://www.gmaven.com)
 *
 * @author     CodeChap
 * @license    MIT
 * @copyright  2017 CodeChap
 *
 */

namespace CodeChap\Gmv;

class Gmv extends Arc\Singleton
{
	/**
	 * Store the start time
	 */
	public $time = false;

	/**
	 * Command lime object
	 */
	public $cli = false;

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
	 * Destructor
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

		// Pre-build return array
		$totals = [
			'synchronized_property_types'         => 0,
			'synchronized_provinces'              => 0,
			'synchronized_suburbs'                => 0,
			'synchronized_cities'                 => 0,
			'synchronized_properties'             => 0,
			'synchronized_units'                  => 0,
			'synchronized_images'                 => 0,
			'synchronized_images_units'           => 0,
			'synchronized_brokers_to_properties'  => 0,
			'synchronized_properties_to_contacts' => 0
		];

		// Start fetching aggregates data
		if($this->get_config('sync_aggregates')){
			$totals['synchronized_property_types'] = $this->getCategories();
			$totals['synchronized_provinces'] = $this->getProvinces();
			$totals['synchronized_suburbs'] = $this->getSuburbs();
			$totals['synchronized_cities'] = $this->getCities();
		}

		// Start fetching property data
		if($this->get_config('sync_properties')){
			$totals['synchronized_properties'] = $this->getProperties();
		}

		// Start fetching unit data
		if($this->get_config('sync_units')){
			$totals['synchronized_units'] = $this->getUnits();
		}

		// Start fetching images
		if($this->get_config('sync_images')){
			$totals['synchronized_images'] = $this->getImages();
		}

		if($this->get_config('sync_images_units')){
			$totals['synchronized_images_units'] = $this->getUnitImages();
		}

		// Start matching brokers to properties
		if($this->get_config('sync_brokers_to_properties')){
			$totals['synchronized_brokers_to_properties'] = $this->getBrokers();
		}

		// Start matching contacts to properties
		if($this->get_config('sync_properties_to_contacts')){
			$totals['synchronized_properties_to_contacts'] = $this->getContacts();
		}

		// Done
		return [
			'time'   => ceil((time()-$this->time) / 60) . ' minutes.',
			'totals' => $totals,
		];
	}

	/**
	 * Do a patial merge with Gmaven
	 *
	 * @param Int Number of hours in the past to sync by. Your cronjob should then update by this number so every 2 hours by default
	 *
	 * @return Int Total
	 */
	public function partial($hours = 2)
	{
		$lastSyncDate = strtotime("-".$hours." hours");

		// Start fetching images - good to go
		if(true){
			$totals['synchronized_images'] = $this->getImages();
		}

		// Start fetching aggregates data
		if(false){
			$totals['property_types'] = $this->getCategories();
			$totals['provinces'] = $this->getProvinces();
			$totals['suburbs_of_those_provinces'] = $this->getSuburbs();
			$totals['cities_of_those_provinces'] = $this->getCities();
		}

		// Start fetching property data
		if(false){
			$totals['synchronized_properties'] = $this->getProperties($lastSyncDate);
		}

		// Start fetching unit data
		if(false){
			$totals['synchronized_units'] = $this->getUnits($lastSyncDate);
		}

		// Done
		return [
			'time'   => ceil(time()-$this->time) . ' seconds.',
			'totals' => $totals,
		];
	}

	/**
	 * Get all categories
	 *
	 * @return Int Total
	 */
	private function getCategories()
	{
		// Info
		$this->cli->green('Fetching categories');

		// Call Gmaven
		$r = $this->post('data/default/property/aggregates', [
			'size'       => -1,
			'aggregates' => [
				'basic.primaryCategory' => 1
			]
		]);

		// Gather data
		$data = array_filter($r->aggregates->{'basic.primaryCategory$$distinct'});

		// Find total
		$t = count($data);

		// Progress bar
		$progress = $this->cli->progress()->total($t);

		// Insert
		$db = Db::forge($this->get_config());
		$db->query("TRUNCATE TABLE `#gmaven_categories`")->exec();
		foreach($data as $i => $category){
			$db->query("INSERT INTO `#gmaven_categories` (`category`, `updated_at`) VALUES('".addslashes($category)."', ".$this->time.")")->exec();
			$progress->advance();
		}

		// Return total
		return $t;
	}

	/**
	 * Get all provinces
	 *
	 * @return Int Total
	 */
	private function getProvinces()
	{
		// Info
		$this->cli->green('Fetching provinces');

		// Call Gmaven
		$r = $this->post('data/default/property/aggregates', [
			'size'       => -1,
			'aggregates' => [
				'basic.province' => 1
			]
		]);

		// Gather data
		$data = array_filter($r->aggregates->{'basic.province$$distinct'});
		$t = count($data);

		if($t){

			// Progress bar
			$progress = $this->cli->progress()->total($t);

			// Insert
			$db = Db::forge($this->get_config());
			$db->query("TRUNCATE TABLE `#gmaven_provinces`")->exec();
			foreach($data as $i => $province){
				$db->query("INSERT INTO `#gmaven_provinces` (`province`, `updated_at`) VALUES('".addslashes($province)."', ".$this->time.")")->exec();
				$progress->advance();
			}
		}

		// Return total
		return $t;
	}

	/**
	 * Get all suburbs
	 *
	 * @return Int Total
	 */
	private function getSuburbs()
	{
		// Database
		$db = Db::forge($this->get_config());

		// Clear out table
		$db->query("TRUNCATE TABLE `#gmaven_suburbs`")->exec();

		// We need a list of property ids
		$provinces = $db->query("SELECT `id`, `province` FROM `#gmaven_provinces`")->get();

		// Count
		$tt = [];

		// Call Gmaven on each province
		foreach($provinces as $p){

			// Info
			$this->cli->green('Fetching suburbs of ' . $p['province']);

			// Call
			$r = $this->post('data/default/property/aggregates', [
				'size'  => -1,
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
			$t = count($data);
			$tt[] = $t;

			if($t){

				// Progress bar
				$progress = $this->cli->progress()->total($t);

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
					$progress->advance();
				}
			}
		}

		// Return totals
		if(count($tt)){
			return array_sum($tt);
		}
		else{
			return 0;
		}
	}

	/**
	 * Get all cities
	 *
	 * @return Int Total
	 */
	private function getCities()
	{
		// Info
		$this->cli->green('Fetching cities');

		// Call Gmaven
		$r = $this->post('data/default/property/aggregates', [
			'size'       => -1,
			'aggregates' => [
				'basic.city' => 1
			]
		]);

		// Gather data
		$data = array_filter($r->aggregates->{'basic.city$$distinct'});
		$t = count($data);

		if($t){

			// Progress bar
			$progress = $this->cli->progress()->total($t);

			// Insert
			$db = Db::forge($this->get_config());
			$db->query("TRUNCATE TABLE `#gmaven_cities`")->exec();
			foreach($data as $i => $city){
				$db->query("INSERT INTO `#gmaven_cities` (`city`, `updated_at`) VALUES('".addslashes($city)."', ".$this->time.")")->exec();
				$progress->advance();
			}
		}

		// Return total
		return $t;
	}

	/**
	 * Get all properties and re-insert them into the database
	 *
	 * Total results may differ from what you see in CRE, Gmaven apply filters to the API to ensure that no obviously "incomplete" 
	 * properties are displayed (e.g. ones that're missing critical location or price information) which would account for this 
	 * difference.
	 *
	 * @param Date of when to start syncing
	 *
	 * @return Int Total
	 */
	private function getProperties($fromWhen = false)
	{
		// Vars
		$query = [];
		$from = [];

		// Partial or full sync
		if($fromWhen){
			$from = [
				"_updated" => ["\$gte" => $fromWhen]
			];
		}

		// Call Gmaven to get total properties including archived ones
		$r = $this->post('data/default/property/search', [
			'sourceFields' => ['id'],
			'query'        => $query + $from,
			'page'         => ['number' => 1, 'size' => 1]
		]);

		// Find total
		$t = $r->md->totalResults;

		// Info
		$this->cli->green('Fetching '.$t.' properties.');

		// Only continue if there is work to be done
		if($t == 0){
			return;
		}

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
			'query' => $query + $from,
			'page'  => ['number' => 1, 'size' => $t]
		]);

		// Progress bar
		$progress = $this->cli->progress()->total($t);

		// Forge database connection
		$db = Db::forge($this->get_config());

		// Clear out existing entries when fetching everything
		if($fromWhen == false){
			$db->query("TRUNCATE TABLE `#gmaven_properties`")->exec();
			$db->query("TRUNCATE TABLE `#gmaven_property_details`")->exec();
		}

		// Loop over results
		foreach($r->list as $i => $p){

			// Try find the entry
			if($fromWhen){
				if($exists = $db->query("SELECT `id` FROM `#gmaven_property_details` WHERE `gmv_id` = '".addslashes($p->id)."'")->get()){

					// Find pid
					$property = $db->query("SELECT `id` FROM `#gmaven_properties` WHERE `did` = ".$exists[0]['id'])->get();
					$pid = $property[0]['id'];
					$did = $exists[0]['id'];

					// Delete property, property.details & property.units
					$r = "
						BEGIN;
						DELETE FROM `#gmaven_properties` WHERE id = ".$pid.";
						DELETE FROM `#gmaven_property_details` WHERE did = ".$did.";
						DELETE FROM `#gmaven_units` WHERE pid = ".$pid.";
						COMMIT;
					";

					$db->query($r)->exec();
				}
			}

			// Find province, city, suburb and category id
			$catId = $db->query("SELECT `id` FROM `#gmaven_categories` WHERE `category` = '".(addslashes($p->basic->primaryCategory))."'")->get_one('id');
			$pid   = $db->query("SELECT `id` FROM `#gmaven_provinces` WHERE `province`  = '".(addslashes($p->basic->province))."'")->get_one('id');
			$cid   = $db->query("SELECT `id` FROM `#gmaven_cities` WHERE `city`         = '".(addslashes($p->basic->city))."'")->get_one('id');
			$sid   = $db->query("SELECT `id` FROM `#gmaven_suburbs` WHERE `suburb`      = '".(addslashes($p->basic->suburb))."'")->get_one('id');

			// Insert data
			$q = "
			BEGIN;
			INSERT INTO `#gmaven_property_details`
			(`gmv_id`, `name`, `customReferenceId`, `displayAddress`, `marketingBlurb`)
			VALUES (
			 '".addslashes($p->id)."',
			 ".((isset($p->basic->name) and !empty($p->basic->name))                            ? "'".addslashes($p->basic->name)."'"              : 'NULL').",
			 ".((isset($p->basic->customReferenceId) and !empty($p->basic->customReferenceId))  ? "'".addslashes($p->basic->customReferenceId)."'" : 'NULL').",
			 ".((isset($p->basic->displayAddress) and !empty($p->basic->displayAddress))        ? "'".addslashes($p->basic->displayAddress)."'"    : 'NULL').",
			 ".((isset($p->basic->marketingBlurb) and !empty($p->basic->marketingBlurb))        ? "'".addslashes($p->basic->marketingBlurb)."'"    : 'NULL')."
			);
			INSERT INTO `#gmaven_properties`
			(`did`, `lon`, `lat`, `gla`, `currentVacantArea`, `weightedAskingRental`, `for_sale`, `category_id`, `province_id`, `city_id`, `suburb_id` ,`updated_at`, `gmv_updated`)
			VALUES (
			 LAST_INSERT_ID(),
			 ".($p->geo->lon == 0 ? 'NULL' : $p->geo->lon).",
			 ".($p->geo->lat == 0 ? 'NULL' : $p->geo->lat).",
			 ".(!empty($p->basic->gla)                    ? $p->basic->gla                    : 0).",
			 ".(!empty($p->vacancy->currentVacantArea)    ? $p->vacancy->currentVacantArea    : 0).",
			 ".(!empty($p->vacancy->weightedAskingRental) ? $p->vacancy->weightedAskingRental : 0).",
			 ".(!empty($p->basic->forSale)                ? $p->basic->forSale                : 0).",
			 ".$catId.",
			 ".$pid.",
			 ".$cid.",
			 ".$sid.",
			 ".$this->time.",
			 ".(isset($p->updated) ? round($p->_updated) : 0)."
			);
			COMMIT;
			";

			// @todo Do a check and make sure the number of details entries match the number of properties

			// Insert
			$db->query($q)->exec();

			// Update progress bar
			$progress->advance();
		}

		// Done
		return $t;
	}

	/**
	 * Fetch units of properties
	 *
	 * @param Int Timestamp of when to start syncing from
	 */
	public function getUnits($fromWhen = false)
	{
		// Vars
		$query = [];
		$from = [];

		$query = [
			'isArchived' => [
				"\$in" => ["\$null", "false"]
			]
		];

		// Partial or full sync @todo Remove, record a timestamp instead
		if($fromWhen){
			//$from = [
			//	"_updated" => ["\$gte" => $fromWhen]
			//];
		}

		// Call Gmaven to get total properties
		$r = $this->post('data/custom/propertyUnit/search', [
			'sourceFields' => ['id'],
			'query'        => $query + $from,
			'page'         => ['number' => 1, 'size' => 1]
		]);
		$t = $r->md->totalResults;

		// Info
		$this->cli->green('Fetching '.$t.' units.');

		// Only continue if there is work to be done
		if($t == 0){
			return;
		}

		// Now pull everything
		$r = $this->post('data/custom/propertyUnit/search', [
			'sourceFields' => [
				'id',
				'_updated',
				'isArchived',
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
			'query' => $query + $from,
			'page'  => ['number' => 1, 'size' => $t]
		]);

		// Progress bar
		$progress = $this->cli->progress()->total($t);

		// Forge database connection
		$db = Db::forge($this->get_config());

		// Clear out existing entries
		if($fromWhen == false){
			$db->query("TRUNCATE TABLE `#gmaven_units`")->exec();
		}

		// Loop over results
		foreach($r->list as $i => $u){

			$pid = 0;
			$catId = 0;

			if(isset($u->propertyId) and !empty($u->propertyId) ){

				// Find category id
				if(isset($u->unitDetails->primaryCategory) and $catgoryId = addslashes($u->unitDetails->primaryCategory)){
					$catId = $db->query("SELECT `id` FROM `#gmaven_categories` WHERE `category` = '".$catgoryId."'")->get_one('id');
				}

				// Find Property id
				if(isset($u->propertyId) and $propertyId = addslashes($u->propertyId)){
					$pid = $db->query("
						SELECT P.`id` FROM `#gmaven_property_details` D
						LEFT JOIN `#gmaven_properties` P ON P.`did` = D.`id`
						WHERE `gmv_id` = '".$propertyId."'"
					)->get_one('id');

					if(empty($pid)){
						$pid = 0;
					}
				}

				// Check for existing entry
				if($eId = $db->query("SELECT `id` FROM `#gmaven_units` WHERE `gmv_id` = '".$u->id."'")->get_one('id')){
					$db->query("DELETE FROM `#gmaven_units` WHERE `id` = ".$eId)->exec();
				}

				// Insert data
				$q = "
				INSERT INTO `#gmaven_units`
				(`pid`, `category_id`, `gla`, `gmr`, `netAskingRental`, `availableFrom`, `propertyId`, `gmv_id`, `unitId`, `customReferenceId`, `availableType`, `marketingHeading`, `description`, `updated_at`, `gmv_updated`)
				VALUES (
				 ".$pid.",
				 ".$catId.",
				 ".(isset($u->unitDetails->gla)                      ? $u->unitDetails->gla : 0).",
				 ".(isset($u->vacancy->unitDetails->gmr)             ? $u->vacancy->unitDetails->gmr : 0).",
				 ".(isset($u->vacancy->unitDetails->netAskingRental) ? $u->vacancy->unitDetails->netAskingRental : 0).",
				 ".(isset($u->vacancy->marketing->availableFrom)     ? round($u->vacancy->marketing->availableFrom) : 0).",
				 '".$propertyId."',
				 '".addslashes($u->id)."',
				 ".((isset($u->unitDetails->unitId) and !empty($u->unitDetails->unitId))                             ? "'".addslashes($u->unitDetails->unitId)."'"              : 'NULL').",
				 ".((isset($u->unitDetails->customReferenceId) and !empty($u->unitDetails->customReferenceId))       ? "'".addslashes($u->unitDetails->customReferenceId)."'"    : 'NULL').",
				 ".((isset($u->vacancy->marketing->availableType) and !empty($u->vacancy->marketing->availableType)) ? "'".addslashes($u->vacancy->marketing->availableType)."'" : 'NULL').",
				 ".((isset($u->vacancy->sales->marketingHeading) and !empty($u->vacancy->sales->marketingHeading))   ? "'".addslashes($u->vacancy->sales->marketingHeading)."'"  : 'NULL').",
				 ".((isset($u->vacancy->sales->description) and !empty($u->vacancy->sales->description))             ? "'".addslashes($u->vacancy->sales->description)."'"       : 'NULL').",
				 ".$this->time.",
				 ".(isset($u->updated) ? round($u->_updated) : 0)."
				);
				";

				// Insert
				$db->query($q)->exec();
			}

			// Update progress bar
			$progress->advance();
		}

		// Return totals
		return $t;
	}

	/**
	 * Match images to properties
	 */
	public function getImages()
	{
		// Call Gmaven to get total properties
		$r = $this->post('data/content/entity/property/search', [
			'contentCategory' => 'Image',
		]);
		$t = count($r->list);

		// Info
		$this->cli->green('Fetching '.$t.' building images.');

		// Only continue if there is work to be done
		if($t == 0){
			return;
		}

		// Progress bar
		$progress = $this->cli->progress()->total($t);

		// Forge database connection
		$db = Db::forge($this->get_config());

		// Mark all entries as removed
		//$db->query("UPDATE `#gmaven_building_images` SET `removed` = 1")->exec();

		// Clear out existing entries
		$db->query("TRUNCATE TABLE `#gmaven_building_images`")->exec();

		// Loop over results
		foreach($r->list as $i => $img){

			// Insert data
			$q = "
			INSERT INTO `#gmaven_building_images`
			(`entityDomainKey`, `contentDomainKey`, `rating`, `updated_at`, `gmv_updated`)
			VALUES (
			 '".$img->entityDomainKey."',
			 '".$img->contentDomainKey."',
			 ".( isset($img->metadata->Rating) ? $img->metadata->Rating : 0 ).",
			 ".$this->time.",
			 ".( (isset($img->updated) and !empty($img->updated) and $img->updated > 0) ? round($img->updated) : 0 )."
			);
			";

			// Insert
			if( ! $db->query($q)->exec()){
				die($q);
			}

			// Update progress bar
			$progress->advance();
		}

		// Done
		return $t;
	}

	/**
	 * Sync unit images
	 *
	 * @param  
	 * @return 
	 */
	function getUnitImages()
	{
		// Call Gmaven to get total units
		$r = $this->post('data/content/entity/propertyUnit/search', [
			'contentCategory' => 'Image',
		]);
		$t = count($r->list);

		// Info
		$this->cli->green('Fetching '.$t.' unit images.');

		// Only continue if there is work to be done
		if($t == 0){
			return;
		}

		// Progress bar
		$progress = $this->cli->progress()->total($t);

		// Forge database connection
		$db = Db::forge($this->get_config());

		// Clear out existing entries
		$db->query("TRUNCATE TABLE `#gmaven_unit_images`")->exec();

		// Loop over results
		foreach($r->list as $i => $img){

			// Find propertyUnit entityDomainKey
			if( ! empty($img->entities)){
				foreach($img->entities as $e){
					if($e->entityName == 'propertyUnit'){

						// Insert data
						$q = "
						INSERT INTO `#gmaven_unit_images`
						(`entityDomainKey`, `contentDomainKey`, `rating`, `updated_at`, `gmv_updated`)
						VALUES (
						 '".$e->entityDomainKey."',
						 '".$img->contentDomainKey."',
						 ".( isset($img->metadata->Rating) ? $img->metadata->Rating : 0 ).",
						 ".$this->time.",
						 ".( isset($img->updated) ? round($img->updated) : 0)."
						);
						";

						// Insert
						$db->query($q)->exec();
					}
				}
			}

			// Update progress bar
			$progress->advance();
		}

		// Done
		return $t;
	}

	/**
	 * Match brokers to properties
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
		$t = count($list);

		// Info and progress
		$this->cli->green('Match brokers to properties');
		$progress = $this->cli->progress()->total($t);

		if($t == 0){
			return;
		}

		// Clear out existing entries
		$db->query("TRUNCATE TABLE `#gmaven_brokers`")->exec();
		$db->query("TRUNCATE TABLE `#gmaven_brokers_to_properties`")->exec();

		// Loop over each property
		foreach($list as $i => $p){

			// Fetch info
			$r = $this->get('data/entity/property/'.$p['gmv_id'].'/responsibility');

			// Loop over everything and mach up
			if(isset($r->list) and count($r->list)){
				foreach($r->list as $l){
					if(isset($l->userDomainKey) and ! empty($l->userDomainKey)){
						foreach($team as $member){
							if($l->userDomainKey == $member->_id){
								$this->brokerInset($member, $p, $l->responsibility);
							}
						}
						break;
					}
				}
			}

			// Update progress bar
			$progress->advance();
		}

		// Done
		return $t;
	}

	/**
	 * 
	 */
	public function getContacts()
	{
		// Total to return
		$t = 0;

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
		$db->query("TRUNCATE TABLE `#gmaven_contacts`")->exec();
		$db->query("TRUNCATE TABLE `#gmaven_contacts_to_properties`")->exec();

		//print count($list); die();

		// Info and progress
		$this->cli->green('Match contacts to properties');
		$progress = $this->cli->progress()->total(count($list));

		//die();

		// Buid up an array of property ids and a string to query with
		$ids = [];
		foreach($list as $property){

			// Find contacts listed on a property
			$r = $this->post('data/default/property/search', [
				'sourceFields' => [
					'contacts._id',
				],
				'query' => [
					'id' => [
						"\$eq" => $property['gmv_id']
					]
				],
				'page'  => ['number' => 1, 'size' => 1]
			]);

			// Check for results and exit if nothing
			if(count($r->list) > 0){

				// Reset array
				$arr = [];

				// Pull out all the contact ids
				foreach($r->list as $objArr){
					if(isset($objArr->contacts)){
						foreach($objArr->contacts as $obj){
							$arr[] = $obj->_id;
						}
					}
				}

				// If we have array data to work with
				if(count($arr)){

					// Call Gmaven to get total properties
					$result = $this->post('data/default/contact/search', [
						'sourceFields' => ['id', 'name', 'tel', 'cell', 'email'],
						'query' => [
							"id" => [
								"\$in" => $arr
							]
						]
					]);

					if($result->md->totalResults > 0){
						foreach($result->list as $contact){
							if($this->contactInsert($contact, $property)){
								$t++;
							}
						}
					}
				}
			}

			// Update progress bar
			$progress->advance();
		}

		// Done
		return $t;
	}

	/**
	 * Inserts a new broker and matches new or existing brokers to a property
	 *
	 * @param Object The member of the team
	 * @param Array  Property array to assign the broker to
	 * @param String Responsibility of the broker
	 *
	 * @return void
	 */
	public function brokerInset($member, $p, $r)
	{
		// Forge database connection
		$db = Db::forge($this->get_config());

		// Check  if the broker exists
		if($db->query("SELECT * FROM `#gmaven_brokers` WHERE `gmv_id` = '".$member->_id."'")->get_one('id', false) == false){

			// Inset new broker
			$q = "
			INSERT INTO `#gmaven_brokers`
			(`gmv_id`, `name`, `resp`, `tel`, `cell`, `email`, `updated_at`)
			VALUES (
			 '".$member->_id."',
			 '".$member->name."',
			 '".$r."',
			 '".(!empty($member->tel)   ? $member->tel   : '')."',
			 '".(!empty($member->cell)  ? $member->cell  : '')."',
			 '".(!empty($member->email) ? $member->email : '')."',
			 ".$this->time."
			);
			";

			try{
				$db->query($q)->exec();
			}
			catch(\Exception  $e){
				$this->cli->red($e->getMessage());
			}
		}

		// Get property id
		$pid = $p['id'];

		// Get broker id
		$bid = $db->query("SELECT `id` FROM `#gmaven_brokers` WHERE `gmv_id` = '".$member->_id."'")->get_one('id');

		// Match up
		if($bid and $pid){
			$check = $db->query("SELECT COUNT(*) AS 'T' FROM `#gmaven_brokers_to_properties` WHERE `pid` = ".$pid." AND bid = ".$bid)->get_one('T');
			if($check == 0){
				$db->query("INSERT INTO `#gmaven_brokers_to_properties` (`pid`, `bid`) VALUES (".$pid.", ".$bid.")")->exec();
			}
		}
	}

	/**
	 * Inserts a new contact and matches new or existing contacts to a property
	 *
	 * @param Object The contact object
	 * @param Array  Property array to assign the broker to
	 */
	public function contactInsert($contact, $property)
	{
		// Forge database connection
		$db = Db::forge($this->get_config());

		// Check  if the broker exists
		if($db->query("SELECT * FROM `#gmaven_contacts` WHERE `gmv_id` = '".$contact->id."'")->get_one('id', false) == false){

			// Inset new broker
			$q = "
			INSERT INTO `#gmaven_contacts`
			(`gmv_id`, `name`, `tel`, `cell`, `email`, `updated_at`)
			VALUES (
			 '".$contact->id."',
			 '".(!empty(addslashes($contact->name))  ? $contact->name  : '')."',
			 '".(!empty($contact->tel)   ? $contact->tel   : '')."',
			 '".(!empty($contact->cell)  ? $contact->cell  : '')."',
			 '".(!empty($contact->email) ? $contact->email : '')."',
			 ".$this->time."
			);
			";

			// Info
			//$this->cli->green('Inserted ' . $contact->name);

			try{
				$db->query($q)->exec();
			}
			catch(\Exception  $e){
				$this->cli->red($e->getMessage());
			}
		}

		// Get property id
		$pid = $property['id'];

		// Get contact id
		$cid = $db->query("SELECT `id` FROM `#gmaven_contacts` WHERE `gmv_id` = '".$contact->id."'")->get_one('id');

		// Match up
		if($cid and $pid){
			$check = $db->query("SELECT COUNT(*) AS 'T' FROM `#gmaven_contacts_to_properties` WHERE `pid` = ".$pid." AND cid = ".$cid)->get_one('T');
			if($check == 0){
				$db->query("INSERT INTO `#gmaven_contacts_to_properties` (`pid`, `cid`) VALUES (".$pid.", ".$cid.")")->exec();
				return true;
			}
		}

		// No match
		return false;
	}

	/**
	 * Get data via Guzzle
	 *
	 * @param String The endpoint we calling against
	 */
	private function get($endPoint)
	{
		// Set and filter post data
		$clientDataArray = [
			'base_uri' => 'https://www.gmaven.com/api/'
		];

		// Setup Guzzle
		$client = new \GuzzleHttp\Client($clientDataArray);
		$response = $client->request('get', $endPoint, [
			'headers' => [
				'gmaven.apiKey' => $this->get_config('key'),
				'Content-Type'  => 'application/json'
			]
		]);

		// Return response data
		return $this->getResponse($response);
	}

	/**
	 * Post data via Guzzle
	 *
	 * @param String The endpoint we calling against
	 * @param Array Extra data to send to Gmaven
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

		// Setup Guzzle
		$client = new \GuzzleHttp\Client($clientDataArray);
		$response = $client->request('post', $endPoint, [
			'debug'   => false,
			'headers' => [
				'gmaven.apiKey' => $this->get_config('key'),
				'Content-Type'  => 'application/json'
			]
		]);

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
	 * Log and show errors @todo
	 */
	private function handleError($e){
		return false;
	}

	/**
	 * Just usefull for testing
	 */
	public function call($endpoint, $fields = [])
	{
		$method = count($fields) ? 'POST' : 'GET';

		switch($method){
			case 'GET' :
				$r = $this->get($endpoint);
			break;
			case 'POST' :
				$r = $this->post($endpoint, $fields);
			break;
		}

		return $r;
	}
}
?>
