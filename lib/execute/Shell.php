<?php

namespace bmca\execute{
	
	// Import Dependencies
	require __DIR__ . '/vendor/autoload.php'; 
	
	class Shell extends \MrRio\ShellWrap{
			
		/*
			Converts a string to its appropriate ShellWrap Function calls
			Also attempts to force caller to use hard coded instructions and not user data
				
			If this function isn't specified the inherited __call() & __callStatic()
			functions will capure the input and use the library normally 
		*/
		public static function execute($command){
			return shell_exec($command);
		}
		
		public static function executeFAF($command){
			exec($command.' > /dev/null 2>&1 &');
		}
		
		public static function executeSilent($command){
			exec($command);
		}
		
		//sanatizes input arguements before replacing all %V% with args
		public static function secureExecute($command = '', array $args = array()){
			//Process and execute command
			self::execute(self::secExec($command, $args));
		}
		
		//sanatizes input arguements and inserts them between each arg
		public static function secureExecuteArr(array $commands = array(), array $args = array()){
			//Process and execute command
			self::execute(self::secExecArr($commands, $args));
		}
		
		public static function secureExecuteFAF(array $commands = array(), array $args = array()){
			//Process and execute command
			self::executeFAF(self::secExecArr($commands, $args));
		}
		
		public static function secureExecuteSilent(array $commands = array(), array $args = array()){
			//Process and execute command
			self::executeSilent(self::secExecArr($commands, $args));
		}
			
		public static function secureExecuteArrFAF(array $commands = array(), array $args = array()){
			//Process and execute command
			self::executeFAF(self::secExecArr($commands, $args));
		}
		
		public static function secureExecuteArrSilent(array $commands = array(), array $args = array()){
			//Process and execute command
			self::executeSilent(self::secExecArr($commands, $args));
		}
		
		private static function secExec($command = '', array $args = array()){
			//if no command or no args given throw exception
			
			$newCommand = '';
			$lastAppendedIndex = 0;
			$lastAppendedArg = 0;
			
			//insert args
			for($i = 0; $i < count($command)-2; $i++){
				if($command[$i] == '%' && $command[$i+1] == 'V' && $command[$i+2] == '%'){
					$newCommand .= substr($command, $lastAppendedIndex, $i - $lastAppendedIndex) . escapeshellarg($args[$lastAppendedArg]);
					$lastAppendedIndex = $i;
					$lastAppendedArg++;					
				}
			}
			
			//if some arguments could not be inserted, throw exception
			//if(lastAppendedArg != count($args))
		
			//append the remaining command characters
			$newCommand .= substr($command, $lastAppendedIndex+3);
			
			return $newCommand;
		}
		
		private static function secExecArr(array $commands = array(), array $args = array()){
			//if there are too few/many commands/args
			//count($commands) -1 != count($args)
			//throw exception
			
			$argCount = count($args);
			
			//sanitize each arg
			for($i = 0; $i < $argCount; $i++)
				$args[$i] = escapeshellarg($args[$i]);
				
			$command = '';
			
			//combine commands and args
			for($i = 0; $i < $argCount; $i++)
				$command .= $commands[$i] . ' ' . $args[$i];
			$command .= $commands[$argCount];
			
			return $command;
		}
	}	
		
}
	
	
	
	