<?php

	namespace bmca\middleware{
		
		// Import Dependencies
		require __DIR__ . '/vendor/autoload.php';
	
		class Optimize extends \Slim\middleware{
			
			public function call(){
                //Enable GZip HTTP Compression
                \bmca\Optimize::gzipHTTP();
                
				//Other than GZip, this middleware only runs on the exit cycle, call the next middleware
				$this->next->call();
                
                //All other middleware have completed execution, optimization can begin
                
                //Minify
			}
			
		}
		
	}