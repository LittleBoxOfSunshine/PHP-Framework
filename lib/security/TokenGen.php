<?php 

	namespace bmca\security{

		class TokenGen{

			public static function tokenRaw($size=16){
				return bin2hex(openssl_random_pseudo_bytes($size));
			}

			public static function tokenb16($size=16){
				return bin2hex(self::tokenRaw($size));
			}

			public static function tokenb64($size=16){
				return base64_encode(self::tokenRaw($size));
			}

			public static function token2048bit(){
				return base64_encode(openssl_random_pseudo_bytes(256));
			}

			public static function token1024bit(){
				return base64_encode(openssl_random_pseudo_bytes(128));
			}

			public static function token512bit(){
				return base64_encode(openssl_random_pseudo_bytes(64));
			}

			public static function token256bit(){
				return base64_encode(openssl_random_pseudo_bytes(32));
			}

			public static function token128bit(){
				return base64_encode(openssl_random_pseudo_bytes(16));
			}

			public static function token64bit(){
				return base64_encode(openssl_random_pseudo_bytes(8));
			}

		}

	}