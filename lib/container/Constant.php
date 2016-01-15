<?php

namespace bmca\container{

    // Import Dependencies
    require 'vendor/autoload.php';

    class Constant{

        private $value;

        public function __construct($value){
            $this->value = $value;
        }

        public function value(){
            return $this->value;
        }

    }

}