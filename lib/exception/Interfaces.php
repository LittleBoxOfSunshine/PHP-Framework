<?php

namespace bmca\exception{
		
	// Import Dependencies
	require __DIR__ . '/vendor/autoload.php';	
		
	interface ExceptionLogger{
		/// Allows \bmca\exception\Handler to use any accompanying loggers specified by the user
		static function logException($message, array $args=array());
	}
	
	interface CatchableException{
		/// Allows handler to attempt to use user provided function to recover
		static function attemptExceptionRecovery(&$message, array &$args = array());
	}
	
}