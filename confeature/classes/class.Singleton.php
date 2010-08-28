<?php

abstract class Singleton {

    protected function __construct() {
    }

    final public static function getInstance() {
        static $aoInstance = array();
		
        $calledClassName = get_called_class();

        if (! isset ($aoInstance[$calledClassName])) {
            $aoInstance[$calledClassName] = new $calledClassName();
        }

        return $aoInstance[$calledClassName];
    }

    final private function __clone() {
    }
}



if(!function_exists('get_called_class')){
	function get_called_class(){
		$bt = debug_backtrace();
		if(isset($bt[2]['function']) && $bt[2]['function'] == 'call_user_func'){
			return $bt[2]['args'][0][0];
		}else{
			$lines = file($bt[1]['file']);
			preg_match('/([a-zA-Z0-9\_]+)::'.$bt[1]['function'].'/',
				$lines[$bt[1]['line']-1],
				$matches);
			return $matches[1];
		}
	}
}
