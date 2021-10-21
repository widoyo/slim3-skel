<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class ApiAction
{
    private $view;
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function index($request, $response, $args)
    {
        return $response->withJson(array('home' => 'page'));
    }

    public function show($request, $response, $args)
    {
        return $response->withJson(array('sn' => $args['sn']));
    }

}