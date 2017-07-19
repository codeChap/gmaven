<?php

/**
 * Builds the database tables
 */

namespace CodeChap\Gmv;

class Db extends Arc\Singleton
{
	/**
	 * The database connection
	 */
	public $db = false;

	private $cli = false;

	/**
	 * Setup database connection using config values
	 */
	public function __construct($config)
	{
		// Create database connection
		$this->db = new \PDO("mysql:host=".$config['host'].";dbname=".$config['base'], $config['user'], $config['pass']);
		$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		// Call parent constructor
		parent::__construct($config);
	}

	/**
	 * Format query
	 */
	public function query($q)
	{
		$this->q = preg_replace("/#/", $this->get_config('pfx'), $q);
		return $this;
	}

	/**
	 * Inserts etc
	 */
	public function exec()
	{
		try {
			$r = $this->db->exec($this->q);
		}
		catch(\PDOException $e){
			throw new \Exception($e->getMessage());
		}
	}

	/**
	 * Queries etc
	 */
	public function get($key = false)
	{
		try {

			// Prepare and fetch all
			$p = $this->db->prepare($this->q);
			$p->execute();
			$r = $p->fetchAll();
			
			// Done
			if(count($r)){
				if($key){
					$return = [];
					foreach($r as $k => $v){
						$return[] = $v[$key];
					}
					return $return;
				}else{
					return $r;
				}
			}
			else{
				return false;
			}
		}
		catch(\PDOException $e){
			throw new \Exception($e->getMessage());
		}
	}

	/**
	 * Queries etc
	 */
	public function get_one($key, $default = 0)
	{
		try {

			// Prepare and fetch all
			$p = $this->db->prepare($this->q);
			$p->execute();
			$r = $p->fetchAll();
			
			// Done
			if(count($r)){
				return $r[0][$key];
			}
			else{
				return $default;
			}
		}
		catch(\PDOException $e){
			throw new \Exception($e->getMessage());
		}
	}

	/**
	 * Close any open database connections
	 */
	public function __destruct()
	{
		$this->db = null;
	}
}

?>