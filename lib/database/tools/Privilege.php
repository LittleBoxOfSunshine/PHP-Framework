<?php 

namespace bmca\database{
		
	class Privilege{
		//Supported "Structure" Privileges	
		const CREATE = 'CREATE';
		const ALTER = 'ALTER';
		const DROP = 'DROP';
		const INDEX = 'INDEX';
		const CREATE_TEMPORARY_TABLES = 'CREATE TEMPORARY TABLES';
		const LOCK_TABLES = 'LOCK TABLES';
		
		//Supported "Data" Privileges
		const SELECT = 'SELECT';
		const INSERT = 'INSERT';
		const UPDATE = 'UPDATE';
		const DELETE = 'DELETE';
		
		//Supported "User" Privileges
		const CREATE_USER = 'CREATE USER';
		const GRANT_OPTION = 'GRANT OPTION';
		
		//Variables that make up a grantable privilege
		protected $privilege;
		protected $database;
		protected $table;
		protected $server;
		
		///Build the privilege using the provided properties
		public function __construct($privilege, $database, $table, $server){
			$this->privilege = $privilege;
			$this->database = $database;
			$this->table = $table;
			$this->server = $server;
		}
		
		///Accessor function for protected member privilege
		public function getPrivilege(){
			return $this->privilege;
		}
		
		///Accessor function for protected member database
		public function getDatabase(){
			return $this->database;
		}
		
		///Accessor function for protected member table
		public function getTable(){
			return $this->table;
		}
		
		///Accessor function for protected member server
		public function getServer(){
			return $this->server;
		}
		
		///Check if a privilege exists and is supported
		public static function isPrivilege($priv){
			return defined($priv);
		}
	}
	
	
	
}