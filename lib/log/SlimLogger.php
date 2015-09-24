<?php 

	require_once 'vendor/autoload.php';

	namespace bmca\log{
	
		/*
			Simplifies use of Monolog middleware through set of configuration options
			that will dictate appropriate instructions to Monolog.
		*/
		class SlimLogger extends \bmca\log\Logger{
			
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