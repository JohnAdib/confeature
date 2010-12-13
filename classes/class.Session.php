<?php
/**
 * Session management
 */

class Session {
	
	/**
	 * Reads a session variable and returns its value
	 *
	 * @param string $name	Name of the session variable
	 * @return mixed	Value of the session variable
	 */
	public static function read($name){
		self::_init();
		if(isset($_SESSION[$name]))
			return $_SESSION[$name];
		else
			return null;
	}
	
	/**
	 * Checks if a session variable exists
	 *
	 * @param string $name	Name of the session variable
	 * @return bool	True the session variable exists; False otherwise
	 */
	public static function exists($name){
		self::_init();
		return isset($_SESSION[$name]);
	}
	
	/**
	 * Creates or modify a session variable
	 *
	 * @param string $name		Name of the session variable
	 * @param string $value		Value of the session variable. Destroy the session variable if omitted or null
	 */
	public static function write($name, $value=null){
		if(!isset($value))
			self::delete($name);
		self::_init();
		$_SESSION[$name] = $value;
	}
	
	/**
	 * Deletes a session variable
	 *
	 * @param string $name	Name of the session variable
	 */
	public static function delete($name){
		self::_init();
		if(isset($_SESSION[$name]))
			unset($_SESSION[$name]);
	}
	
	/**
	 * Frees all session variables currently registered
	 */
	public static function wipe(){
		self::_init();
		session_unset();
	}
	
	/**
	 * Destroys all data registered to a session and the session itself
	 */
	public static function destroy(){
		self::_init();
		session_destroy();
		unset($_SESSION);
	}
	
	/**
	 * Updates the current session id with a newly generated one
	 *
	 * @param string $delete_old	If true, deletes the old session
	 */
	public static function regenerate_id($delete_old=false){
		self::_init();
		session_regenerate_id($delete_old);
	}
	
	/**
	 * Get the current session name
	 */
	public static function name(){
		return session_name();
	}
	
	/**
	 * Get and/or set the current session id
	 * 
	 * @param string $new_id	New session id
	 * @return string	Session id for the current session
	 */
	public static function id($new_id=null){
		return session_id($new_id);
	}
	
	/**
	 * Write session data and end session
	 */
	public static function close(){
		if(isset($_SESSION))
			session_write_close();
	}
	
	
	/**
	 * Recovers / creates a session
	 */
	private static function _init(){
		if(isset($_SESSION))
			return;
		session_name(Config::SESS_ID);
		session_set_cookie_params(null, '/');
		session_start();
	}
	
}
