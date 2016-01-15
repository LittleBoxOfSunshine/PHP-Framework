<?php

namespace bmca\middleware{

    // Import Dependencies
    require 'vendor/autoload.php';

    class Authentication
    {
        public function __invoke($request, $response, $next)
        {

            return $response;
        }
    }
}