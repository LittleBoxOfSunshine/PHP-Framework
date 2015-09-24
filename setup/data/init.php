<?php

	/*
		BMCA PHP Framework Template:
		
			Here is a template/hello world program that uses the BMCA PHP Framework
			(BMCA PHP Framework is a set of custom middleware and libraries to add security,
			optimization, and advanced autoconfiguration abilities to the Slim Microframework)
	*/
	
	use \bmca\framework\App as App; 

	$app = new App( array(
		'mode' => App::DEV_MODE,
		'debug' => true,
		'log.level' => \Slim\Log::DEBUG,
		'log.enabled' => true,
		'cookies.encrypt' => true,
		'cookies.lifetime' => '20 minutes',
		'cookies.path' => '/',
		'cookies.domain' => 'domain.com',
		'cookies.secure' => true,
		'cookies.httponly' => false,
		'cookies.secret_key' => 'secret',
		'cookies.cipher' => MCRYPT_RIJNDAEL_256,
		'cookies.cipher_mode' => MCRYPT_MODE_CBC
	));
	
	//Add custom middleware here (don't use anything in the \bmca\middleware namespace 
	//or slim middleware, your config array will handle adding that middleware as appropriate)
	
	//Define your routes here
	$app->get('/foo', function () {
		echo '<h1>Hello World!</h1>';
	});
	
	$app->run();