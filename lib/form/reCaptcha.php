<?php 

	namespace bmca\form{
	
		// Import Dependencies
		require __DIR__ . '/vendor/autoload.php'; 
	
		class reCaptcha{
			
			protected $SECRET;
			protected $SITE_KEY;
			protected $response;
			
			public function __construct($siteKey, $secret){
				$this->SITE_KEY = $siteKey;
				$this->SECRET = $secret;
			}
			
			//Checks to see if a valid response has been received
			public function checkResponse(){
				//If post data exists
				if(isset($_POST['g-recaptcha-response'])){
					
					//Sanatize Input
					filter_var($_POST['g-recaptcha-response'], FILTER_SANITIZE_STRING);
					
					//Recaptcha object for verification
					$recaptcha = new \ReCaptcha\ReCaptcha($this->SECRET);

					//Verify the response
    				$this->response = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
    
					//Check success status
					if($this->response->isSuccess()){
						return true;		
					}
				}
				
				//If code has made it to this point, something has failed return false
				return false;
			}
			
			//Gets the error codes of the last response if they exist
			public function getErrorCodes(){
				if(isset($this->response))
					return $this->response->getErrorCodes();
				else {}
					//throw an exception
			}
			
			//Returns the HTML to be included in the form
			public function getHTML(){
				return '
				<div class="g-recaptcha" data-sitekey="'.$this->SITE_KEY.'"></div>
				<script type="text/javascript"
						src="https://www.google.com/recaptcha/api.js?hl=<?php echo $lang; ?>">
				</script>
				';
			}
		}
	
	}