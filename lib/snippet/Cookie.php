<?php

namespace bmca\snippet {

    class Cookie {

        public static function setCookie($name, $value, $time = null, $path = null, $domain = null, $secure = null, $httponly = null)
        {
            // Unencrypted cookies
            setcookie($name, urlencode($value), $time, $path, $domain, $secure, $httponly);
        }

        public static function setEncryptedCookie($name, $value, $time = null, $path = null, $domain = null, $secure = null, $httponly = null, $key){

            $iv = mcrypt_create_iv(
                mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC),
                MCRYPT_DEV_URANDOM
            );

            $encryptedValue = base64_encode(
                $iv .
                mcrypt_encrypt(
                    MCRYPT_RIJNDAEL_128,
                    hash('sha256', $key, true),
                    $value,
                    MCRYPT_MODE_CBC,
                    $iv
                )
            );

            setcookie($name, urlencode($encryptedValue), $time, $path, $domain, $secure, $httponly);
        }

        public static function editCookie($name, $value, $time = null, $path = null, $domain = null, $secure = null, $httponly = null){
            if(!isset($_COOKIE[$name]))
                \bmca\exception\Handler::warning("There is no cookie named $name to edit. Creating a new cookie...");

            self::setCookie($name, $value, $time, $path, $domain, $secure, $httponly);
        }

        public static function getCookie($name)
        {
            // Check if the cookie exists
            if(!isset($_COOKIE[$name])) {
                \bmca\exception\Handler::unrecoverableException("There is no cookie named $name. Returning NULL...");
                return null;
            }

            // Get cookie value
            $value = urldecode($_COOKIE[$name]);

            return $value === false ? null : $value;
        }

        public static function exists($name) {
            return isset($_COOKIE[$name]);
        }

        public static function getEncryptedCookie($name, $key) {
            $value = base64_decode(self::getCookie($name));

            $iv = substr($value, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));

            $value = rtrim(
                mcrypt_decrypt(
                    MCRYPT_RIJNDAEL_128,
                    hash('sha256', $key, true),
                    substr($value, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC)),
                    MCRYPT_MODE_CBC,
                    $iv
                ),
                "\0"
            );

            return $value;
        }

        public static function deleteCookie($name, $path=null, $domain=null, $secure=null, $httponly=null)
        {
            if(!isset($_COOKIE[$name])) {
                \bmca\exception\Handler::warning("There is no cookie named $name to delete...");
            }
            else {
                unset($_COOKIE[$name]);
                setcookie($name, null, 0, $path, $domain, $secure, $httponly); // empty value and old timestamp
            }
        }

    }
}