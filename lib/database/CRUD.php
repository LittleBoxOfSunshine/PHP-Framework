<?php

namespace bmca\database{
		
	// Import Dependencies
	require __DIR__ . '/vendor/autoload.php';	

	interface CRUD{
		
		//Execute SQL query
		public function create($table, $attributes, $where);
			
		//
		public function read($table, $attributes, $args);
		
		//
		public function update($table, $attributes, $args, $where);
		
		public function delete($table, $where);
	
	}
	
}