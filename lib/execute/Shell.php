<?php

	namespace bmca\execute{
	
		require_once 'vendor/autoload.php';
	
		class Shell extends MrRio\ShellWrap{
			
			/*
				Converts a string to its appropriate ShellWrap Function calls
				Also attempts to force caller to use hard coded instructions and not user data
				
				If this function isn't specified the inherited __call() & __callStatic()
				functions will capure the input and use the library normally 
			*/
			public function execute(){
				
			}
			
		}	
		
	}
	
	