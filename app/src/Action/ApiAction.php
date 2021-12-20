<?php
namespace App\Action;

use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use PDO;


final class ApiAction
{
    private $view;
    private $logger;
    private $db;
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $this->container['db'];
        $this->logger = $this->container['logger'];
    }

    public function index($request, $response, $args)
    {
        if ($this->container->session['user']['username']) {
            $sql = "SELECT WHERE sn IN ()" . $this->container->session['user']['username'];
        } else {
            $sql = "Kosong";
        }
        
        return $response->withJson(array('home' => $sql));
    }

    public function show($request, $response, $args)
    {
        return $response->withJson(array('sn' => $args['sn']));
    }

}