<?php 

	require_once 'vendor/autoload.php';

	namespace bmca\log{
	
		class Logger{
			
			protected $resource;
			
			protected $settings;
			
			protected $levels = array(
				\bmca\log::EMERGENCY => \Monolog\Logger::EMERGENCY,
				\bmca\log::ALERT => \Monolog\Logger::ALERT,
				\bmca\log::CRITICAL => \Monolog\Logger::CRITICAL,
				\bmca\log::ERROR => \Monolog\Logger::ERROR,
				\bmca\log::WARN => \Monolog\Logger::WARNING,
				\bmca\log::NOTICE => \Monolog\Logger::NOTICE,
				\bmca\log::INFO => \Monolog\Logger::INFO,
				\bmca\log::DEBUG => \Monolog\Logger::DEBUG,
			);
			
			public function __construct($settings = array(), $name = 'Generic', $merge = true){
				
				//Import the settings for the logger
				if($merge){
					//Add default values to array then merge with input, any user defined values in
					//the input arguement will overwrite the defaults as a result of array_merge
					$this->settings = array_merge(array(
						'name' => $name,
						'handlers' => array(
							new \Monolog\Handler\StreamHandler(__DIR__.'/logs/'.date('y-m-d').'.log', \Monolog\Logger::DEBUG),
						),
						'processors' => array(),
					), $settings);
				} else{
					$this->settings = $settings;	
				}
				
				//Build the logger
				$this->resource = new \Monolog\Logger($this->settings['name']);
				
				//Build the handlers
				foreach($this->settings['handlers'] as $handler)
						$this->resource->pushHandler($handler);
				
				//Build the processors
				foreach($this->settings['processors'] as $processor)
						$this->resource->pushProcessor($processor);
			}
		
		}	
		
	}