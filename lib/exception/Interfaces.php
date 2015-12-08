<?php

namespace bmca\exception{
		
	// Import Dependencies
	require 'vendor/autoload.php';
		
	interface ExceptionLogger{
		/// Allows \bmca\exception\Handler to use any accompanying loggers specified by the user
		static function logException($message, array $args=array());
	}

	interface CatchableException{

		public static function buildHandlerEnum();
	}

}