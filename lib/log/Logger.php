<?php 

	namespace bmca\log{
		
		// Import Dependencies
		require 'vendor/autoload.php';
	
		class Logger{
			
			protected $mono;
			
			protected $settings;
			
			const EMERGENCY = 'EMERGENCY';
			const ALERT = 'ALERT';
			const CRITICAL = 'CRITICAL';
			const ERROR = 'ERROR';
			const WARN = 'WARN';
			const NOTICE = 'NOTICE';
			const INFO = 'INFO';
			const DEBUG = 'DEBUG';
			
			protected $levels = array(
				\bmca\log\Logger::EMERGENCY => \Monolog\Logger::EMERGENCY,
				\bmca\log\Logger::ALERT => \Monolog\Logger::ALERT,
				\bmca\log\Logger::CRITICAL => \Monolog\Logger::CRITICAL,
				\bmca\log\Logger::ERROR => \Monolog\Logger::ERROR,
				\bmca\log\Logger::WARN => \Monolog\Logger::WARNING,
				\bmca\log\Logger::NOTICE => \Monolog\Logger::NOTICE,
				\bmca\log\Logger::INFO => \Monolog\Logger::INFO,
				\bmca\log\Logger::DEBUG => \Monolog\Logger::DEBUG
			);
			
			public function __construct($settings = array(), $name = 'Generic', $merge = true){
				
				//Import the settings for the logger
				if($merge){
					//Add default values to array then merge with input, any user defined values in
					//the input argument will overwrite the defaults as a result of array_merge
					$this->settings = array_merge(array(
						'name' => $name,
						'handlers' => array(
							new \Monolog\Handler\StreamHandler(__DIR__."/logs/$name-".date('y-m-d').'.log', \Monolog\Logger::DEBUG),
						),
						'processors' => array(),
					), $settings);
				} else{
					$this->settings = $settings;	
				}
				
				//Build the logger
				$this->mono = new \Monolog\Logger($this->settings['name']);
				
				//Build the handlers
				foreach($this->settings['handlers'] as $handler)
						$this->mono->pushHandler($handler);
				
				//Build the processors
				foreach($this->settings['processors'] as $processor)
						$this->mono->pushProcessor($processor);
			}
			
			public function debug($message, array $context = array()){
        		return $this->mono->addDebug($message, $context);
    		}
			
			public function info($message, array $context = array()){
        		return $this->mono->addInfo($message, $context);
    		}
			
			public function notice($message, array $context = array()){
        		return $this->mono->addNotice($message, $context);
    		}
			
			public function warning($message, array $context = array()){
        		return $this->mono->addWarning($message, $context);
    		}
			
			public function error($message, array $context = array()){
        		return $this->mono->addError($message, $context);
    		}
			
			public function critical($message, array $context = array()){
        		return $this->mono->addCritical($message, $context);
    		}
			
			public function alert($message, array $context = array()){
        		return $this->mono->addAlert($message, $context);
    		}
			
			public function emergency($message, array $context = array()){
        		return $this->mono->addEmergency($message, $context);
    		}
			
			public function getLevels(){
				return $this->levels;
			}
		
		}	
		
	}