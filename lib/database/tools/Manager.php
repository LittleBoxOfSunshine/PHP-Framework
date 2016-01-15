<?php

	namespace bmca\database{

		require_once 'Connection.php';
	
		class ManagedConnection{
			
			protected $connections = [];
			protected $defaultConnection;
			
			public function __construct($default = NULL, $connection = NULL){
				if(isset($default) && isset($connection)){
					if(getClass($connection) == 'Connection'){
						$this->defaultConnection = $default;
						$this->connections[$keys] = $connection;
					}
					else {}
						//throw an exception
				}
			}
			
			public function openConnection($key, $connection){
				//Force connection to be a connection object
				if(get_class($connection) == 'Connection')
					if(!isset($this->connection[$key]))
						$this->connection[$key] = $connection;
					else {}
						//throw an exception
				else {}
					//throw an exception 
			}
			
			public function openConnections($keys, $connections){
				//Check that sizes match
				//if(count($keys) != count($connections))
					//throw an exception
				
				//Insert each key-value pair
				for($i=0; $i<count($keys); $i++)
					openConnection($keys[$i], $connections[$i]);
			}
			
			public function closeConnection($key){
				//Unset the entry if possibele
				if(isset($this->connections[$key]))
					unset($this->connections[$key]);
				else{}
					//throw an exception
			}
			
			//Change exisiting connection to a new one
			public function resetConnection($key, $connection){
				//Force connection to be a connection object and force replacement (vs creating new)
				if(get_class($connection) == 'Connection')
					if(isset($this->connection[$key]))
						$this->connection[$key] = $connection;
					else {}
						//throw an exception
				else {}
					//throw an exception 
			}
			
			public function closeAllConnections(){
				//Remove data
				unset($this->connections);
				
				//Recreate array
				$this->connections = [];
			}
			
			//Returns an array with all connection keys
			public function getConnectionKeys(){
				//Array to hold keys
				$keys = [];
				
				//Append each key to array
				foreach($this->connections as $key => $val)
					$keys[] = $key;
				
				//Return key array
				return $keys;
			}
			
			//Checks if the key is present
			public function isKey($key){
				return isset($this->connections[$key]);
			}
			
			//Execute SQL query with appropriate user
			public function query($sql, $key = ''){
				if($key == '' && isset($this->defaultConnection))
					$key = $this->defaultConnection;
				else {}
					//throw an exception
				
				//Execute query on the requested connection if it exists	
				if(isset($this->connections[$key]))
					$this->connections[$key]->query($sql);
				else {}
					//throw an exception
			}
			
			//Execute SQL query but return array instead of matrix with just one row
			public function queryRow($sql, $key = ''){
				$temp = query($sql);
				return $temp[0];
			}
			
			//Execute SQL query but return single value instead of matrix with just one value
			public function queryValue($sql, $key = ''){
				$temp = query($sql);
				return $temp[0][0];
			}
		}
		
	}