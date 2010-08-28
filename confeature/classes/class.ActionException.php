<?php
/**
 * Exception returned in case of error by a controller's action, giving a controller's name and an action's name to be applied
 */

class ActionException extends Exception {
	
	/**
	 * Name of the controller which will be called because of the Exception
	 * @var string
	 */
	private $controller;
	
	/**
	 * Name of the action which will be called because of the Exception
	 * @var string
	 */
	private $action;
	
	/**
	 * Parameters passed to the action which will be called because of the Exception
	 * @var array
	 */
	private $params = array();
	
	/**
	 * Constructor
	 *
	 * @param string $controller	Name of the controller which will be called because of the Exception
	 * @param string $action		Name of the action which will be called because of the Exception
	 * @param string $params		Parameters passed to the action which will be called because of the Exception
	 */
	public function __construct($controller=null, $action=null, array $params=null){
		if(isset($controller))
			$this->controller = $controller;
		if(isset($action))
			$this->action = $action;
		if(isset($params))
			$this->params = $params;
	}
	
	// Getters
	
	public function getController(){
		return $this->controller;
	}
	
	public function getAction(){
		return $this->action;
	}
	
	public function getParams(){
		return $this->params;
	}
	
	public function getParam($key){
		return isset($this->params[$key]) ? $this->params[$key] : false;
	}
	
	
	// Setters
	
	public function setController($controller){
		$this->controller = $controller;
	}
	
	public function setAction($action){
		$this->action = $action;
	}
	
	public function setParams(array $params){
		$this->params = $params;
	}
	
	public function setParam($key, $value){
		$this->params[$key] = $value;
	}
	
}
