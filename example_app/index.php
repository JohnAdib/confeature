<?php
define('APP_DIR', realpath('./').'/');
define('CF_DIR', realpath('../confeature/').'/');
define('DATA_DIR', realpath('../example_data/').'/');

try{
	
	// Loading Confeature
	require_once CF_DIR.'init.php';
	
	// Loading the controllers and actions from url info
	Routes::dispatch();
	
}catch(Exception $e){
	if(Config::DEBUG)
		echo $e->getMessage();
}
