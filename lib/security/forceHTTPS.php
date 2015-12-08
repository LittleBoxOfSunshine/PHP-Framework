<?php 

	namespace bmca\security{
		
		//Flags for uri wildcard matching
		const URI_PREFIX_WILDCARD = -1;
		const URI_NO_wILDCARD = 0;
		const URI_SUFFIX_WILDCARD = 1;
		
		///Forces all connections to use HTTPS when invoked
		function forceHTTPS(){
			//If https is not enabled, redirect to the https version of the page
			if($_SERVER['HTTPS'] != 'on'){
    			header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    			exit();
			}
		}
		
		///Forces all connections to the given uris to use HTTPS when invoked, 
		function requireHTTPS(array $uris = array(), $invert = false, $wildCard = \bmca\security\URI_NO_WILDCARD){
			//If https is already enabled, there is nothing to check
			if($_SERVER['HTTPS'] == 'on')
				return;
			
			$uriInArray = '';
			
			//Respond to the wildcard setting
			switch($wildCard){
				case \bmca\security\URI_NO_WILDCARD:
				$uriInArray = in_array($_SERVER['REQUEST_URI'], $uris);
				break;
				
				case \bmca\security\URI_PREFIX_WILDCARD:
				$uriInArray = count(preg_grep('.*'.preg_quote($_SERVER['REQUEST_URI']).'$', $uris)) > 0;
				break;
				
				case \bmca\security\URI_SUFFIX_WILDCARD:
				$uriInArray = count(preg_grep('^'.preg_quote($_SERVER['REQUEST_URI']).'.*', $uris)) > 0;
				break;
				
				default:
				\bmca\exception\Handler::recoverableException("Wildcard $wildCard is not defined, use one of the constants in \\bmca\\security");
				return;
			}
			
			//If the above condition holds true when invert is applied then https needs to be enabled
			if($invert ? !$uriInArray : $uriInArray){	
				header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
				exit();
			}

		}
		
		
	}