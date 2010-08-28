<?php
/**
 * Routes container to be inherited by the Config class
 * Has helpers to manipulates URLs and pages parameters
 */

abstract class RoutesAbstract {
	
	/**
	 * Returns the page address by route name and parameters
	 * 
	 * @param string $route	Name of the route
	 * @param array $attrs	Optionnals parameters (associative array)
	 * @return string	Page address
	 */
	public static function getPage($route, $attrs=array()){
		
		if(isset(Routes::$routes[$route])){
			if(isset(Routes::$routes[$route]['extend'])){
				foreach(Routes::$routes[$route]['extend'] as $vars => $route_){
					$vars = explode('&', $vars);
					foreach($vars as $var){
						if(!isset($attrs[$var]))
							continue 2;
					}
					return self::getPage($route_, $attrs);
				}
			}
			
			$address = Routes::$routes[$route]['url'];
			foreach($attrs as $key => $value)
				$address = str_replace('{'.$key.'}', $value, $address);
			return $address;
		}
		return '';
	}
	
	// Retourne les variables d'une adresse pas l'adresse de la page
	/**
	 * Returns the variables corresponding to a page address
	 * 
	 * @param string $address	Page address
	 * @return array	Variables as an associative array
	 */
	public static function getVars($address){
		
		foreach(Routes::$routes as $route){
			if(preg_match('#'.$route['regexp'].'#', $address)){
				$address = preg_replace('#'.$route['regexp'].'#', $route['vars'], $address);
				break;
			}
		}
		
		$address = str_replace('?', '&', $address);
		parse_str($address, $vars);
		return $vars;
	}
	
	
	/**
	 * Extracts vars from the URL, and calls the relevant controllers and actions
	 */
	public static function dispatch(){
		$params = self::getVars(preg_replace('#^'.preg_quote(Config::URL_ROOT).'#', '', urldecode($_SERVER['REQUEST_URI'])));
		
		if(!__autoload('Layout_Controller'))
			throw new Exception('"Layout_Controller" class not found!');
		
		// Loading the main Controller
		$controller = new Layout_Controller();
		
		$controller_name = isset($params['controller']) ? $params['controller'] : '';
		$controller_action = isset($params['action']) ? $params['action'] : '';
		
		if($controller_name=='' || !__autoload($controller_name.'_Controller')){
			$controller_name = 'Page';
			$controller_action = 'error404';
		}
		
		try {
			$controller_class = $controller_name.'_Controller';
			$controller->specificController = new $controller_class();
			$controller->specificController->{$controller_action}($params);
		
		}catch(ActionException $e){
			$controller_name = $e->getController();
			$controller_action = $e->getAction();
			$controller_class = $controller_name.'_Controller';
			$controller->specificController = new $controller_class();
			$controller->specificController->{$controller_action}($e->getParams());
		}catch(Exception $e){
			$controller_name = 'Page';
			$controller_action = 'error';
			$controller->specificController = new Page_Controller();
			$controller->specificController->error($e);
		}
		
		if(isset($params['mode']) && method_exists($controller, $params['mode']))
			$controller->{$params['mode']}();
		else
			$controller->index();
		
		// Rendering the view
		$controller->render();
		
	}
	
}
