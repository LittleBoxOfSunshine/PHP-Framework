<?php

namespace bmca\container {

    // Import Dependencies
    require 'vendor/autoload.php';

    class ConstBool extends Constant{

        public function __construct($value){
            if(isset($value) && is_boolean($value))
                parent::__construct($value);
            else
                \bmca\exception\Handler::fatalException('ERROR: ConstBool constructor requires a boolean...');
        }

    }
}