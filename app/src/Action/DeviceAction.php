<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class DeviceAction
{
    private $view;
    private $logger;

    public function __construct(Twig $view, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->logger = $logger;
    }

    public function index($request, $response, $args)
    {
        return $this->view->render($response, 'device/index.html');
    }

    public function show($request, $response, $args)
    {
        return $this->view->render($response, 'device/show.html', ['sn' => $args['sn']]);
    }

}