<?php 

namespace bmca\container{

	// Import Dependencies
	require 'vendor/autoload.php';

	class Enum {

        private $data = [];

		public function __construct(array $args = array()){
            $counter = 0;

            foreach($this->$args as $key => $value){
                if(is_integer($key)){
                    // find the next unused number
                    while(in_array($counter, $key))
                        $counter++;
                    $this->data[$key] = $counter++;
                }
                else{
                    $this->data[$key] = $value;
                }
            }
		}

        public function __get($key){
            if(isset($this->data[$key]))
                return $this->data[$key];
            else
                \bmca\exception\Handler::fatalException("ERROR: The enum key $key does not exist...");
        }

        public function isKey($key){
            return array_key_exists($key, $this->data);
        }

        public function getKeys(){
            return array_keys($this->data);
        }

	}
		
}