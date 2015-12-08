<?php
	
namespace bmca\framework{
    
    // Import Dependencies
	require 'vendor/autoload.php';
	
	class App extends \Slim\App
    {

        protected $logger;
        protected $settings;

        const MODE_DEV = 'development';
        const MODE_PRODUCTION = 'production';

        // TODO: use array merge in settings
        public function __construct(array $userSettings = array()){
            /*
            // Enable client side sessions
            $app->add(new \Slim\Middleware\SessionCookie(array(
                'expires' => '20 minutes',
                'path' => '/',
                'domain' => null,
                'secure' => false,
                'httponly' => false,
                'name' => 'slim_session',
                'secret' => 'CHANGE_ME',
                'cipher' => MCRYPT_RIJNDAEL_256,
                'cipher_mode' => MCRYPT_MODE_CBC
            )));
            */

            // Initialize the logger
            //$this->logger = new \bmca\log\Framework();
            $this->logger = new \bmca\log\Logger();

            // Initialize the error handler
            \bmca\exception\Handler::init('DEV', $this->logger);

            //if default settings need to be used
            $userSettings = array_merge(array(
                'mode' => self::MODE_DEV,
                'debug' => true,
                //  'log.level' => \Slim\Log::DEBUG,
                //  'log.enabled' => true,
                'cookies.encrypt' => true,
                'cookies.lifetime' => '20 minutes',
                'cookies.path' => '/',
                'cookies.domain' => 'domain.com',
                'cookies.secure' => true,
                'cookies.httponly' => false,
                // fix this below
                'cookies.secret_key' => 'secret',
                'cookies.cipher' => MCRYPT_RIJNDAEL_256,
                'cookies.cipher_mode' => MCRYPT_MODE_CBC,
                ), $userSettings
            );

            $this->settings = array_merge(array(
                'middleware_optimize' => false,
                'middleware_authentication' => false,
                'middleware_cache' => false,
                'middleware_client_session' => false
                ), $userSettings
            );

            //Pass along settings to slim
            parent::__construct($userSettings);

            $container = $this->getContainer();

            $container['view'] = function ($c) {
                $view = new \Slim\Views\Twig('app/templates/', [
                    'cache' => false//'app/cache'
                ]);
                $view->addExtension(new \Slim\Views\TwigExtension(
                    $c['router'],
                    $c['request']->getUri()
                ));

                return $view;
            };

            //Use Monolog to handle Slim logging
            //$this->log->setWriter(new \bmca\log\SlimLogger());

            /*
	           Initialize middleware
            */

            //Enable Optimizations
            if ($this->settings['middleware_optimize'])
                $this->add(new \bmca\middleware\Optimize());

            // Enable Client Session
            if ($this->settings['middleware_client_session'])
                $this->add(new \bmca\middleware\ClientSession());

            //Enable Session
            //client, splitclient, server, mixed

            //Enable Authentication
            if ($this->settings['middleware_authentication'])
                $this->add(new \bmca\middleware\Authentication());

            //Enable Cache
            if ($this->settings['middleware_cache'])
                $this->add(new \bmca\middleware\Cache());

            /*
	           Define routes
            */

            // Automatically load router files
            //$routers = \bmca\snippet::rglob('../routers/*.php');
            //foreach($routers as $router)
            // require $router;
        }

        public function addTwigGlobals(array $globals = array()){
            $container = $this->getContainer();
            foreach($globals as $key => $val)
                $container['view']->getEnvironment()->addGlobal($key, $val);

        }

        public function autoBuildTemplateRoutes($rootdir, $routePrefix = '')
        {
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
    }
    
}