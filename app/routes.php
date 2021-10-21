<?php
// Routes
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/', App\Action\HomeAction::class)
    ->setName('homepage');

$app->group('/api', function ($app) {
    $app->get('/logger', App\Action\ApiAction::class . ':index');
    $app->get('/logger/{sn}', App\Action\ApiAction::class . ':show');
});
    
$app->group('/logger', function ($app) {
    $app->get('', App\Action\DeviceAction::class . ':index');
    $app->get('/{sn}', App\Action\DeviceAction::class . ':show');
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
    $next = $request->getParsedBodyParam('next');
    $next = in_array($next, array('', '/login', '/logout')) ? '/' : $next; 
    $stmt = $this->db->prepare("SELECT * FROM users WHERE username=:username");
    $stmt->execute([':username' => $username]);
    $this->logger->debug('$next = ' . $next);
    if ($username !== '') {
        if ($username == 'demo')
        {
            date_default_timezone_set("Africa/Accra");
        }
        else
        {
            date_default_timezone_set('Asia/Jayapura');
        }
        $this->session->user = array('username' => $username, 'tz' => date_default_timezone_get());
        return $response->withRedirect($next, 302);
    }
    else
    {
        return $this->view->render($response, 'login.html', ['error' => 'user keliru', 'next' => '/']);
    }

});