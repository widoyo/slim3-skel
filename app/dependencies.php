<?php
// DIC configuration
use Slim\App;

return function(App $app) {
    $container = $app->getContainer();

    // -----------------------------------------------------------------------------
    // Service providers
    // -----------------------------------------------------------------------------
    
    $container['session'] = function ($c) {
        return new \SlimSession\Helper();
    };
    
    // Twig
    $container['view'] = function ($c) {
        $settings = $c->get('settings');
        $view = new Slim\Views\Twig($settings['view']['template_path'], $settings['view']['twig']);
    
        // Add extensions
        $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
    //    $view->addExtension(new Twig_Extension_Debug());
        $env = $view->getEnvironment();
        $env->addGlobal('user', $c->session->user);
    
        return $view;
    };
    
    // Flash messages
    $container['flash'] = function ($c) {
        return new Slim\Flash\Messages;
    };
    
    $flash = new Twig\TwigFunction('flash', function ($key) use ($container) {
        return $container->get('flash')->getMessage($key);
    });
    $container->get('view')->getEnvironment()->addFunction($flash);
    
    // -----------------------------------------------------------------------------
    // Service factories
    // -----------------------------------------------------------------------------
    
    // monolog
    $container['logger'] = function ($c) {
        $settings = $c->get('settings');
        $logger = new Monolog\Logger($settings['logger']['name']);
        $logger->pushProcessor(new Monolog\Processor\UidProcessor());
        $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['logger']['path'], Monolog\Logger::DEBUG));
        return $logger;
    };
    
    $container['db'] = function($c) {
        $settings = $c->get('settings')['db'];
        $connection = $settings['connection'];
        $host = $settings['host'];
        $port = $settings['port'];
        $database = $settings['database'];
        $username = $settings['username'];
        $password = $settings['password'];
    
        $dsn = "$connection:host=$host;port=$port;dbname=$database";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    
        try {
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    };
    
    $container['notFoundHandler'] = function ($c) {
        return function($request, $response) use ($c) {
            $new_response = $response->withStatus(404);
            return $c->view->render($new_response, '404.html');
        };
    };
    // -----------------------------------------------------------------------------
    // Action factories
    // -----------------------------------------------------------------------------
    
    $container[App\Action\HomeAction::class] = function ($c) {
        return new \App\Action\HomeAction($c);
    };
    
    $container[App\Action\DeviceAction::class] = function ($c) {
        return new \App\Action\DeviceAction($c);
    };
    
    $container[App\Action\ApiAction::class] = function ($c) {
        return new \App\Action\ApiAction($c);
    };

    $container[App\Action\TenantAction::class] = function ($c) {
        return new \App\Action\TenantAction($c);
    };

    $container[App\Action\PosAction::class] = function ($c) {
        return new \App\Action\PosAction($c);
    };

    $container[App\Action\DasAction::class] = function ($c) {
        return new \App\Action\DasAction($c);
    };
};
