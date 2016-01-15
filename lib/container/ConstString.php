<?php

namespace bmca\container {

    // Import Dependencies
    require 'vendor/autoload.php';

    class ConstString extends Constant
    {

        public function __construct($value)
        {
            if (isset($value) && is_string($value))
                parent::__construct($value);
            else
                \bmca\exception\Handler::fatalException('ERROR: ConstString constructor requires a string...');
        }

    }

}