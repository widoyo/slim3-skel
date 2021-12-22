<?php
// Routes
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function(App $app) {
    $app->get('/', function (Request $request, Response $response, array $args)
    {
        if (!$this->session->user)
        {
            return $response->withRedirect('/login', 302);
        }
        else if ($this->session->user['tenant'])
        {
            // User tenant: homepage tampil hujan hari ini, tma terakhir
            $sns = "";
            foreach ($this->session->user['sn'] as $s) {
                $sns .= "'" . $s . "',";
            }
            $sql = "SELECT to_timestamp(MAX(content->>'sampling')::bigint) as sampling, "
                . "content->>'device' AS device "
                . "FROM raw WHERE sn IN (".substr($sns, 0, strlen($sns) -1).") "
                . "GROUP BY content->>'device' "
                . "ORDER BY sampling DESC";
            //$this->logger->debug($sns);
//            $sns = implode(",", $this->session->user['sn']);
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM location");
            $stmt->execute();
            //$this->logger->debug('tenant_id' . $this->session->user['tenant']['nama']);
            return $this->view->render($response, 'home_tenant.html', ['logger' => $stmt->fetchall()]);
        }
        else
        {
            $sns = "";
            foreach ($this->session->user['sn'] as $s) {
                $sns .= "'" . $s . "',";
            }
            $sql = "SELECT to_timestamp(MAX(content->>'sampling')::bigint) as sampling, "
                . "content->>'device' AS device "
                . "FROM raw WHERE sn IN (".substr($sns, 0, strlen($sns) -1).") "
                . "GROUP BY content->>'device' "
                . "ORDER BY sampling DESC";
            //$this->logger->debug($sql);
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $this->view->render($response, 'home_owner.html', ['logger' => $stmt->fetchall()]);
        }
        
    })->setName('homepage');
    
    $app->group('/api', function ($app) {
        $app->get('', App\Action\ApiAction::class . ':index');
        $app->get('/logger/{sn}', App\Action\ApiAction::class . ':show');
    });
    
    $app->group('/logger', function ($app) {
        $app->get('', App\Action\DeviceAction::class . ':index');
        $app->get('/add', App\Action\DeviceAction::class . ':add');
        $app->post('/add', App\Action\DeviceAction::class . ':add');
        $app->get('/{sn}', App\Action\DeviceAction::class . ':show');
    });
    
    $app->group('/das', function ($app) {
        $app->get('', App\Action\DasAction::class .':index');
    });

    $app->group('/pos', function ($app) {
        $app->get('', App\Action\PosAction::class .':index')->setName('pos_index');
        $app->get('/{id}', App\Action\PosAction::class . ':show')->setName('pos_show');
    });

    $app->group('/tenant', function ($app) {
        $app->get('', App\Action\TenantAction::class . ':index');
    });
    $app->get('/logout', function (Request $request, Response $response, array $args) {
        unset($this->session->user);
        return $response->withRedirect('/login', 302);
    })->setName('logout');
    
    $app->get('/login', function (Request $request, Response $response, array $args) {
        $next = $request->getQueryParam('next', '');
        return $this->view->render($response, 'login.html', ['next' => $next]);
    })->setName('login');
    
    $app->post('/login', function (Request $request, Response $response, array $args){
        $username = $request->getParsedBodyParam('username');
        $password = $request->getParsedBodyParam('password');
        $next = $request->getParsedBodyParam('next');
        $next = in_array($next, array('', '/login', '/logout')) ? '/' : $next; 
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username=:username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        $this->logger->debug('$next = ' . $next);
        if ($user && password_verify($password, $user['password'])) {
            date_default_timezone_set($user['tz']);
            $tenant = false;
            $sn = array();
            if ($user['tenant_id'])
            {
                $stmt = $this->db->prepare("SELECT id,nama,slug FROM tenant WHERE id=:id");
                $stmt->execute([":id" => $user['tenant_id']]);
                $tenant = $stmt->fetch();

                $stmt = $this->db->prepare("SELECT sn FROM logger WHERE tenant_id=:id");
                $stmt->execute([":id" => $user['tenant_id']]);
                $sn = array();
                foreach($stmt->fetchall() as $s)
                {
                    array_push($sn, $s['sn']);
                }
            } else {
    
                $sql = "SELECT sn FROM logger";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $sn = array();
                foreach($stmt->fetchall() as $s) 
                {
                    array_push($sn, $s['sn']);
                }
            }
            $this->session->user = array('username' => $username, 
                'tenant' => $tenant,
                'tz' => date_default_timezone_get(),
                'sn' => $sn
            );
            return $response->withRedirect($next, 302);
        }
        else
        {
            $this->flash->addMessage("errors", "Username atau Password keliru");
            return $this->view->render($response, 'login.html', ['next' => '/']);
        }
    
    });
};
