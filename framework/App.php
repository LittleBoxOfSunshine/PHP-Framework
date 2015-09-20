<?php 
	
	class App{
		
		protected $controller = 'Home';
		protected $method = 'index';
		
		public function __construct(){
			//Load args from URL
			$url = $this->parseURL();
			
			//Set controller if exists, else default is used
			if(file_exists('../app/controller/' . $url[0] . '.php')){
				$this->controller = $url[0];
				unset($url[0]);
			}
			
			//Include the controller
			require_once '../app/controller/' . $this->controller . '.php';
			
			//Set method if exists, else default is used
			if(isset($url[1])){
				if(method_exists($this->controller, $url[1])){
					$this->method = $url[1];
					unset($url[1]);
				}
			}
			
			//Split remaining args into array or create empty array if no args remain
			$this->args = $url ? array_values($url) : [];
			
			//Call the requested controller function with the given arguments
			call_user_func_array([$this->controller, $this->method], $this->args);
		}
		
		protected function parseURL(){
			//verify url exists
			if(isset($_GET['url'])){	
				//Remove trailing / (if exists) and split the url into an array
				$url = explode('/', rtrim($_GET['url'], '/'));
				return $url;
			}
		}
	}