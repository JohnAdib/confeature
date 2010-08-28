<?php
// General configuration

final class Config extends ConfigAbstract {
	
	// Absolute URL of the website
	const URL_ABSOLUTE	= 'http://example/';
	// Absolute path of the website on the domain
	const URL_ROOT		= '/';
	// Absolute path for static files
	const URL_STATIC	= '/static/';
	
	// Timezone
	const TIMEZONE	= 'Europe/Paris';

	// DB connection
	public static $DB	= array(
		'driver'	=> 'mysql',
		'dsn'		=> 'host=localhost;dbname=example',
		'username'	=> 'example',
		'password'	=> ''
	);
	
	// Md5 salt for more security
	const MD5_SALT		= '5e89f7s4ds32';

	// Directories
	// relative to "app" dir
	const DIR_APP_STATIC	= 'static/';		// Fichiers statics
	// relative to "data" dir
	const DIR_DATA_LOGS		= 'logs/';		// Logs
	const DIR_DATA_TMP		= 'tmp/';		// Temporary files
	
	// Name of the session
	const SESS_ID		= 'PHPSESSID';

	// Contact name and mail
	const CONTACT_NAME	= 'Example';
	const CONTACT_MAIL	= 'contact@example.com';
	
	// SMTP server
	const SMTP_HOST		= 'smtp.example.com';
	
	
	// Languages
	public static $LOCALES = array('fr_FR');
	

	// Debug mode
	const DEBUG			= true;
}
