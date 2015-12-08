<?php 
	
	namespace bmca\database{

		// Import Dependencies
		require __DIR__ . '/vendor/autoload.php';		

		// Forces PDO with prepared statements, enables query buffering
		class Connection{
			
			//Database the connection is using
			protected $DATABASE;
			
			//User the connection is using
			protected $USER;
			
			//Password the connection is using
			protected $PASSWORD;
			
			//IP Address the connection is using
			protected $ADDRESS;
			
			//Mysqli connection
			protected $CONNECTION;
			
			public function __construct($address, $user, $password, $database){	
				//set connection data
				$this->ADDRESS = $address;
				$this->DATABASE = $database;
				$this->USER = $user;
				$this->PASSWORD = $password;
				
				//initialize the connection
				try {
					$this->CONNECTION = new PDO("mysql:host=$this->ADDRESS;dbname=$this->DATABASE", $this->USER, $this->PASSWORD);
					$this->CONNECTION->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				} catch(PDOException $e) {
					 \bmca\exception\Handler::fatalException('ERROR: ' . $e->getMessage());
				}
			}
			
			public static function bindParam(){
				
			}
			
			public function fetchBound(){
				
			}
			
			public function fetchAll(){
				
			}
			
			public function fetchAssoc(){
				
			}
			
			public function fetchInto(){
				
			}
			
			private function fetch(){
				
			}
			
			public function enableQueryBuffer(){
				
			}
			
			public function disableQueryBuffer(){
				
			}
			
			public function flushQueryBuffer(){
				
			}
			
			public function prepare($prepare){
				
			}
			
			public function execute(array $args=array()){
				
			}
			

			
			/*
			
			public function fetchClass(){
				
			}
			
			public function fetchClassLate(){
				
			}
			
			*/
		}
		
	}