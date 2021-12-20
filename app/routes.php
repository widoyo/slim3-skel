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
            $sql = "SELECT sn FROM logger WHERE ";
            $this->logger->debug('tenant_id' . $this->session->user['tenant']['nama']);
            return $this->view->render($response, 'home_tenant.html');
        }
        else
        {
            return $this->view->render($response, 'home_owner.html');
        }
        
    })->setName('homepage');
    
    $app->group('/api', function ($app) {
        $app->get('', App\Action\ApiAction::class . ':index');
        $app->get('/logger/{sn}', App\Action\ApiAction::class . ':show');
    });
    
    $app->group('/logger', function ($app) {
        $app->get('', App\Action\DeviceAction::class . ':index');
        $app->get('/{sn}', App\Action\DeviceAction::class . ':show');
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
                $stmt = $this->db->prepare("SELECT sn FROM logger");
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
            return $this->view->render($response, 'login.html', ['error' => 'user keliru', 'next' => '/']);
        }
    
    });
};
