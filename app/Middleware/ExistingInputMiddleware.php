<?php

namespace App\Middleware;

class ExistingInputMiddleware extends Middleware

{

    public function __invoke($request, $response, $next)
    {

        $this->container->view->getEnvironment()->addGlobal('old',$_SESSION['existing']);

        $_SESSION['old'] = $request->getParams();

        //var_dump($_SESSION['old']);

        $response = $next($request, $response);

        return $response;

    }

}