<?php

namespace bmca\container {

    // Import Dependencies
    require 'vendor/autoload.php';

    class ConstInt extends Constant{

        public function __construct($value){
            if(isset($value) && is_integer($value))
                parent::__construct($value);
            else
                \bmca\exception\Handler::fatalException('ERROR: ConstInt constructor requires an integer...');
        }

    }

}