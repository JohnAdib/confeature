<?php
/**
 * Configuration of the routes
 */

final class Routes extends RoutesAbstract {
	
	/**
	 * List of the routes
	 *
	 *	{
	 *		"module" : {	// Name of the route. e.g. : 'user_view'
	 *			regexp : regular expression (PCRE) matching with the url. e.g. : '^user/([a-z0-9]+)/(?=\?|$)'
	 *			vars : variables corresponding to the url, with values of the previous regexp. e.g. : 'controller=User&action=view&username=$1'
	 *			url : URL of the page depending on various parameters ({id}, {title}...). e.g. : 'users/{username}/'
	 *			extend (optionnal) : {
	 *				"vars1" : "module_extended1"
	 *				"vars2" : "module_extended2"
	 *				...
	 *				// vars1 = names of additional variables (seperated by &). e.g. : 'page'
	 *				// module_extended1 = route name to use when these variables are defined : 'user_view_page'
	 *			}
	 *		},
	 *	...}
	 *
	 * @static array
	 */
	protected static $routes =	array(
		// Home
		'home'	=> array(
			'regexp'	=> '^(?=\?|$)',
			'vars'		=> 'controller=Page&action=home',
			'url'		=> ''
		),
		
	);

}
