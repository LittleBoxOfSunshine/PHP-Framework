<?php 

	namespace bmca\form{
	
		// Import Dependencies
		require 'vendor/autoload.php';

        use \bmca\container\ConstString as ConstString;
	
		class reCaptcha{

            private static $SECRET;
            private static $SITE_KEY;
            private static $response;

            private static $initialized = false;
			
			public static function init($siteKey, $secret){
				self::$SITE_KEY = new ConstString($siteKey);
				self::$SECRET = new ConstString($secret);

                self::$initialized = true;
			}
			
			//Checks to see if a valid response has been received
			public static function checkResponse(array & $responseData=array(), $nativePOST=false){

                // Verify instantiation has occurred
                if(!self::$initialized)
                    self::crash();

				// If using native post, assign the variable
				if($nativePOST){
					if(count($responseData) == 1)
						\bmca\exception\Handler::warning('Response data provided, but $nativePOST is true, so $responseData is being ignored.', $responseData);

					$responseData = $_POST['g-recaptcha-response'];
				}
				// Not using native POST, and NO data provided, display error message.
				else if(is_null($responseData)){
					\bmca\exception\Handler::unrecoverableException('No data provided to verify recaptcha. Verification defaulting to false...');
				}
                // Not using native POST, data IS provided
                else{
                    $data = $responseData['g-recaptcha-response'];
                }

				//If post data exists and isn't null
				if(isset($data) && !is_null($data)){
					
					//Sanatize Input
					filter_var($data, FILTER_SANITIZE_STRING);
					
					//Recaptcha object for verification
					$recaptcha = new \ReCaptcha\ReCaptcha(self::$SECRET->value());

					//Verify the response
    				self::$response = $recaptcha->verify($data, $_SERVER['REMOTE_ADDR']);
    
					//Check success status
					if(self::$response->isSuccess()){
						return true;		
					}
				}

				//If code has made it to this point, something has failed return false
				return false;
			}
			
			//Gets the error codes of the last response if they exist
			public static function getErrorCodes(){
                // Verify instantiation has occurred
                if(!self::$initialized)
                    self::crash();

                if(isset(self::$response))
					return self::$response->getErrorCodes();
				else {}
					//throw an exception
			}
			
			//Returns the HTML to be included in the form
			public static function getHTML($count=1, $lightTheme=true){
                // Verify instantiation has occurred
                if(!self::$initialized)
                    self::crash();

                if($count <= 1) {
                    return '
                    <div class="g-recaptcha" data-sitekey="' . self::$SITE_KEY->value() . '"></div>
                    <script type="text/javascript"
                            src="https://www.google.com/recaptcha/api.js">
                    </script>
                    ';
                }
                else{
                    $lightTheme = $lightTheme ? 'light' : 'dark';

                    $data = [];

                    for($i = 1; $i <= $count; $i++)
                        $data[] = "<div id=\"recaptcha$i\"></div>";

                    $data = ['divs' => $data];

                    $script = '<script src="https://www.google.com/recaptcha/api.js?onload=recaptchaCallBack&render=explicit" async defer></script>
                               <script>';

                    for($i = 1; $i <= $count; $i++)
                        $script .= "var recaptcha$i;";

                    $script .= 'var recaptchaCallBack = function() {';

                    for($i = 1; $i <= $count; $i++)
                        $script .= "recaptcha$i = grecaptcha.render('recaptcha$i', {
                            'sitekey' : '".self::$SITE_KEY->value()."',
                            'theme' : '$lightTheme'
                        });";

                    $script .= "}; </script>";

                    $data['script'] = $script;

                    return $data;
                }
			}

            private static function crash(){
                \bmca\exception\Handler::fatalException('reCaptcha must be initialized before it can be used...');
            }
		}
	
	}