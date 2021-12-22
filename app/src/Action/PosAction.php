<?php
namespace App\Action;

use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class PosAction
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
        $sql = "SELECT * FROM location WHERE tenant_id=:tenant_id ORDER BY nama";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":tenant_id" => $tenant_id]);
        return $stmt->fetchall();
    }

    public function index($request, $response, $args)
    {
        
        return $this->view->render($response, 'pos/index.html', ['poses' => $this->_all()]);
    }

    public function show($request, $response, $args)
    {
        $sql = "SELECT * FROM location WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":id" => $args['id']]);
        return $this->view->render($response, 'pos/show.html', ['id' => $args['id']]);
    }

}