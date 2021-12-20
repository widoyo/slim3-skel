<?php
// Application middleware
use Slim\App;
// e.g: $app->add(new \Slim\Csrf\Guard);

return function(App $app) {
    // Hoster only 
    $app->add(function ($request, $response, $next) {
        $uri = $request->getUri();
        $opened = array('/tenant', '/user');
        if (in_array($uri->getPath(), $opened)) {
            if (is_array($this->session->user['tenant'])) {
                $new_response = $response->withStatus(403);
                return $this->view->render($new_response, '403.html');
            }
        }
        return $next($request, $response);
    });

    $app->add(function ($request, $response, $next) {
        $uri = $request->getUri();
        $this->logger->debug($uri);
        if (!is_array($this->session->user)) {
            $opened = array('/login', '/');
            if (!in_array($uri->getPath(), $opened)) {
                $next = '';
                if ($uri->getPath() != "/logout") $next = "?next=".$uri->getPath(); 
                return $response->withRedirect('/login'.$next, 302);
            }
        }
        //date_default_timezone_set($this->session->user['tz']);
        $this->logger->debug('Default tz' . date_default_timezone_get());
        return $next($request, $response);
    });
};
