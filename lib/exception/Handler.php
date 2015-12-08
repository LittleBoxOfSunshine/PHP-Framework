<?php

	namespace bmca\exception{
		
		// TODO: Throw an actual php exception/something catchable for fatal?
		
		// Import Dependencies
		require 'vendor/autoload.php';
	
		class Handler{
			
			protected static $logger;
			
			protected static $initialized = false;
			
			protected static $error_mode;
		
			protected static $MODES = array(
				'DEV' => 0,
				'TEST' => 1,
				'PRODUCTION' => 2 
			);
			
			public static function init($mode, $logger, $fullTrace=true){
				// Set the handler mode, or default to DEV
				if(isset(self::$MODES[$mode]))
					self::$error_mode = $mode;
				else{
					self::$error_mode = self::$MODES['DEV'];
					self::recoverableException('No error handling mode given, using \bmca\exception::DEV', array(), debug_backtrace());
				}
				
				// Enable php error reporting when in dev mode
				if(self::$error_mode == self::$MODES['DEV']){
					error_reporting(E_ALL);
					ini_set('display_errors', 'On');
					ini_set('display_startup_errors', 'On');
				}
				
				// Set the logger
				if(isset($logger))
					self::$logger = array($logger);
				else
					self::$logger = array('default' => new \bmca\log\Logger());
			}
			
			//The error found does not lead to undefined behaviour, recovery does not change outcome of execution
			public static function recoverableException($message, $args=array(), $backtrace){
				//initialize if needed
				if(!self::$initialized)
					self::init();
				
				//Correct the given backtrace if needed
				if(!isset($backtrace[1]['class']) && !isset($backtrace[1]['function']))
					$backtrace = debug_backtrace();
				else if(!isset($backtrace[1]['class']))
					$backtrace[1]['class'] = 'N/A';
				else if(!isset($backtrace[1]['function']))
					$backtrace[1]['function'] = 'N/A';
					
				//Log the message, use mode to determine if execution should be halted
				self::output('Recoverable Exception: '.$message, $args, self::error_mode <= self::$MODES['DEV'], $backtrace[1]['class'], $backtrace[1]['function']);
			}
			
			//The error found does not lead to undefined behaviour, but recovery alters execution outcome in some way (e.g. break or return)
			public static function unrecoverableException($message, $args=array(), $backtrace){
				//initialize if needed
				if(!self::$initialized)
					self::init();
									
				//Correct the given backtrace if needed
				if(!isset($backtrace[1]['class']) && !isset($backtrace[1]['function']))
					$backtrace = debug_backtrace();
				else if(!isset($backtrace[1]['class']))
					$backtrace[1]['class'] = 'N/A';
				else if(!isset($backtrace[1]['function']))
					$backtrace[1]['function'] = 'N/A';
					
				//Log the message, use mode to determine if execution should be halted
				self::output('Unrecoverable Exception: '.$message, $args, self::error_mode <= self::$MODES['TEST'], $backtrace[1]['class'], $backtrace[1]['function']);
			}
			
			//The error found leads to undefined behaviour and execution must be halted
			public static function fatalException($message, $args=array(), $backtrace){
				//initialize if needed
				if(!self::$initialized)
					self::init();
								
				//Correct the given backtrace if needed
				if(!isset($backtrace[1]['class']) && !isset($backtrace[1]['function']))
					$backtrace = debug_backtrace();
				else if(!isset($backtrace[1]['class']))
					$backtrace[1]['class'] = 'N/A';
				else if(!isset($backtrace[1]['function']))
					$backtrace[1]['function'] = 'N/A';
						
				//Log the message, use mode to determine if execution should be halted
				self::output('Fatal Exception: '.$message, $args, self::error_mode <= self::$MODES['PRODUCTION'], $backtrace[1]['class'], $backtrace[1]['function']);
			}
			
			//The error found leads to undefined behaviour, but could potentially be recoverable in a way that defines behaviour
			public static function catchableFatalException($key, $message, &$args=array(), $backtrace){
				//backup message in case invalid catch implementation is given
				$origMessage = $message;
				
				//initialize if needed
				if(!self::$initialized)
					self::init();
					
				//Correct the given backtrace if needed
				if(!isset($backtrace[1]['class']) && !isset($backtrace[1]['function']))
					$backtrace = debug_backtrace();
				else if(!isset($backtrace[1]['class']))
					$backtrace[1]['class'] = 'N/A';
				else if(!isset($backtrace[1]['function']))
					$backtrace[1]['function'] = 'N/A';
				
				//if the handler function exists, use it. Otherwise throw an exception
				if($backtrace[1]['class'] instanceof \bmca\exception\CatchableException){
					$message = '';

                    $enumeration = $backtrace[1]['class']::getHandlerEnum();
					$recoveryResult = self::attemptExceptionRecovery($enumeration, $key, $message, $args);

					// Ensure that user implementation returned true or false
					if(!isset($recoveryResult) || !is_bool($recoveryResult))
						self::fatalException('Recovery function passed to catchableException must return a bool', array('Original_Exception' => array($origMessage, $args)), $backtrace);

					//run the recovery function, giving it a variable to write messages to
					//if it returns true, it's recoverable so run it so, else it is fatal so run it so
					if($recoveryResult)
						self::unrecoverableException($message, $args, $backtrace);
					else
						self::fatalException($message, $args, $backtrace);
				}
				else{
					self::unrecoverableException('catchableException interface is not implemented', array('Original_Exception' => array($origMessage, $args)), $backtrace);
				}
					
			}
			
			//Get the available error handling modes
			public static function getModes(){
				return self::$MODES;
			}
	
			//Outputs to appropriate logs and/or screen
			private static function output($message, $args, $crash, &$callingClass, &$callingFunction){
				//log the error
				self::$logger->error($message + "\n during {$callingClass} :: {$callingFunction} \n");
				
				//pass the error to any defined accompanying loggers
				if($callingClass instanceof \bmca\exception\ExceptionLogger)
					$callingClass::logException($message . var_export($args, true));
				
				//Crash if instructed
				if($crash){
					//log the backtrace if enabled
					if(self::$fullTrace)
						self::$activeLogger->error(var_export(debug_backtrace(), true));

					//show error in browser if not in production
					if(self::$error_mode == 'PRODUCTION')
						header('HTTP/1.1 500 Internal Server Error');
					else{
						echo $message;
						
						//Output the backtrace if enabled
						if(self::$fullTrace)
							var_dump(debug_backtrace());	
					}
					
					// Kill the script	
					die();	
				}
			}

            /// Function may be overridden to catch appropriate fatal exceptions
            private static function attemptExceptionRecovery(\bmca\container\Enum & $enumeration = NULL, &$key = NULL, &$message, array &$args = array()){
                if($enumeration !== NULL) {
                    if($enumeration->isKey($key)) {
                        $handler = $enumeration->$key;
                        if (is_callable($handler)) {
                            return $handler('');
                        }
                        else{
                            $message = 'Handler function is not callable...';
                            return false;
                        }
                    }
                    else {
                        $message = "Enumeration does not have a key $key...";
                        return false;
                    }
                }
                else {
                    $message = 'No enumeration given...';
                    return false;
                }
            }
		}	
		
	}