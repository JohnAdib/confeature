<?php
/**
 * Cache system
 */

class Cache {
	/**
	 * Instance of Memcache
	 * @var Memcache
	 */
	private static $conn;
	
	/**
	 * Status of the cache server connection
	 * @var bool
	 */
	private static $connected = false;
	
	/**
	 * Cache system used
	 * e.g. : apc, memcache
	 * @var string
	 */
	private static $driver = 'apc';
	
	/**
	 * Host of the cache server (for Memcache)
	 * @var string
	 */
	private static $host = 'localhost';
	
	/**
	 * Port of the cache server (for Memcache)
	 * @var string
	 */
	private static $port = 11211;
	
	/**
	 * Prefix to be added at the beginning of each variable name
	 * @var string
	 */
	private static $prefix = '';
	
	
	/**
	 * Configures the cache (driver...)
	 *
	 * @param array $params	Associative array with configuration variables
	 */
	public static function config($params){
		foreach(array('driver', 'host', 'port', 'prefix') as $var){
			if(isset($params[$var]))
				self::$$var = $params[$var];
		}
		
		if(self::$driver=='apc')
			self::$connected = true;
	}
	
	
	/**
	 * Starts the cache server connection
	 * Private method to call from other static methods of the class
	 */
	private static function _init(){
		if(self::$connected)
			return;
		if(self::$driver=='memcache'){
			$conn = new Memcache();
			$conn->connect(self::$host, self::$port);
			$conn->setCompressThreshold(1000, 0.2);
			self::$conn = $conn;
		}
	}
	
	/**
	 * Reads a cache variable and returns its value
	 *
	 * @param string $name	Name of the cache variable
	 * @return mixed	Value of the cache variable
	 */
	public static function read($name){
		self::_init();
		
		if(self::$driver=='memcache'){
			return self::$conn->get(self::$prefix.$name);
		
		}else if(self::$driver=='apc'){
			return apc_fetch(self::$prefix.$name);
		}
	}
	
	/**
	 * Creates or modify a cache variable
	 *
	 * @param string $name		Name of the cache variable
	 * @param string $value		Value of the cache variable. Destroy the cache variable if omitted or null
	 * @param int $ttl			Life time of the variable in seconds
	 * @return bool	True on success, false on failure
	 */
	public static function write($name, $value, $ttl=7200){
		if(!isset($value))
			self::delete($name);
		self::_init();
		
		if(self::$driver=='memcache'){
			return self::$conn->set(self::$prefix.$name, $value, false, $ttl);
		
		}else if(self::$driver=='apc'){
			return apc_store(self::$prefix.$name, $value, $ttl);
		}
	}
	
	/**
	 * Deletes a cache variable
	 *
	 * @param string $name	Name of the cache variable
	 * @return bool	True on success, false on failure
	 */
	public static function delete($name){
		self::_init();
		
		if(self::$driver=='memcache'){
			return self::$conn->delete(self::$prefix.$name);
		
		}else if(self::$driver=='apc'){
			return apc_delete(self::$prefix.$name);
		}
	}
	
}
