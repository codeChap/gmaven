<?php

/**
 * Builds the database tables
 */

namespace CodeChap\Gmv;

class Build extends Arc\Singleton
{
	/**
	 * Insert
	 */
	public function tables()
	{
		try{

			// Connect
			$db = new \PDO("mysql:host=".$this->get_config('host').";dbname=".$this->get_config('base'), $this->get_config('user'), $this->get_config('pass'));
			$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

			// SQL
			$query = $db->query("SHOW TABLES");
			$r = $query->fetchAll(\PDO::FETCH_COLUMN);

			// Prefix
			$pfx = $this->get_config('pfx');

			// Build category table
			$table = $pfx."gmaven_categories";
			if( ! in_array($table, $r) ){
				$q = "
					CREATE TABLE `".$table."`(
					id           INT(11) AUTO_INCREMENT PRIMARY KEY,
					category     VARCHAR(90) NOT NULL,
					updated_at   INT(11) NOT NULL)
				";
				$db->exec($q);
			}

			// Build provinces table
			$table = $pfx."gmaven_provinces";
			if( ! in_array($table, $r) ){
				$q = "
					CREATE TABLE `".$table."`(
					id           INT(11) AUTO_INCREMENT PRIMARY KEY,
					province     VARCHAR(90) NOT NULL,
					updated_at   INT(11) NOT NULL)
				";
				$db->exec($q);
			}

			// Build suburbs table
			$table = $pfx."gmaven_suburbs";
			if( ! in_array($table, $r) ){
				$q = "
					CREATE TABLE `".$table."`(
					id             INT(11) AUTO_INCREMENT PRIMARY KEY,
					province_id    INT(11) NOT NULL,
					suburb         VARCHAR(90) NOT NULL,
					updated_at     INT(11) NOT NULL)
				";
				$db->exec($q);
			}

			// Build cities table
			$table = $pfx."gmaven_cities";
			if( ! in_array($table, $r) ){
				$q = "
					CREATE TABLE `".$table."`(
					id           INT(11) AUTO_INCREMENT PRIMARY KEY,
					city         VARCHAR(90) NOT NULL,
					updated_at   INT(11) NOT NULL)
				";
				$db->exec($q);
			}

			// Build properties table
			$table = $pfx."gmaven_properties";
			if( ! in_array($table, $r) ){
				$q = "
					CREATE TABLE `".$table."`(
					 `id`                   INT(11) AUTO_INCREMENT PRIMARY KEY,
					 `did`                  INT(11) NOT NULL COMMENT 'Details ID',
					 `lon`                  DECIMAL(9,6) DEFAULT NULL,
					 `lat`                  DECIMAL(9,6) DEFAULT NULL,
					 `gla`                  INT(9) DEFAULT 0,
					 `currentVacantArea`    INT(9) DEFAULT 0,
					 `weightedAskingRental` FLOAT(9,9) DEFAULT 0.000000,
					 `for_sale`             TINYINT(1) DEFAULT 0,
					 `category_id`          INT(11) DEFAULT 0,
					 `province_id`          INT(11) DEFAULT 0,
					 `city_id`              INT(11) DEFAULT 0,
					 `suburb_id`            INT(11) DEFAULT 0,
					 `updated_at`           INT(11) NOT NULL,
					 `gmv_updated`          INT(11)
					)
				";
				$db->exec($q);
			}

			// Build properties table
			$table = $pfx."gmaven_property_details";
			if( ! in_array($table, $r) ){
				$q = "
					CREATE TABLE `".$table."`(
					 `id`   INT( 11 ) AUTO_INCREMENT PRIMARY KEY, 
					 `gmv_id`               VARCHAR(90),
					 `name`                 VARCHAR(90),
					 `customReferenceId`    VARCHAR(40),
					 `displayAddress`       BLOB,
					 `marketingBlurb`       BLOB
					)
				";
				$db->exec($q);
			}

			// Build properties table
			$table = $pfx."gmaven_units";
			if( ! in_array($table, $r) ){
				$q = "
					CREATE TABLE `".$table."`(
					 `id`                 INT(11) AUTO_INCREMENT PRIMARY KEY,
					 `pid`                VARCHAR(90) NOT NULL,
					 `gmv_id`             VARCHAR(90) NOT NULL,
					 `propertyId`         VARCHAR(90),
					 `unitId`             VARCHAR(90),
					 `customReferenceId`  VARCHAR(40),
					 `category_id`        INT(11) DEFAULT 0,
					 `gla`                INT(9) DEFAULT 0,
					 `gmr`                INT(9) DEFAULT 0,
					 `netAskingRental`    INT(9) DEFAULT 0,
					 `availableType`      VARCHAR(90),
					 `availableFrom`      INT(11),
					 `marketingHeading`   VARCHAR(550),
					 `description`        BLOB,
					 `updated_at`         INT(11) NOT NULL,
					 `gmv_updated`        INT(11)
					)
				";
				$db->exec($q);
			}

			// Build properties table
			$table = $pfx."gmaven_building_images";
			if( ! in_array($table, $r) ){
				$q = "
					CREATE TABLE `".$table."`(
					 `id`                 INT(11) AUTO_INCREMENT PRIMARY KEY,
					 `entityDomainKey`    VARCHAR(90) NOT NULL,
					 `contentDomainKey`   VARCHAR(90) NOT NULL,
					 `rating`             INT(2) DEFAULT 0,
					 `updated_at`         INT(11) NOT NULL,
					 `gmv_updated`        INT(11),
					 `removed`            INT(1) DEFAULT 0
					)
				";
				$db->exec($q);
			}

			// Build properties table
			$table = $pfx."gmaven_unit_images";
			if( ! in_array($table, $r) ){
				$q = "
					CREATE TABLE `".$table."`(
					 `id`                 INT(11) AUTO_INCREMENT PRIMARY KEY,
					 `entityDomainKey`    VARCHAR(90) NOT NULL,
					 `contentDomainKey`   VARCHAR(90) NOT NULL,
					 `rating`             INT(2) DEFAULT 0,
					 `updated_at`         INT(11) NOT NULL,
					 `gmv_updated`        INT(11),
					 `removed`            INT(1) DEFAULT 0
					)
				";
				$db->exec($q);
			}

			// Build brokers table
			$table = $pfx."gmaven_brokers";
			if( ! in_array($table, $r) ){
				$q = "
					CREATE TABLE `".$table."`(
					 `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
					 `gmv_id`     VARCHAR(90) NOT NULL,
					 `name`       VARCHAR(90),
					 `resp`       VARCHAR(90),
					 `tel`        VARCHAR(90),
					 `cell`       VARCHAR(90),
					 `email`      VARCHAR(90),
					 `updated_at` INT(11) NOT NULL
					)
				";
				$db->exec($q);
			}

			// Build brokers to properties table
			$table = $pfx."gmaven_brokers_to_properties";
			if( ! in_array($table, $r) ){
				$q = "
					CREATE TABLE `".$table."`(
					 `pid` INT(11) NOT NULL,
					 `bid` INT(11) NOT NULL,
					 UNIQUE KEY `pid_bid`(`pid`, `bid`)
					);
				";
				$db->exec($q);
			}

			// Build contacts table
			$table = $pfx."gmaven_contacts";
			if( ! in_array($table, $r) ){
				$q = "
					CREATE TABLE `".$table."`(
					 `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
					 `gmv_id`     VARCHAR(90) NOT NULL,
					 `name`       VARCHAR(90),
					 `tel`        VARCHAR(90),
					 `cell`       VARCHAR(90),
					 `email`      VARCHAR(90),
					 `updated_at` INT(11) NOT NULL
					)
				";
				$db->exec($q);
			}

			// Build contacts to properties table
			$table = $pfx."gmaven_contacts_to_properties";
			if( ! in_array($table, $r) ){
				$q = "
					CREATE TABLE `".$table."`(
					 `pid` INT(11) NOT NULL,
					 `cid` INT(11) NOT NULL,
					 UNIQUE KEY `pid_bid`(`pid`, `cid`)
					);
				";
				$db->exec($q);
			}

			// Close
		$db = null;
		}
		catch(\PDOException $e){
			echo $e->getMessage();
		}
	}
}

?>