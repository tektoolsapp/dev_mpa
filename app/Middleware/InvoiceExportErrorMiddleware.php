<?php

namespace App\Middleware;

use Carbon\Carbon;
use Slim\Views\Twig;
use Slim\Router;
use Slim\Flash\Messages as Flash;

class InvoiceExportErrorMiddleware extends Middleware
{
    protected $router;
    protected $flash;

    public function __construct(Twig $view, Router $router, Flash $flash)
    {
        $this->view = $view;
        $this->router = $router;
        $this->flash = $flash;
    }

    public function __invoke($request, $response, $next)
    {

        //if($request->getParam('update_dates') == 'Y'){


        $this->flash->addMessage('error', "No Invoices selected for Export");


        $response = $next($request, $response);

        return $response;

    }
}