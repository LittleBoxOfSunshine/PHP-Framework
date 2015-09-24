<?php 

	namespace bmca\database{
	
		class Database{
			
			protected $connection;
			protected $database;
			
			private function validateConnection($connection){
				//
				if(get_class($connection) == 'Connection')
					$this->connection = $connection;	
				else {}
					//throw an exception
					
				//Create Database object
			}
			
		}
		
	}