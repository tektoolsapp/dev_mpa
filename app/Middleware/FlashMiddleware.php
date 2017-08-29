<?php

namespace App\Middleware;

class FlashMiddleware extends Middleware

{

    public function __invoke($request, $response, $next)
    {

        $this->view->offsetSet("flash", $this->flash);

        $response = $next($request, $response);

        return $response;

    }

}


