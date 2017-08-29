<?php

namespace App\Middleware;

use Slim\Views\Twig;

class OldInputMiddleware extends Middleware
{
    protected $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function __invoke($request, $response, $next)
    {
        if(isset($_SESSION['old'])){
            $this->view->getEnvironment()->addGlobal('old',$_SESSION['old']);
            unset($_SESSION['old']);
        }

        //dump($this->view);
        //die();

        $_SESSION['old'] = $request->getParams();

        //$_SESSION['test'] = 'wuhu';

        $response = $next($request, $response);

        return $response;
    }
}