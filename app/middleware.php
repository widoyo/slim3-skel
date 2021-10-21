<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

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
