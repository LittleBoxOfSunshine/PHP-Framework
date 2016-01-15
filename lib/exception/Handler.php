<?php

	namespace bmca\exception{

        //TODO: use constants instead of modes array (for speed)
		
		// Import Dependencies
		require 'vendor/autoload.php';
	
		class Handler{
			
			protected static $logger;
			
			protected static $initialized = false;
			
			protected static $error_mode;

            protected static $fullTrace;

            const MODE_DEV = 0;
            const MODE_TEST = 1;
            const MODE_PRODUCTION = 2;
			
			public static function init($mode=NULL, $logger=NULL, $fullTrace=true){
                $exceptionOccurred = false;
				// Set the handler mode, or default to DEV
				if($mode >= self::MODE_DEV && $mode <= self::MODE_PRODUCTION)
					self::$error_mode = $mode;
				else{
					self::$error_mode = self::MODE_DEV;
                    $exceptionOccurred = true;
				}
				
				// Enable php error reporting when in dev mode
				if(self::$error_mode == self::MODE_DEV){
					error_reporting(E_ALL);
					ini_set('display_errors', 'On');
					ini_set('display_startup_errors', 'On');
				}
				
				// Set the logger
				if(isset($logger))
					self::$logger = $logger;
				else
					self::$logger = new \bmca\log\Logger();

                if(is_bool($fullTrace))
                    self::$fullTrace = $fullTrace;
                else
                    self::$fullTrace = true;

                self::$initialized = true;

                if($exceptionOccurred)
                    self::recoverableException('No error handling mode given, using \bmca\exception::DEV', array(), debug_backtrace(false));
			}
			
			//The error found does not lead to undefined behaviour, recovery does not change outcome of execution
			public static function recoverableException($message, $args=array(), $backtrace=NULL){
				//initialize if needed
				if(!self::$initialized)
					self::init();

				//Correct the given backtrace if needed
				if(!isset($backtrace[1]['class']) && !isset($backtrace[1]['function']))
					$backtrace = debug_backtrace(false);
				else if(!isset($backtrace[1]['class']))
					$backtrace[1]['class'] = 'N/A';
				else if(!isset($backtrace[1]['function']))
					$backtrace[1]['function'] = 'N/A';

				//Log the message, use mode to determine if execution should be halted
				self::output('Recoverable Exception: '.$message, $args, false, $backtrace[1]['class'], $backtrace[1]['function']);
			}

			/// This is an alias for recoverableException
			public static function warning($message, $args=array(), $backtrace=NULL){
				self::recoverableException($message, $args, $backtrace);
			}
			
			//The error found does not lead to undefined behaviour, but recovery alters execution outcome in some way (e.g. break or return)
			public static function unrecoverableException($message, $args=array(), $backtrace=NULL){
				//initialize if needed
				if(!self::$initialized)
					self::init();
									
				//Correct the given backtrace if needed
				if(!isset($backtrace[1]['class']) && !isset($backtrace[1]['function']))
					$backtrace = debug_backtrace(false);
				else if(!isset($backtrace[1]['class']))
					$backtrace[1]['class'] = 'N/A';
				else if(!isset($backtrace[1]['function']))
					$backtrace[1]['function'] = 'N/A';
					
				//Log the message, use mode to determine if execution should be halted
				self::output('Unrecoverable Exception: '.$message, $args, self::$error_mode <= self::MODE_TEST, $backtrace[1]['class'], $backtrace[1]['function']);
			}
			
			//The error found leads to undefined behaviour and execution must be halted
			public static function fatalException($message, $args=array(), $backtrace=NULL){
				//initialize if needed
				if(!self::$initialized)
					self::init();
								
				//Correct the given backtrace if needed
				if(!isset($backtrace[1]['class']) && !isset($backtrace[1]['function']))
					$backtrace = debug_backtrace(false);
				else if(!isset($backtrace[1]['class']))
					$backtrace[1]['class'] = 'N/A';
				else if(!isset($backtrace[1]['function']))
					$backtrace[1]['function'] = 'N/A';
						
				//Log the message, use mode to determine if execution should be halted
				self::output('Fatal Exception: '.$message, $args, self::$error_mode <= self::MODE_PRODUCTION, $backtrace[1]['class'], $backtrace[1]['function']);
			}

            /**
             * The error found leads to undefined behaviour, but could potentially be recoverable in a way that defines behaviour
             * @param $key
             * @param $message
             * @param array $args
             * @param $backtrace
             */
            public static function catchableFatalException($key, $message, &$args=array(), $backtrace){
				//backup message in case invalid catch implementation is given
				$origMessage = $message;
				
				//initialize if needed
				if(!self::$initialized)
					self::init();
					
				//Correct the given backtrace if needed
				if(!isset($backtrace[1]['class']) && !isset($backtrace[1]['function']))
					$backtrace = debug_backtrace(false);
				else if(!isset($backtrace[1]['class']))
					$backtrace[1]['class'] = 'N/A';
				else if(!isset($backtrace[1]['function']))
					$backtrace[1]['function'] = 'N/A';
				
				//if the handler function exists, use it. Otherwise throw an exception
				if($backtrace[1]['class'] instanceof \bmca\exception\CatchableException){
					$message = '';

                    /** @noinspection PhpUndefinedMethodInspection */
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
				return array('DEV' => self::MODE_DEV, 'TEST' => self::MODE_TEST, 'PRODUCTION' => self::MODE_PRODUCTION);
			}
	
			//Outputs to appropriate logs and/or screen
			private static function output($message, $args, $crash, &$callingClass, &$callingFunction){
				//log the error
                /** @noinspection PhpUndefinedMethodInspection */
                self::$logger->error($message . "\n during {$callingClass} :: {$callingFunction} \n");

				//pass the error to any defined accompanying loggers
				if($callingClass instanceof \bmca\exception\ExceptionLogger)
					$callingClass::logException($message . var_export($args, true));

				//Crash if instructed
				if($crash) {
					//log the backtrace if enabled
					if (self::$fullTrace)
						/** @noinspection PhpUndefinedMethodInspection */
						self::$logger->error(self::prettyTrace(debug_backtrace(false)));

					$html = '';

					//show error in browser if not in production
					if (self::$error_mode === self::MODE_PRODUCTION) {
                        throw new \bmca\exception\BMCAHandlerException($message);
					} else {
						// output the message and backtrace
						$html .= '
                        <!DOCTYPE HTML>
                        <html lang="en">
                        <head>
                            <title>Application Error:</title>
                            <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
                            <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
                            <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
                        </head>
                        <body>
                        <h2>Application Error</h2><p>' . $message . '</p>' . self::prettyTrace(debug_backtrace(false)) . '
                        </body>
                        </html>
                        ';
					}

					// Kill the script	
					die($html);
				}
			}

            /// Function may be overridden to catch appropriate fatal exceptions
            private static function attemptExceptionRecovery(\bmca\container\Enum & $enumeration = NULL, &$key = NULL, &$message, array &$args = array()){
                if($enumeration !== NULL) {
                    if($enumeration->isKey($key)) {
                        $handler = $enumeration->$key;
                        if (is_callable($handler)) {
                            return $handler($message, $args) === true;
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

            private static function prettyTrace($trace){
                $idx = 0;

                // Skip trace entries for internal handler functions to avoid polluting the output
                while($trace[$idx]['class'] == 'bmca\exception\Handler' && $trace[$idx]['function'] != 'init')
                    $idx++;

                $html = '<table class="table table-hover table-condensed">
                    <thead>
                       <tr>
                        <th>No.</th>
                        <th>File</th>
                        <th>Line No.</th>
                        <th>Class</th>
                        <th>Type</th>
                        <th>Function</th>
                       </tr>
                    </thead>
                    <tbody>
                ';

                $counter = 0;

                for($idx; $idx < count($trace); $idx++){
                    if(!isset($trace[$idx]['class']) || !isset($trace[$idx]['class']))
                        continue;

                    if($idx >= 9)
                        break;

                    $html .= '
                        <tr data-toggle="collapse" data-target="#traceRow'.$counter.'" class="accordion-toggle">
                         <th>#'.$counter.'</th>
                         <th>'.$trace[$idx]['file'].'</th>
                         <th>'.$trace[$idx]['line'].'</th>
                         <th>'.$trace[$idx]['class'].'</th>
                         <th>'.$trace[$idx]['type'].'</th>
                         <th>'.$trace[$idx]['function'].'</th>
                        </tr>
                        <tr>
                         <th colspan="6" class="hiddenRow">
                          <div id="traceRow'.$counter++.'" class="accordian-body collapse">
                           <pre>'.var_export($trace[$idx]['args'], true).'</pre>
                          </div>
                         </th>
                        </tr>
                    ';
                }

                $html .= '</tbody></table>';

                return $html;
            }

		}
		
	}