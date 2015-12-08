<?php 

	namespace bmca\log{
		
		// Import Dependencies
		require 'vendor/autoload.php';
	
		/*
			Simplifies use of Monolog middleware through set of configuration options
			that will dictate appropriate instructions to Monolog.
		*/
		class SlimLogger extends \bmca\log\Logger{
			
			//Invoke the Logger constructor but change the name to Slim
			public function __construct($settings = array(), $merge = true){
				parrent::__construct($settings, 'Slim', $merge);	
			}
			
			//Pass along slim log record to monolog
			public function write($object, $level){
				//if level exists use it, else default to WARNING
				$this->addRecord(
					isset($this->levels[$level]) ? $this->levels[$level] : \Monolog\Logger::WARNING, 
					$object
				);
			}
		}	
		
	}