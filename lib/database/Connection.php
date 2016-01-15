<?php 
	
	namespace bmca\database{

		// Import Dependencies
		require 'vendor/autoload.php';

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
			
			// PDO Mysql connection
			protected $CONNECTION;

            // CURRENT PREPARED STATEMENT
            protected $statement;

            // Connection has been initialized
            protected $initialized = false;

            // If debug mode is enabled
            protected $DEBUG;
			
			public function __construct($address, $user, $password, $database, $debug=false){
				//set connection data
				$this->ADDRESS = $address;
				$this->DATABASE = $database;
				$this->USER = $user;
				$this->PASSWORD = $password;
                $this->DEBUG = is_bool($debug) ? $debug : false;
			}

            // Internal init function is called automatically if needed. This prevents unused connections from starting up
            private function init(){
                //initialize the connection
                try {
                    $this->CONNECTION = new \PDO("mysql:host=$this->ADDRESS;dbname=$this->DATABASE", $this->USER, $this->PASSWORD);
                    if($this->DEBUG)
                        $this->CONNECTION->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    else
                        $this->CONNECTION->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
                } catch(\PDOException $e) {
                    \bmca\exception\Handler::fatalException('ERROR: ' . $e->getMessage());
                }

                $this->initialized = true;
            }
			
			public function getStatement(){

				if(isset($this->statement)){
                    /** @noinspection PhpUndefinedMethodInspection */
                    return $this->statement;
                }
                else{
                    \bmca\exception\Handler::unrecoverableException('No statement exists to bind to', [$key, $value]);
                }

                return false;
			}

            /*
            public function applyBinding(\bmca\database\Binding & $binding){
                if(isset($this->statement)){
                    foreach($binding->getBindings() as $bind)
                        $this->statement->bindParam($bind->key, $bind->value);
                }
                else{
                    \bmca\exception\Handler::unrecoverableException('No statement exists to bind to', [$binding]);
                }
            }

			public function fetchBound(){
				
			}
            */

            public function fetchArray($key=0){
                if(isset($this->statement)) {
                    try{
                        /** @noinspection PhpUndefinedMethodInspection */
                        $data = $this->fetchAll(\PDO::FETCH_NUM);

                        for ($i = 0; $i < count($data); $i++)
                            $data[$i] = $data[$i][0];

                        return $data;
                    }
                    catch(\PDOException $e){
                        \bmca\exception\Handler::unrecoverableException('A SQL error occurred => '.$e->getCode().' '.$e->getMessage());
                    }
                }
                else{
                    \bmca\exception\Handler::unrecoverableException('No statement exists to fetchArray from');
                }

                return false;
            }
			
			public function fetchAll($mode=\PDO::FETCH_ASSOC){
                if(isset($this->statement)) {
                    try{
                        /** @noinspection PhpUndefinedMethodInspection */
                        return $this->statement->fetchAll($mode);
                    }
                    catch(\PDOException $e){
                        \bmca\exception\Handler::unrecoverableException('A SQL error occurred => '.$e->getCode().' '.$e->getMessage());
                    }
                }
                else{
                    \bmca\exception\Handler::unrecoverableException('No statement exists to fetchAll from');
                }

                return false;
			}
			
			public function fetchAssoc(){
                if(isset($this->statement)) {
                    try{
                        /** @noinspection PhpUndefinedMethodInspection */
                        return $this->statement->fetch(\PDO::FETCH_ASSOC);
                    }
                    catch(\PDOException $e){
                        \bmca\exception\Handler::unrecoverableException('A SQL error occurred => '.$e->getCode().' '.$e->getMessage());
                    }
                }
                else{
                    \bmca\exception\Handler::unrecoverableException('No statement exists to fetchAssoc from');
                }
			}
			
			public function fetchInto(){
				// TODO: fetch into object using binding object
			}

			public function enableQueryBuffer(){
                if(!$this->initialized)
                    self::init();

                if(isset($this->CONNECTION)) {
                    try{
                        $this->CONNECTION->beginTransaction();
                    }
                    catch(\PDOException $e){
                        \bmca\exception\Handler::unrecoverableException('A SQL error occurred => '.$e->getCode().' '.$e->getMessage());
                    }
                }
                else{
                    \bmca\exception\Handler::unrecoverableException('No connection exists to enableQueryBuffer for');
                }
			}
			
			public function disableQueryBuffer(){
                if(!$this->initialized)
                    self::init();

                if(isset($this->CONNECTION)) {
                    try{
                        $this->CONNECTION->rollBack();
                    }
                    catch(\PDOException $e){
                        \bmca\exception\Handler::unrecoverableException('A SQL error occurred => '.$e->getCode().' '.$e->getMessage());
                    }
                }
                else{
                    \bmca\exception\Handler::unrecoverableException('No connection exists to disableQueryBuffer for');
                }
			}

            /**
             *
             */
            public function flushQueryBuffer(){
                if(!$this->initialized)
                    self::init();

                if(isset($this->CONNECTION)) {
                    try{
                        $this->CONNECTION->commit();
                    }
                    catch(\PDOException $e){
                        \bmca\exception\Handler::unrecoverableException('A SQL error occurred => '.$e->getCode().' '.$e->getMessage());
                    }
                }
                else{
                    \bmca\exception\Handler::unrecoverableException('No connection exists to flushQueryBuffer from');
                }
			}

            /**
             *
             */
            public function nextRowSet(){
                if(isset($this->statement)) {
                    try{
                        /** @noinspection PhpUndefinedMethodInspection */
                        $this->statement->nextRowSet();
                    }
                    catch(\PDOException $e){
                        \bmca\exception\Handler::unrecoverableException('A SQL error occurred => '.$e->getCode().' '.$e->getMessage());
                    }
                }
                else{
                    \bmca\exception\Handler::unrecoverableException('No statement exists to get nextRowSet from');
                }
            }

            /**
             * @param $SQL
             */
            public function prepare($SQL){
                if(!$this->initialized)
                    self::init();

                if(isset($this->CONNECTION)) {
                    try{
                        $this->statement = $this->CONNECTION->prepare($SQL);
                    }
                    catch(\PDOException $e){
                        \bmca\exception\Handler::unrecoverableException('A SQL error occurred => '.$e->getCode().' '.$e->getMessage());
                    }
                }
                else{
                    \bmca\exception\Handler::unrecoverableException('No connection exists to prepare statement for');
                }
			}

            public function fetch($mode=\PDO::FETCH_ASSOC){
                if(isset($this->statement)) {
                    try{
                        /** @noinspection PhpUndefinedMethodInspection */
                        return $this->statement->fetch($mode);
                    }
                    catch(\PDOException $e){
                        \bmca\exception\Handler::unrecoverableException('A SQL error occurred => '.$e->getCode().' '.$e->getMessage());
                    }
                }
                else{
                    \bmca\exception\Handler::unrecoverableException('No statement exists to fetch from');
                }

                return false;
            }

            public function fetchValue(){
                if(isset($this->statement)) {
                    try{
                        /** @noinspection PhpUndefinedMethodInspection */
                        $data = $this->fetch(\PDO::FETCH_NUM);
                        return $data[0];
                    }
                    catch(\PDOException $e){
                        \bmca\exception\Handler::unrecoverableException('A SQL error occurred => '.$e->getCode().' '.$e->getMessage());
                    }
                }
                else{
                    \bmca\exception\Handler::unrecoverableException('No statement exists to fetchValue from');
                }

                return false;
            }

            /**
             * @param array $args
             */
            public function execute(array $args=array()){
                if(isset($this->statement)) {
                    try{
                        /** @noinspection PhpUndefinedMethodInspection */
                        //if(count($args) > 0)
                            $this->statement->execute($args);
                        //else
                            //$this->statement->execute();

                    }
                    catch(\PDOException $e){
                        \bmca\exception\Handler::unrecoverableException('A SQL error occurred => '.$e->getCode().' '.$e->getMessage());
                    }
                }
                else{
                    \bmca\exception\Handler::unrecoverableException('No statement exists to execute from');
                }
			}

            /**
             * @return mixed
             */
            public function lastInsertId(){
                if(isset($this->statement)) {
                    try{
                        /** @noinspection PhpUndefinedMethodInspection */
                        return $this->CONNECTION->lastInsertId();
                    }
                    catch(\PDOException $e){
                        \bmca\exception\Handler::unrecoverableException('A SQL error occurred => '.$e->getCode().' '.$e->getMessage());
                    }
                }
                else{
                    \bmca\exception\Handler::unrecoverableException('No statement exists to get lastInsertId from');
                }
            }
		}
		
	}