<?php 

	namespace bmca\optimize{
		
		//Enables gzip compression for HTTP clients that support it
		function gzipHTTP(){
			//Start php's integrated gzip handler
			ob_start("ob_gzhandler");
		}
		
	}
