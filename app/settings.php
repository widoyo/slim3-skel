<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

return [
    'settings' => [
        // Slim Settings
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => true,

        // View settings
        'view' => [
            'template_path' => __DIR__ . '/templates',
            'twig' => [
                'cache' => __DIR__ . '/../cache/twig',
                'debug' => true,
                'auto_reload' => true,
            ],
        ],

        // monolog settings
        'logger' => [
            'name' => 'app',
            'path' => __DIR__ . '/../log/app.log',
        ],

        // Database
        'db' => [
			'connection' => $_ENV['DB_CONNECTION'],
			'host' => $_ENV['DB_HOST'],
			'port' => $_ENV['DB_PORT'],
			'database' => $_ENV['DB_DATABASE'],
			'username' => $_ENV['DB_USERNAME'],
			'password' => $_ENV['DB_PASSWORD'],
        ],    ],
];
