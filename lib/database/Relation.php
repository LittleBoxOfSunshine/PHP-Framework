<?php

namespace bmca\database{
		
	// Import Dependencies
	require __DIR__ . '/vendor/autoload.php';	

	interface Relation{
		
		//Execute SQL query
		public function select($table, $attributes, $where);
			
		//
		public function insert($table, $attributes, $args);
		
		//
		public function update($table, $attributes, $args, $where);
		
		public function deleteFrom($table, $where);
	
	}
	
}