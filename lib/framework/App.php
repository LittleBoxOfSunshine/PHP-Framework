<?php
	
namespace bmca\framework{
    
    // Import Dependencies
    require 'vendor/autoload.php';
	
	class App extends \Slim\App
    {

        protected $logger;
        protected $app_settings;

        protected $errorHTML = [];

        private $autoload; // function used for autoloading based on regex routes

        const MODE_DEV = 'DEV';
        const MODE_TEST = 'TEST';
        const MODE_PRODUCTION = 'PRODUCTION';

        const DEFAULT_404_PAGE = 'Error (404) <br> The requested URL does not exist...';
        const DEFAULT_500_PAGE = 'Error (500) <br> An internal server has occurred...';

        public function __construct(array $userSettings = array()){

            // Define default settings that are specific to bmca\framework\App
            $this->app_settings = array_merge(array(
                'middleware_optimize' => false,
                'middleware_minify' => false,
                'middleware_uglify' => false,
                'middleware_cache' => false,
                'middleware_client_session' => false,
                'middleware_session' => false,
                'middleware_csrf' => false,
                'middleware_restful_csrf' => false,
                'middleware_twig' => false,
                'mode' => self::MODE_DEV
            ), $userSettings
            );

            // Apply any settings defined in the config file
            if(file_exists('app/res/config.json')) {
                $config_file = json_decode(file_get_contents('app/res/config.json'), true);
                if(isset($config_file))
                    $this->app_settings = array_merge($this->app_settings, $config_file);
                else
                    \bmca\exception\Handler::unrecoverableException('The config file could not be parsed. Skipping.');
            }

            $this->getContainer()['app_settings'] = $this->app_settings;

            // Define default settings for slim
            $slimSettings = [];
            if($this->app_settings['mode'] == self::MODE_DEV){
                $slimSettings['mode'] = 'development';
                $slimSettings['debug'] = true;
            }
            else{
                $slimSettings['debug'] = false;
            }

            // Pass along settings to slim
            parent::__construct($slimSettings);

            // Initialize the logger
            $this->logger = new \bmca\log\Logger();

            // Initialize the error handler
            switch($this->app_settings['mode']){
                case self::MODE_DEV:
                    \bmca\exception\Handler::init(\bmca\exception\Handler::MODE_DEV, $this->logger);
                    break;
                case self::MODE_TEST:
                    \bmca\exception\Handler::init(\bmca\exception\Handler::MODE_TEST, $this->logger);
                    break;
                case self::MODE_PRODUCTION:
                    \bmca\exception\Handler::init(\bmca\exception\Handler::MODE_PRODUCTION, $this->logger);
                    break;
                default:
                    \bmca\exception\Handler::init(\bmca\exception\Handler::MODE_DEV, $this->logger);
                    \bmca\exception\Handler::fatalException('The given app mode '.$this->app_settings['mode'].' is invalid...');
            }

            /*
                Initialize middleware
            */

            // Simple Middleware Layer to handle outputting
          //  $this->add(function($request, $response){

         //   });

            if($this->app_settings['middleware_twig']) {
                $container = $this->getContainer();
                $container['view'] = function ($c) {
                    $view = new \Slim\Views\Twig('app/templates/', [
                        //'cache' => 'app/cache'
                    ]);
                    $view->addExtension(new \Slim\Views\TwigExtension(
                        $c['router'],
                        $c['request']->getUri()
                    ));

                    return $view;
                };
            }

            if($this->app_settings['middleware_csrf']) {
                $container = $this->getContainer();

                // TODO: CSRF MIDDLEWARE
            }

            if($this->app_settings['middleware_restful_csrf']) {
                $container = $this->getContainer();

                // TODO: RESTFUL CSRF MIDDLEWARE

                if($this->app_settings['middleware_csrf']) {
                    // TODO: issue warning about both being on
                }else{

                }
            }

            // Alias for maximum level of optimization
            if ($this->app_settings['middleware_optimize']) {
               // $this->app_settings['middleware_optimize'] = true;
                $this->app_settings['middleware_minify'] = true;
                $this->app_settings['middleware_uglify'] = true;
            }

            // Enable Minification
            if ($this->app_settings['middleware_minify'] && $this->app_settings['mode'] != self::MODE_DEV)
                $this->add(new \bmca\middleware\Minify());

            // Enable Uglification
            //if ($this->app_settings['middleware_uglify'])
                //$this->add(new \bmca\middleware\Optimize());

            // Enable Compiler TODO: Implement compiler

            //Enable Session
            if ($this->app_settings['middleware_session'])
                $this->add(new \bmca\middleware\Session());

            // TODO: be sure both session types are not enabled at the same time

            // Enable Client Session
            if ($this->app_settings['middleware_client_session'])
                $this->add(new \bmca\middleware\ClientSession());

            //Enable Cache
            if ($this->app_settings['middleware_cache'])
                $this->add(new \bmca\middleware\Cache());

            /*
               Define Models
            */

            // Automatically load model files
            $models = \bmca\snippet\FilePaths::rglob('app/models/*.php', true);
            foreach($models as $model) {
                try{
                    require $model;
                }catch(Exception $e){
                    \bmca\exception\Handler::unrecoverableException("Failed to load the model $model, php message -> ". $e->getMessage());
                }
            }

            /*
	           Define routes
            */

            // Automatically load router files
            $routers = \bmca\snippet\FilePaths::rglob('app/routers/*.php', true);
            foreach($routers as $router) {
                try {
                    require $router;
                } catch (Exception $e) {
                    \bmca\exception\Handler::unrecoverableException("Failed to load the model $router, php message -> " . $e->getMessage());
                }
            }

            /*
             * Define CSS/JS automatic loading
             */
            $this->autoload = function($path, $type, &$response) {

                $response = $response->withHeader('Content-Type', $type);

                if(file_exists("app/public/$path")) {
                    $html = file_get_contents("app/public/$path");
                }
                // Return 404 error
                else{
                    $html = $this->get404($response);
                    $response = $response->withStatus(404);
                }

                if($html !== false) {
                    $a = \GuzzleHttp\Psr7\stream_for($html);
                    $stream = new \GuzzleHttp\Psr7\AppendStream([$a]);
                    $response = $response->withBody($stream);
                }

                return $response;
            };

            $autoload = $this->autoload;

            $this->get('/css/{path:.*\.css}', function($request, $response, $args) use (&$autoload) {
                return $autoload('css/'.$args['path'], 'text/css', $response);
            });

            $this->get('/js/{path:.*\.js}', function($request, $response, $args) use (&$autoload) {
                return $autoload('js/'.$args['path'], 'application/javascript', $response);
            });

        }

        private function getErrorPage(&$response, $type='text/html; charset=UTF-8', $errorNo){

            $response = $response->withHeader('Content-Type', $type);

            if(isset($this->errorHTML[$errorNo])) {
                // Additional error checking is not necessary, set404 will catch any possible errors
                // before this code is run
                if ($this->errorHTML[$errorNo]['isTwigPath']) {
                    $this->getContainer()['view']->render($response, $this->errorHTML[$errorNo]['html'], []);
                    return false;
                } else {
                    return $this->errorHTML[$errorNo]['html'];
                }
            }else{
                if($errorNo == 500)
                    return self::DEFAULT_500_PAGE;
                else if($errorNo == 404)
                    return self::DEFAULT_404_PAGE;
            }
        }

        private function setErrorPage($html, $isTwigPath=false, $errorNo){

            $container = $this->getContainer();

            if($errorNo == '404')
                $handler = 'notFoundHandler';
            else if($errorNo == '500')
                $handler = 'errorHandler';
            else
                $handler = '';

            //Override the default Not Found Handler
            $container[$handler] = function ($container) use ($html, $isTwigPath, $errorNo) {
                return function ($request, $response) use ($container, $html, $isTwigPath, $errorNo) {
                    if($isTwigPath){
                        if($this->app_settings['middleware_twig']) {
                            if (file_exists('app/templates/'.$html))
                                $container['view']->render($response, $html, []);
                            else
                                \bmca\exception\Handler::unrecoverableException("Twig path for $errorNo page '$html' does not exist...");
                        }
                        else {
                            \bmca\exception\Handler::unrecoverableException('Twig path given, but twig is not enabled...');
                        }

                        return $response
                            ->withStatus((int)$errorNo)
                            ->withHeader('Content-Type', 'text/html; charset=UTF-8');
                    }
                    else {
                        return $container['response']
                            ->withStatus((int)$errorNo)
                            ->withHeader('Content-Type', 'text/html; charset=UTF-8')
                            ->write($html);
                    }
                };
            };

            $this->errorHTML[$errorNo] = [
                'html' => $html,
                'isTwigPath' => $isTwigPath
            ];
        }

        public function get404(&$response, $type='text/html; charset=UTF-8'){
            $this->getErrorPage($response, $type, '404');
        }

        public function set404($html, $isTwigPath=false){
            $this->setErrorPage($html, $isTwigPath, '404');
        }

        public function get500(&$response, $type='text/html; charset=UTF-8'){
            $this->getErrorPage($response, $type, '500');
        }

        public function set500($html, $isTwigPath=false){
            $this->setErrorPage($html, $isTwigPath, '500');
        }

        public function addTwigGlobals(array $globals = array()){
            $container = $this->getContainer();

            if(!isset($container['view'])) {
                \bmca\exception\Handler::unrecoverableException('Cannot assign twig globals when twig is not enabled...');
            }
            else {
                foreach ($globals as $key => $val)
                    $container['view']->getEnvironment()->addGlobal($key, $val);
            }
        }

        // TODO: rewrite this as a regex route for improved efficiency? (only skipping the rglob by doing so)
        public function autoBuildTemplateRoutes($rootdir, $routePrefix = '')
        {
            $container = $this->getContainer();
            if(isset($container['view'])) {
                if (strlen($rootdir) < 2)
                    $rootdir = '';
                else if ($rootdir[strlen($rootdir) - 1] == '/')
                    $rootdir = substr($rootdir, strlen($rootdir) - 2);

                $paths = \bmca\snippet\FilePaths::rglob('app/templates/' . $rootdir, true);
                foreach ($paths as $path) {
                    $prefixLength = strlen('app/templates/');
                    $path = substr($path, $prefixLength, strpos($path, '.') - $prefixLength);
                    $this->get($routePrefix . '/' . $path, function ($request, $response, $args) use ($path) {
                        $templateLocation = $path . '.html.twig';
                        // ensure the route exists
                        if (file_exists('app/templates/' . $templateLocation))
                            $this->view->render($response, $templateLocation, []);
                        else // route does not exist, give 404 error
                            $response->withStatus(404);
                    });
                }
            }
            else{
                \bmca\exception\Handler::unrecoverableException('Cannot auto build twig templates when twig is not enabled...');
            }
        }

        /********************************************************************************
         * HTTP Cookies
         *******************************************************************************/

        /*
        public function setCookie($name, $value, $time = null, $path = null, $domain = null, $secure = null, $httponly = null)
        {
            // If cookies are to be encrypted
            if($this->app_settings['cookies_encrypt'])
                \bmca\snippet\Cookie::setEncryptedCookie($name, $value, $time, $path, $domain, $secure, $httponly);
            // Unencrypted cookies
            else
                \bmca\snippet\Cookie::setCookie($name, $value, $time, $path, $domain, $secure, $httponly);
        }

        public function editCookie($name, $value, $time = null, $path = null, $domain = null, $secure = null, $httponly = null){
            \bmca\snippet\Cookie::editCookie($name, $value, $time, $path, $domain, $secure, $httponly);
        }

        public function getCookie($name)
        {
            if($this->app_settings['cookies_encrypt'])
                \bmca\snippet\Cookie::getCookie($name);
            else
                \bmca\snippet\Cookie::getEncryptedCookie($name, $this->app_settings['cookies_key']);
        }

        public function deleteCookie($name)
        {
            \bmca\snippet\Cookie::deleteCookie($name);
        }
        */

        public function initReCaptcha($sitekey, $secret){
            \bmca\form\reCaptcha::init($sitekey, $secret);

            if($this->app_settings['middleware_twig'])
                $this->addTwigGlobals(['_reCaptcha' => \bmca\form\reCaptcha::getHTML()]);
        }

        public function reCaptchaHTML($count=1, $lightTheme=true){
            return \bmca\form\reCaptcha::getHTML($count, $lightTheme);
        }

        public function reCaptchaCheckResponse(array & $responseData=array(), $nativePOST=false){
            // TODO: If Authentication / Security logging is enabled, log the result

            \bmca\form\reCaptcha::checkResponse($responseData, $nativePOST);
        }

        public function reCaptchaGetErrorCodes(){
            return \bmca\form\reCaptcha::getErrorCodes();
        }
    }
    
}