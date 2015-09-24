<?php 
	
	namespace bmca\database{

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
				$this->CONNECTION = mysqli_connect($this->ADDRESS, $this->USER, $this->PASSWORD, $this->DATABASE);
			}
			
			//Execute SQL query
			public function query($sql){
				//Sanatize input
				mysqli_real_escape_string($this->DATABASE, $query);
				
				//Initialize array for result storage
				$results = [];
				
				//Execute the query
				$rows = mysqli_query($this->DATABASE, $query);
				
				//Load response into matrix
				while ($row = $rows->fetch_row())
					$results[] = $row;
					
				//Return the matrix
				return $result;
			}
			
			//Execute SQL query but return array instead of matrix with just one row
			public function queryRow($sql){
				$temp = query($sql);
				return $temp[0];
			}
			
			//Execute SQL query but return single value instead of matrix with just one value
			public function queryValue($sql){
				$temp = query($sql);
				return $temp[0][0];
			}
			
		}
		
	}