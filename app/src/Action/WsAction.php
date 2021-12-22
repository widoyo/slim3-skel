<?php
namespace App\Action;

use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class WsAction
{
    private $view;
    private $logger;
    private $db;
    private $c;

    public function __construct(ContainerInterface $container)
    {
        $this->view = $container['view'];
        $this->logger = $container['logger'];
        $this->db = $container['db'];
        $this->c = $container;
    }

    public function _all()
    {
        $tenant_id = $this->c['session']['user']['tenant']['id'];
        $sql = "SELECT * FROM logger WHERE tenant_id=:tenant_id ORDER BY sn";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":tenant_id" => $tenant_id]);
        return $stmt->fetchall();
    }

    public function index($request, $response, $args)
    {
        
        return $this->view->render($response, 'tenant/index.html', ['logger' => "Hello"]);
    }

    public function show($request, $response, $args)
    {
        if (! in_array($args['sn'], $this->c['session']['user']['sn'])) {
            return $response->withStatus(404);
        }
        return $this->view->render($response, 'tenant/show.html', ['sn' => $args['sn']]);
    }

}