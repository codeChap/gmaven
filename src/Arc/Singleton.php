<?php

/**
 * Part of the Gmaven Package
 *
 * @package    Gmaven
 * @version    1.1
 * @author     CodeChap
 * @license    MIT License
 * @copyright  2017 Octane
 */

namespace CodeChap\Arc;

abstract class Singleton
{
	/**
   * @var object $_instance Holds the instance.
   */
  private static $instances = [];

  /**
   * @var array $config Default config array.
   */
  protected $config = [];

  /**
   * Gets a new instance of this class based on the calling class
   *
   * @param   mixed     $config  Optional config override.
   * @return  Instance
   */
  public static function forge($config = [], $name = false)
  {
    $name = $name ? : get_called_class(); // late-static-bound class name
    
    if ( ! isset(self::$instances[$name])) {
      self::$instances[$name] = new static($config);
    }
    
    return self::$instances[$name];
  }

	/**
	 * Sets up the class object. If a config is given, it will merge with the config file.
	 *
	 * @param   mixed  $config  Optional config override.
	 * @return  void
	 */
	protected function __construct($config = []) {

    // Check for string
    if(is_string($config)){
      $config = \Config::load($config);
    }

		// Order of this addition is important, do not change this.
		$this->config = $config + $this->config;
	}

  /**
   * Sets a config value on the object.
   *
   * @param   string
   * @param   mixed
   * @return  object  This, to allow for chaining.
   */
  public function set_config($config, $value = null)
  {
    $config = is_array($config) ? $config : array($config => $value);
    foreach ($config as $key => $value){
      if (strpos($key, '.') === false){
      	$this->config[$key] = $value;
      }
      else{
      	\Arr::set($this->config, $key, $value);
      }
    }

    return $this;
  }

  /**
   * Get a single or multiple config values by key.
   *
   * @param   string|array  A single key or multiple in an array, empty to fetch all.
   * @param   mixed         Default output when config wasn't set.
   * @return  mixed|array   A single config value or multiple in an array when $key input was an array.
   */
  public function get_config($key = null, $default = null)
  {
    if ($key === null){
      return $this->config;
    }

    if (is_array($key)){
      $output = array();
      foreach ($key as $k){
        $output[$k] = $this->get_config($k, $default);
      }
      return $output;
    }

    if (strpos($key, '.') === false){
      return array_key_exists($key, $this->config) ? $this->config[$key] : $default;
    }
    else{
      return \Arr::get($this->config, $key, $default);
    }
  }

  /**
   * Set Helper
   *
   * @param   String  The class param to change
   * @param   String  The value of the param
   * @return  Object  This to allow for chaining
   */
  public function set($key, $value)
  {
    // Find the key if set
    if(isset($this->$key)){
      $this->key = $value;
    }

    // Else make it part of the data array
    else{
      $this->data[$key] = $value;
    }

    // Done
    return $this;
  }

  /**
   * Get Helper
   *
   * @param   String  The class param to get
   * @return  String|Array
   */
  public function get($key)
  {
    // Find the key if set
    if(isset($this->$key)){
      return $this->$key;
    }

    // Else make it part of the data array
    else if(isset($this->data[$key])){
      return $this->data[$key];
    }

    // Not set
    return false;
  }

	// Prevent these functions
  protected function __clone() {}
  public function __wakeup(){throw new Exception("Cannot unserialize singleton");}
}