<?php

class Layout_Controller extends Controller {
	
	// Standard mode
	public function index(){
		$this->setView('index.php');
		
		// JS à charger
		$this->addJSFile($this->specificController->jsFiles);
		$this->addJSCode($this->specificController->jsCode);
		
		// CSS à charger
		if(File::exists(Config::DIR_APP_STATIC.'css/style.css')){
			$this->addCSSFile(Config::URL_STATIC.'css/style.css');
		}else{
			$files = glob(Config::DIR_APP_STATIC.'css/[0-9]-style.css');
			foreach($files as $file)
				$this->addCSSFile(Config::URL_STATIC.'css/'.substr($file, strrpos($file, '/')+1));
		}
		if(Config::DEBUG)
			$this->addCSSFile(Config::URL_STATIC.'css/debug.css');
		$this->addCSSFile($this->specificController->cssFiles);
		
		$this->set(array(
			'jsFiles'			=> & $this->jsFiles,
			'jsCode'			=> & $this->jsCode,
			'cssFiles'			=> & $this->cssFiles
		));
		
	}
	
	// Print mode
	public function printmode(){
		$this->setView('printmode.php');
	}
	
	// AJAX mode
	public function ajax(){
		$this->setView('ajax.php');
		header('Content-Type: text/xml; charset=utf-8');
	}
	
	// Rendering the specific view
	public function __renderContent(){
		$this->specificController->render();
	}
}
