<?php
/**
 * Model
 * The Model class must be extented to the specific models.
**/

abstract class Model {
	
	/**
	 * Name of the entry (for example : book)
	 * @var string
	 */
	private $entry;
	
	/**
	 * Name of the table (for example : books)
	 * @var string
	 */
	private $table;
	
	/**
	 * Constructor - Parse the class' name and extract the name of the corresponding table
	 */
	public function __construct($name=null){
		// Name of the specific model
		$name = substr(get_class($this), 0, -6);
		if($name != ''){
			$this->name = $name;
			
			// Name of the specific model, lower case, with underscores
			$this->entry = strtolower(preg_replace('#(?<!^)[A-Z]#', '_$0', $name));
			
			// Name of the corresponding table, plural
			$table = $this->entry;
			if(substr($table, -1)=='y' && substr($table, -2)!='ey')
				$table = substr($table, 0, -1).'ies';
			else if(substr($table, -1)=='x')
				$table .= 'es';
			else if(substr($table, -1)!='s')
				$table .= 's';
			$this->table = $table;
		}
	}
	
	/**
	 * Creates a new instance of DB_Query with the table name corresponding to the model name
	 *
	 * @return DB_Query	New instance
	 */
	protected function createQuery(){
		return DB::createQuery($this->table);
	}
	
}
