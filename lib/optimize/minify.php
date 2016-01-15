<?php

namespace bmca\optimize{

    class Minify
    {
        //Enables gzip compression for HTTP clients that support it
        public static function minifyHTML($html)
        {
            $search = array('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/', '/\n/', '/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s');
            $replace = array(' ', ' ', '>', '<', '\\1');
            return preg_replace($search, $replace, $html);
        }

        public static function minifyCSS($css)
        {
            # remove comments first (simplifies the other regex)
            $re1 = <<<'EOS'
                (?sx)
                  # quotes
                  (
                    "(?:[^"\\]++|\\.)*+"
                  | '(?:[^'\\]++|\\.)*+'
                  )
                |
                  # comments
                  /\* (?> .*? \*/ )
EOS;

            $re2 = <<<'EOS'
                (?six)
                  # quotes
                  (
                    "(?:[^"\\]++|\\.)*+"
                  | '(?:[^'\\]++|\\.)*+'
                  )
                |
                  # ; before } (and the spaces after it while we're here)
                  \s*+ ; \s*+ ( } ) \s*+
                |
                  # all spaces around meta chars/operators
                  \s*+ ( [*$~^|]?+= | [{};,>~+-] | !important\b ) \s*+
                |
                  # spaces right of ( [ :
                  ( [[(:] ) \s++
                |
                  # spaces left of ) ]
                  \s++ ( [])] )
                |
                  # spaces left (and right) of :
                  \s++ ( : ) \s*+
                  # but not in selectors: not followed by a {
                  (?!
                    (?>
                      [^{}"']++
                    | "(?:[^"\\]++|\\.)*+"
                    | '(?:[^'\\]++|\\.)*+'
                    )*+
                    {
                  )
                |
                  # spaces at beginning/end of string
                  ^ \s++ | \s++ \z
                |
                  # double spaces to single
                  (\s)\s+
EOS;

            $css = preg_replace("%$re1%", '$1', $css);
            return preg_replace("%$re2%", '$1$2$3$4$5$6$7', $css);
        }

        public static function minifyJS($js)
        {
            return \JShrink\Minifier::minify($js);
        }

        public static function minifyJSON($json)
        {
            return \bmca\optimize\JSONMin::minify($json);
        }
    }
}
