<?php
/**
 * Controller
 * The Controller class must be extented to the specific controllers.
 */

abstract class Controller {
	
	/**
	 * Name of the controller, extracted from the class name
	 * @var string
	 */
	public $name;
	
	/**
	 * View to be used for rendering, defined by an action
	 * @var string
	 */
	private $view = false;
	
	/**
	 * Vars to be passed to the view
	 * @var array
	 */
	private $vars = array();
	
	/**
	 * Breadcrumbs
	 * @var array
	 */
	public $breadcrumbs = array();
	
	/**
	 * Title to be added to the window title
	 * @var string
	 */
	public $title = '';
	
	/**
	 * List of the JS files to be included in the page
	 * @var array
	 */
	public $jsFiles = array();
	
	/**
	 * JS script to be executed at the end of the page
	 * @var string
	 */
	public $jsCode = '';
	
	/**
	 * List of the CSS files to be included in the page
	 * @var array
	 */
	public $cssFiles = array();
	
	/**
	 * Instance of the model corresponding to the specific controller
	 * @var *_Model
	 */
	protected $model;
	
	/**
	 * Instance of the specific controller called by the main controller
	 * @var *_Controller
	 */
	public $specificController;
	
	
	/**
	 * Constructor - Extracts its name and instanciates the corresponding model
	 */
	public function __construct(){
		// Détection du nom du contrôleur
		preg_match('#^(.*)_Controller$#', get_class($this), $match);
		$this->name = $match[1];
		
		// Chargement du modèle s'il existe
		$model_name = $this->name.'_Model';
		if(__autoload($model_name)){
			$this->model = new $model_name();
		}
	}
	
	
	/**
	 * Loads the view
	 */
	public function render(){
		if(!$this->view)
			throw new Exception('View not defined');
		extract($this->vars);
		include $this->view;
	}
	
	
	
	/**
	 * Defines the name of the file to be used as the view
	 *
	 * @param string $name	File name
	 */
	protected function setView($name){
		$path = APP_DIR.'views/'.$this->name.'/'.$name;
		if(File::exists($path))
			$this->view = $path;
		else
			throw new Exception('View "'.$name.'" not found');
	}
	
	
	
	/**
	 * Defines a pair key-value or an associative array of keys-values which will be used as variables in the view
	 *
	 * @param string $key	Name of the variable (string) or associative array of variables (array)
	 * @param mixed $value	Value of the variable (useless if $key is an array)
	 */
	protected function set($key, $value=null){
		if(is_string($key)){
			$this->vars[$key] = $value;
		}else if(is_array($key)){
			$this->vars = array_merge($this->vars, $key);
		}
	}
	
	/**
	 * Defines the title to be added to the window title
	 *
	 * @param string $title	Title
	 */
	protected function setTitle($title){
		$this->title = $title;
	}
	
	
	/**
	 * Add a JS file to be included in the page
	 *
	 * @param string|array $filepath	JS file path, or list (array) of JS files
	 */
	public function addJSFile($filepath){
		if(is_array($filepath))
			$this->jsFiles += $filepath;
		else
			$this->jsFiles[] = $filepath;
	}
	
	/**
	 * Add JS code to be executed at the end of the page
	 *
	 * @param string $code	JS code
	 */
	public function addJSCode($code){
		$this->jsCode .= $code;
	}
	
	/**
	 * Add a CSS file to be included in the <head> tag of the page
	 *
	 * @param string|array $filepath	CSS file path, or list (array) of CSS files
	 */
	public function addCSSFile($filepath){
		if(is_array($filepath))
			$this->cssFiles += $filepath;
		else
			$this->cssFiles[] = $filepath;
	}
	
}
