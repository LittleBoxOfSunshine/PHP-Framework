<?php 

	namespace bmca\optimize{

		class HTTPCompress
		{
			//Enables gzip compression for HTTP clients that support it
			public static function gzipHTTPStart()
			{
				//Start php's integrated gzip handler
                if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
                    ob_start('ob_gzhandler');
                else
                    ob_start();
			}

            public static function gzipHTTPEnd(){
                $buffer = ob_get_contents();
                ob_end_clean();
                return $buffer;
            }
		}
	}
