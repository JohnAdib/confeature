<?php
define('START_TIME', microtime(true));

/* Cron mode */
if(defined('CRON_MODE') && CRON_MODE){
	set_time_limit(0);
	$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	$_SERVER['HTTP_USER_AGENT'] = '';
	$_SERVER['REQUEST_URI'] = '';
	$_SERVER['HTTP_ACCEPT'] = '';
}


/**
 * Magic function of class autoloading
 *
 * @param string $class	Class name
 * @return bool	True on success, false on failure
 */
function __autoload($class){
	if(class_exists($class))
		return true;
	
	// Controller
	if(substr($class, -11)=='_Controller' && file_exists($file = APP_DIR.'controllers/'.substr($class, 0, -11).'.php'));
	
	// Model
	else if(substr($class, -6)=='_Model' && file_exists($file = APP_DIR.'models/'.substr($class, 0, -6).'.php'));
	
	// Configuration classes
	else if(in_array($class, array('Config', 'Routes')) && file_exists($file = APP_DIR.'config/'.$class.'.php'));
	
	// APP classes
	else if(file_exists($file = APP_DIR.'classes/class.'.$class.'.php'));
	
	// Confeature classes
	else if(file_exists($file = CF_DIR.'classes/class.'.$class.'.php'));
		
	else
		return false;
	
	require_once $file;
	return class_exists($class);
}


/**
 * Error handler, called when an error occurs
 *
 * @param int $errno	Error code
 * @param int $errstr	Error message
 * @param int $errfile	File in which the error occured
 * @param int $errline	Line of the file where the error occured
 */
function error_handler($errno, $errstr, $errfile, $errline){
	$log = new Log('php_errors.log');
	$msg = '';
	$exception = true;
	
	// cf. http://www.php.net/manual/en/function.set-error-handler.php
	switch($errno){
		case E_ERROR:
		case E_USER_ERROR:
			$msg = 'Fatal error: '.$errstr.' in '.$errfile.':'.$errline;
			$log->write($msg);
			if(Config::DEBUG)
				return $msg;
			else
				return 'An error occured, please try again later.';
			break;
		
		case E_WARNING:
		case E_USER_WARNING:
			$msg = 'Warning: '.$errstr.' in '.$errfile.':'.$errline;
			break;
		
		case E_NOTICE:
		case E_USER_NOTICE:
			$msg = 'Notice: '.$errstr.' in '.$errfile.':'.$errline;
			$exception = false;
			break;

		default:
			$msg = 'Unknown error ['.$errno.']: '.$errstr.' in '.$errfile.':'.$errline;
			break;
    }
    $log->write($msg);
    if($exception)
		throw new Exception($msg, $errno);
}
ini_set('display_errors', true);
error_reporting(E_ALL);
set_error_handler('error_handler');


// Output buffering
function ob_callback($buffer){
	// Interception of a fatal error
	if(preg_match('#Fatal error: (.*) in (.*) on line ([0-9]+)\s*$#s', $buffer, $match))
		$buffer = error_handler(E_ERROR, $match[1], $match[2], (int) $match[3]);
	
	return $buffer;
}
ob_start('ob_callback');

// Time-zone from the configuration
date_default_timezone_set(Config::TIMEZONE);

// Language of the app
if(!defined('LANG'))
	define('LANG', Config::$LOCALES[0]);
L10N::load(LANG);


// Visitor IP
if(isset($_SERVER['HTTP_CLIENT_IP']) && Validation::isIP($_SERVER['HTTP_CLIENT_IP']) && !Validation::isLocalIP($_SERVER['HTTP_CLIENT_IP']))
	define('IP', $_SERVER['HTTP_CLIENT_IP']);
else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && Validation::isIP($_SERVER['HTTP_X_FORWARDED_FOR']) && !Validation::isLocalIP($_SERVER['HTTP_X_FORWARDED_FOR']))
	define('IP', $_SERVER['HTTP_X_FORWARDED_FOR']);
else if(Validation::isIP($_SERVER['REMOTE_ADDR']) && !Validation::isLocalIP($_SERVER['REMOTE_ADDR']))
	define('IP', $_SERVER['REMOTE_ADDR']);
else
	define('IP', '');


// Security : The session is wiped if the user-agent change
if(Session::exists('HTTP_USER_AGENT')){
	if(Session::read('HTTP_USER_AGENT') != $_SERVER['HTTP_USER_AGENT']){
		Session::regenerate_id();
		Session::wipe();
		Session::write('HTTP_USER_AGENT', $_SERVER['HTTP_USER_AGENT']);
	}
}else{
	Session::write('HTTP_USER_AGENT', $_SERVER['HTTP_USER_AGENT']);
}


// Security : The page is reloaded without session id in the URL if the session id is present in the URL
if(strpos($_SERVER['REQUEST_URI'], Session::name()) && count($_POST) == 0){
	Session::close();
	setcookie(Session::name(), Session::id(), null, '/', '.'.$domaine);
	$page_address = preg_replace('#(?<=&|\?)'.Session::name().'=[^&]+(?:&|$)#', '', $_SERVER['REQUEST_URI']);
	$page_address = rtrim($page_address, '?&');
	header('Location: http://'.$_SERVER['HTTP_HOST'].$page_address);
	exit;
}


// Security : The $_POST variables are wiped if the referer domain is different from the current domain
if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '' && !preg_match('#^https?://'.preg_quote($_SERVER['SERVER_NAME']).'#', $_SERVER['HTTP_REFERER'])){
	// On vide $_POST
	$_POST = array();
}


// Removing special characters from $_POST variables (they may be a problem with DB or AJAX)
foreach($_POST as $key => $value){
	if(!is_array($value))
		$value = preg_replace('#[\x01-\x08\x0B\x0C\x0E-\x1F]#', '', $value);
}


// Detection of mobile device
$is_mobile = !empty($_SERVER['X_WAP_PROFILE'])
	|| preg_match('#(text/vnd\.wap\.wml|application/vnd.wap.xhtml)#', $_SERVER['HTTP_ACCEPT'])
	|| preg_match('#(?<![a-z])('.
		'iphone|ipod|symbian|nokia|wap|vodafone|pocket|'.
		'ipad|sonyericsson|motorola|android|opera mini|'.
		'blackberry|palm os|palm|hiptop|avantgo|plucker|'.
		'xiino|blazer|elaine|iris|3g_t|windows ce|opera mobi|'.
		'windows ce; smartphone|windows ce; iemobile|'.
		'mini 9\.5|vx1000|lge|m800|e860|u940|ux840|compal|'.
		'wireless|mobi|ahong|lg380|lgku|lgu900|lg210|'.
		'lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|'.
		'vx400|mk99|d615|d763|el370|sl900|mp500|samu3|'.
		'samu4|vx10|xda|samu5|samu6|samu7|samu9|a615|'.
		'b832|m881|s920|n210|s700|c-810|h797|mob-x|treo|'.
		'sk16d|848b|mowser|s580|r800|471x|v120|rim8|'.
		'c500foma|160x|x160|480x|x640|t503|w839|i250|'.
		'sprint|w398samr810|m5252|c7100|mt126|x225|s5330|'.
		's820|htil-g1|fly v71|s302|x113|novarra|k610i|'.
		'three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|'.
		'mtk|c5588|s710|t880|c5005|i;458x|p404i|s210|'.
		'c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|'.
		'a1000|mms|myx|a700|gu1100|bc831|e300|ems100|'.
		'me701|me702m-three|sd588|s800|8325rc|ac831|mw200|'.
		'brew|d88|htc|355x|m50|km100|d736|kindle|mobile|'.
		'p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|'.
		'phone|lg|samsung|240x|x320|vx10|sony cmd|psp|'.
		'up\.browser|up\.link|mmp|symbian|smartphone|midp'.
		')(?![a-z])#i', $_SERVER['HTTP_USER_AGENT']);

// Mobile mode for the website
$mobile_mode = false;
if(isset($_GET['mobile'])) {
	$mobile_mode = $_GET['mobile'] == 1 ? 1 : 0;
	Cookie::write('mobile', (string) $mobile_mode, 60*24*3600);
}else if(isset($_COOKIE['mobile'])){
	$mobile_mode = $_COOKIE['mobile'] == 1 ? 1 : 0;
}else{
	$mobile_mode = $is_mobile;
}

define('MOBILE_BROWSER', $is_mobile);
define('MOBILE_MODE', $mobile_mode);


// Configuration of the DB
if(isset(Config::$DB))
	DB::config(Config::$DB);

// Configuration of the cache
if(isset(Config::$CACHE))
	Cache::config(Config::$CACHE);
