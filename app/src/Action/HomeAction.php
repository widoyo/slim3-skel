<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Container\ContainerInterface;

final class HomeAction
{
    private $logger;
    private $db;
    private $session;

    public function __construct(ContainerInterface $container)
    {
        $this->db = $container['db'];
        $this->logger = $container['logger'];
        $this->session = $container['session'];
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $this->logger->info("Home page action dispatched");
        $this->logger->info(date_default_timezone_get());
        $sql = "SELECT COUNT(*) as count FROM raw";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetch();
        $this->view->render($response, 'home.html', ['count' => $count['count'], 'homeuser' => $this->session['user']['username']]);
        return $response;
    }
}
