<?php

namespace bmca\container {

    // Import Dependencies
    require 'vendor/autoload.php';

    class ConstDouble extends Constant{

        public function __construct($value){
            if(isset($value) && is_double($value))
                parent::__construct($value);
            else
                \bmca\exception\Handler::fatalException('ERROR: ConstDouble constructor requires a double...');
        }

    }

}