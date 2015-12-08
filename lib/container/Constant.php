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

    class ConstString extends Constant{

        public function __construct($value){
            if(isset($value) && is_string($value))
                parent::__construct($value);
            else
                \bmca\exception\Handler::fatalException('ERROR: ConstString constructor requires a string...');
        }

    }

    class ConstBool extends Constant{

        public function __construct($value){
            if(isset($value) && is_boolean($value))
                parent::__construct($value);
            else
                \bmca\exception\Handler::fatalException('ERROR: ConstBool constructor requires a boolean...');
        }

    }

    class ConstInt extends Constant{

        public function __construct($value){
            if(isset($value) && is_integer($value))
                parent::__construct($value);
            else
                \bmca\exception\Handler::fatalException('ERROR: ConstInt constructor requires an integer...');
        }

    }

    class ConstDouble extends Constant{

        public function __construct($value){
            if(isset($value) && is_double($value))
                parent::__construct($value);
            else
                \bmca\exception\Handler::fatalException('ERROR: ConstDouble constructor requires a double...');
        }

    }

}