<?php

	require_once 'vendor/autoload.php';

	namespace bmca\middleware{
	
		class Logger extends \Slim\middleware{
			
			public function call(){
				//Set the new log writer
				$this->app->log->setWriter(new \bmca\log\SlimLogger());
				
				//Call the next middleware
				$this->next->call();	
			}
			
		}
		
	}