<?php

namespace App\Middleware;

class AuthMiddleware extends Middleware

{

    public function __invoke($request, $response, $next)
    {

        if(!$this->container->auth->check()){

            $this->container->flash->addMessage('error', 'Must be signed in!');

            //return $response->withRedirect($this->router->pathFor('auth.signin'));
            return $response->withRedirect($this->container->router->pathFor('auth.signin'));


        }

        $response = $next($request, $response);

        return $response;

    }

}


