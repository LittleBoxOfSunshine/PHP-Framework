<?php

namespace bmca\database{
		
	// Import Dependencies
	require __DIR__ . '/vendor/autoload.php';	
		
	abstract class DBModel implements Relation, CRUD{
		
		private static $binds;
	
		/// Allows \bmca\exception\Handler to use any accompanying loggers specified by the user
		abstract public static function setBinding(\bmca\database\Bind & $bind);
		
		//Execute SQL query
		public function select($table, $attributes, $where){
				
		}
			
		//
		public function insert($table, $attributes, $args){
			
		}
			
		//
		public function update($table, $attributes, $args, $where){
								
		}
			
		public function deleteFrom($table, $where){
				
		}
		
		private function 
	}
}