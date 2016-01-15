<?php

namespace bmca\middleware {

    class Session
    {
        public function __invoke($request, $response, $next)
        {
            if(!isset($_SESSION)) {
                session_start();
            }

            return $next($request, $response);
        }
    }
}