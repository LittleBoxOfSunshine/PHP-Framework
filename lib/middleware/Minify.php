<?php

/* NOTE:
   The following changes have been made:
        namespace is now bmca\middleware
        php closing tag has been removed
        changed namespace ...; syntax to namespace ... { ... } syntax
*/

/*
 The MIT License (MIT)
Copyright (c) 2014 Christian Klisch
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace bmca\middleware {
    /**
     * Minify-Middleware is a summary of stackoverflow answers to reduce html traffic
     * by remove whitespaces, tabs and empty lines.
     * */
    class Minify
    {
        public function __invoke($request, $response, $next)
        {
            $response = $next($request, $response);

            $contentType = array_pop($response->getHeader('Content-Type'));

            $body = $response->getBody();

            if($contentType === 'text/html; charset=UTF-8')
                $minifiedBody = \bmca\optimize\Minify::minifyHTML($body);
            else if($contentType === 'text/css')
                $minifiedBody = \bmca\optimize\Minify::minifyCSS($body);
            else if($contentType === 'application/javascript')
                $minifiedBody = \bmca\optimize\Minify::minifyJS($body);
            else if($contentType === 'application/json')
                $minifiedBody = \bmca\optimize\Minify::minifyJSON($body);
            else
                return $response;

            $a = \GuzzleHttp\Psr7\stream_for($minifiedBody);
            $stream = new \GuzzleHttp\Psr7\AppendStream([$a]);
            $newResponse = $response->withBody($stream);

            return $newResponse;
        }
    }
}