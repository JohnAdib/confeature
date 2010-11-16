<?php
/**
 * Database connection class using PDO
 * + helpers for fast and secure queries
 */

class DB {
	/**
	 * Instance of PDO
	 * @var PDO
	 */
	private static $conn;
	
	/**
	 * Status of the DB connection : True if connected, false otherwise
	 * @var bool
	 */
	private static $connected = false;
	
	/**
	 * Driver used by PDO
	 * @var srting
	 */
	private static $driver = 'mysql';	// Driver used
	
	/**
	 * Data Source Name (without the driver name)
	 * @var string
	 */
	private static $dsn = 'host=localhost;port=3306';
	
	/**
	 * User name for identification
	 * @var string
	 */
	private static $username;
	
	/**
	 * Password for identification
	 * @var string
	 */
	private static $password;
	
	/**
	 * Queries log (if Config::DEBUG==true)
	 * @var array
	 */
	private static $queriesLog = array();
	
	/**
	 * Errors log
	 * @var array
	 */
	private static $errorsLog = array();
	
	
	/**
	 * Configures DB connection info
	 *
	 * @param array $params	Associative array with configuration variables
	 */
	public static function config($params){
		foreach(array('driver', 'dsn', 'username', 'password') as $var){
			if(isset($params[$var]))
				self::$$var = $params[$var];
		}
	}
	
	
	/**
	 * Starts the DB connection
	 * Private method to call from other static methods of the class
	 */
	private static function _init(){
		if(self::$connected)
			return;
		try{
			$conn = new PDO(
				self::$driver.':'.self::$dsn,
				isset(self::$username) ? self::$username : null,
				isset(self::$password) ? self::$password : null
			);
			self::$connected = true;
		}catch(Exception $e){
			self::_logError($e);
			throw $e;
		}
		
		// Si on utilise Mysql, on passe en UTF-8
		if(self::$driver == 'mysql')
			$conn->query('SET NAMES utf8');
		self::$conn = $conn;
	}
	
	
	/**
	 * Logs a query
	 *
	 * @param Exception $e	Exception related to the error
	 */
	private static function _logQuery($query, $duration){
		self::$queriesLog[] = array($query, $duration);
	}
	
	/**
	 * Returns the queries log
	 *
	 * @return	Array of the queries and them durations
	 */
	public static function getQueriesLog(){
		return self::$queriesLog;
	}
	
	
	/**
	 * Logs an error
	 *
	 * @param Exception $e	Exception related to the error
	 * @param string $query	SQL query involved (optional)
	 */
	private static function _logError(Exception $e, $query=null){
		self::$errorsLog[] = array($e->getCode(), $e->getMessage(), $query);
		// Ecriture du log dans un fichier
		$logfile = new Log('db_errors.log');
		$logfile->write($e->getCode().': '.$e->getMessage().(isset($query) ? ' - '.$query : ''));
	}
	
	/**
	 * Returns the errors log
	 *
	 * @return	Array of the errors
	 */
	public static function getErrorsLog(){
		return self::$errorsLog;
	}
	
	/**
	 * Check if an error occured
	 *
	 * @param PDOStatement $stmt	Concerned PDO statement
	 */
	private static function _checkError($stmt=null){
		if(isset($stmt)){
			$error_info = $stmt->errorInfo();
			if(isset($error_info[1]))
				throw new Exception($error_info[2], $error_info[1]);
		}
		$error_info = self::$conn->errorInfo();
		if(isset($error_info[1]))
			throw new Exception($error_info[2], $error_info[1]);
	}
	
	
	/**
	 * Executes a query and returns the PDOStatement
	 *
	 * @param string $query	SQL query
	 * @param array $params	Parameters (optionnal)
	 * @return PDOStatement
	 */
	public static function execute($query, $params=array()){
		self::_init();
		try{
			if(Config::DEBUG)
				$time = microtime(true);
			
			// Preparation of the query
			$stmt = self::$conn->prepare($query);
			
			// Error ?
			if($stmt===false)
				self::_checkError($stmt);
			
			// Execution of the query
			if(!$stmt->execute($params))
				self::_checkError($stmt);
			
			if(Config::DEBUG)
				self::_logQuery($query, microtime(true) - $time);
			
			return $stmt;
			
		}catch(Exception $e){
			self::_logError($e, $query);
			throw $e;
		}
	}
	
	
	/**
	 * Executes a SELECT query and returns an associative array of the results
	 *
	 * @param string $query	SQL query
	 * @param array $params	Parameters (optionnal)
	 * @return array	Results
	 */
	public static function select($query, $params=array()){
		$stmt = self::execute($query, $params);
		try{
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
			
		}catch(Exception $e){
			self::_logError($e, $query);
			throw $e;
		}
	}
	
	/**
	 * Quotes a string for a secure insertion
	 *
	 * @param string|int $str		String or int to be quoted
	 * @return string	Quoted string
	 */
	public static function quote($str){
		self::_init();
		return self::$conn->quote($str);
	}
	
	/**
	 * Returns the id of the last inserted row
	 *
	 * @return string	Id of the last inserted row, or '0' if not found
	 */
	public static function lastInsertId(){
		if(!self::$connected)
			return '0';
		try{
			return self::$conn->lastInsertId();
		}catch(Exception $e){
			self::_logError($e);
			throw $e;
		}
	}
	
	
	/**
	 * Generates the conditions based on a string or a possibly multidimensional array
	 * Format :
	 *	array(
	 *		'`field1`="value"',
	 *		'field2' => 42,
	 *		array(
	 *			'field3' => 'foo',
	 *			'field4' => 'bar',
	 *			array(
	 *				'field5' => 'foo2',
	 *				'field6' => 'foo3'
	 *			)
	 *		)
	 *	)
	 *	=> `field`="value" AND `field2`=42 AND (`field3`="foo" OR `field4`="bar" OR (`field5`="foo2" AND `field6`="foo3"))
	 *
	 * @param string|array $conditions	String or array of the conditions
	 * @param string $op				Operand to be applied
	 * @return string	Conditions in SQL format
	*/
	public static function computeConditions($conditions, $op='AND'){
		if(is_string($conditions))
			return $conditions;
		if($op != 'AND')
			$op = 'OR';
		
		$where = array();
		foreach($conditions as $key => $value){
			if(is_string($key) && !isset($value)){
				$where[] = '`'.$key.'` = NULL';
			}else if(is_string($key) && is_string($value)){
				$where[] = '`'.$key.'` = '.self::quote($value);
			}else if(is_string($key) && is_int($value)){
				$where[] = '`'.$key.'` = '.$value;
			}else if(is_array($value)){
				$where[] = '('.self::computeConditions($value, $op=='AND' ? 'OR' : 'AND').')';
			}else if(is_string($value)){
				$where[] = '('.$value.')';
			}
		}
		return implode(' '.$op.' ', $where);
	}
	
	
	/**
	 * Returns a new instance of DB_Query associated to a table
	 *
	 * @param string $table	Table to be associated
	 * @return DB_Query	New instance
	 */
	public static function createQuery($table){
		return new DB_Query($table);
	}
	
}




/**
 * Helper useful to manipulate data easily without SQL queries
 * Writes simple SQL queries
 */

class DB_Query {
	
	/**
	 * SQL table
	 * @var string
	 */
	private $table;
	
	/**
	 * Fields to be retrieved
	 * @var array
	 */
	private $fields = array();
	
	/**
	 * Where conditions
	 * @var array
	 */
	private $where = array();
	
	/**
	 * Fields of the ORDER BY instruction
	 * @var array
	 */
	private $order = array();
	
	/**
	 * Number of lines to be returned
	 * @var int
	 */
	private $limit = 0;
	
	/**
	 * Number of lines to be skipped
	 * @var int
	 */
	private $offset = 0;
	
	/**
	 * Associative array of fields-values to add / modify
	 * @var array
	 */
	private $set = array();
	
	/**
	 * Allows modification / deletion of data without condition if true
	 * @var bool
	 */
	private $force = false;
	
	
	/**
	 * Constructor - Defines the table on which we work
	 *
	 * @param string $table	SQL table
	 */
	public function __construct($table){
		$this->table = $table;
	}
	
	
	/**
	 * Set the fields to be retrieved in a SELECT query
	 *
	 * @param string|array $field,...	Fields' names
	 * @return DB_Query	This instance
	 */
	public function fields(){
		$fields = func_get_args();
		foreach($fields as $field){
			if(is_array($fields))
				$this->fields = array_merge($this->fields, $fields);
			else if(is_string($field))
				$this->fields[] = $field;
		}
		return $this;
	}
	
	/**
	 * Set the conditions to be matched in the query
	 *
	 * @param string|array $conditions	Conditions which will be computed by DB::computeConditions
	 * @return DB_Query	This instance
	 */
	public function where($conditions){
		if(is_string($conditions)){
			$this->where[] = $conditions;
		}else if(is_array($conditions)){
			foreach($conditions as $key => $value){
				if(is_string($key))
					$this->where[$key] = $value;
				else
					$this->where[] = $value;
			}
		}
		return $this;
	}
	
	/**
	 * Set the fields which will be used in the ORDER BY instruction
	 *
	 * @param string|array $field,...	Fields' names / pairs field-direction
	 * @return DB_Query	This instance
	 */
	public function order(){
		$fields = func_get_args();
		foreach($fields as $field){
			if(is_string($field))
				$this->order[] = $field;
			else if(is_array($field) && isset($field[0]) && is_string($field[0]))
				$this->order[] = '`'.$field[0].'` '.(isset($field[1]) && strtoupper($field[1])=='DESC' ? 'DESC' : 'ASC');
		}
		return $this;
	}
	
	/**
	 * Set the limit and possibly the offset of the query
	 *
	 * @param int $limit	Number of lines to be returned
	 * @param int $offset	Number of lines to be skipped
	 * @return DB_Query	This instance
	 */
	public function limit($limit, $offset=0){
		if(!is_int($limit))
			throw new Exception('$limit must be an int');
		if(!is_int($offset))
			throw new Exception('$offset must be an int');
		$this->limit = $limit;
		$this->offset = $offset;
		return $this;
	}
	
	/**
	 * Set the values of fields to be modified
	 *
	 * @param string|array $field	Name of a field or associative array of fields-values
	 * @param string|int $value		Value of the field (useless if $field is an array)
	 * @return DB_Query	This instance
	 */
	public function set($field, $value=null){
		if(is_array($field)){
			foreach($field as $field_ => $value){
				if(is_string($field_))
					$this->set[$field_] = $value;
				else if(is_int($field_))
					$this->set[] = $value;
			}
		}else if(is_string($field) && isset($value) && (is_int($value) || is_string($value))){
			$this->set[$field] = $value;
		}else{
			throw new Exception('You must define a pair ($field, $value) or define $field as an associative array');
		}
		return $this;
	}
	
	/**
	 * Allows modification / deletion of data without condition
	 *
	 * @param bool $force	Allows if true or omitted, else disallows
	 * @return DB_Query	This instance
	 */
	public function force($force=true){
		$this->force = $force;
		return $this;
	}
	
	
	/**
	 * Execute a SELECT query with parameters previously set
	 *
	 * @param int $id	If set, add a condition id=$id
	 * @return array	Results
	 */
	public function select($id=null){
		if(isset($id) && (is_int($id) || is_string($id))){
			$this->where['id'] = $id;
			$this->limit = 1;
		}
		if(count($this->fields) == 0)
			$this->fields[] = '*';
		$this->fields = array_unique($this->fields);
		
		$results = DB::select('
			SELECT '.implode(', ', $this->fields).'
			FROM '.$this->table.'
			'.(count($this->where)==0 ? '' : 'WHERE '.DB::computeConditions($this->where)).'
			'.(count($this->order)==0 ? '' : 'ORDER BY '.implode(', ', $this->order)).'
			'.($this->limit != 0 ? 'LIMIT '.$this->offset.', '.$this->limit : '').'
		');
		return $results;
	}
	
	/**
	 * Execute a SELECT COUNT(*) query with parameters previously set
	 *
	 * @return int	Numbers of rows counted
	 */
	public function count(){
		$results = DB::select('
			SELECT COUNT(*) AS nb
			FROM '.$this->table.'
			'.(count($this->where)==0 ? '' : 'WHERE '.DB::computeConditions($this->where)).'
		');
		return isset($results[0]) ? (int) $results[0]['nb'] : 0;
	}
	
	/**
	 * Execute an UPDATE query with parameters previously set
	 *
	 * @param int $id	If set, add a condition id=$id
	 * @return int	Number of affected rows
	 */
	public function update($id=null){
		if(isset($id) && (is_int($id) || is_string($id))){
			$this->where['id'] = $id;
			$this->limit = 1;
		}
		
		if(count($this->where)==0 && !$this->force)
			throw new Exception('You must use "force" method to update without condition');
		
		$set = array();
		foreach($this->set as $key => $value){
			if(is_int($key))
				$set[] = $value;
			else
				$set[] = '`'.$key.'` = '.(isset($value) ? DB::quote($value) : 'NULL');
		}
		
		return DB::execute('
			UPDATE '.$this->table.'
			SET '.implode(', ', $set).'
			'.(count($this->where)==0 ? '' : 'WHERE '.DB::computeConditions($this->where)).'
			'.($this->limit != 0 ? 'LIMIT '.$this->limit : '').'
		');
	}
	
	/**
	 * Execute a DELETE query with parameters previously set
	 *
	 * @param int $id	If set, add a condition id=$id
	 * @return int	Number of affected rows
	 */
	public function delete($id=null){
		if(isset($id) && (is_int($id) || is_string($id))){
			$this->where['id'] = $id;
			$this->limit = 1;
		}
		
		if(count($this->where)==0 && !$this->force)
			throw new Exception('You must use "force" method to delete without condition');
		
		return DB::execute('
			DELETE FROM '.$this->table.'
			'.(count($this->where)==0 ? '' : 'WHERE '.DB::computeConditions($this->where)).'
			'.($this->limit != 0 ? 'LIMIT '.$this->limit : '').'
		');
	}
	
	/**
	 * Execute an INSERT or REPLACE query with fields-values previously set
	 *
	 * @param bool $replace	If true, use a REPLACE instruction
	 * @return int	Id of the new row
	 */
	public function insert($replace=false){
		$fields = array_keys($this->set);
		$values = array();
		foreach($this->set as $value)
			$values[] = isset($value) ? DB::quote($value) : 'NULL';
		
		DB::execute('
			'.($replace ? 'REPLACE' : 'INSERT').' INTO '.$this->table.'
			(`'.implode('`, `', $fields).'`)
			VALUES
			('.implode(', ', $values).')
		');
		return DB::lastInsertId();
	}
	
	/**
	 * Execute a REPLACE query with fields-values previously set
	 *
	 * @return int	Number of affected rows
	 */
	public function replace(){
		return $this->insert(true);
	}
	
}