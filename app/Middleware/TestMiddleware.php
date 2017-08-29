<?php

namespace App\Middleware;

use Slim\Views\Twig;
use Slim\Router;
use Slim\Flash\Messages as Flash;
use App\Models\Invoices;

class TestMiddleware extends Middleware
{
    protected $invoices;
    protected $use_var;
    protected $view;
    protected $router;
    protected $flash;

    public function __construct(Twig $view, Router $router, Flash $flash, Invoices $invoices)
    {
        //$this->use_var = $use_var;
        $this->invoices = $invoices;
        $this->view = $view;
        $this->router = $router;
        $this->flash = $flash;
    }

    public function __invoke($request, $response, $next)
    {

        $_SESSION['test_var'] = 'before changed';

        $response->getBody()->write('BEFORE '.$_SESSION['test_var']);

        $_SESSION['test_var'] = 'changed';

        $response = $next($request, $response);
        $this->flash->addMessage('invoices', "No C Invoices for the selected date range");
        $response->getBody()->write('AFTER '.$_SESSION['test_var']);


        //$my_invoices = $this->invoices->all();

        //dump($my_invoices);

        //$invoices = $my_invoices;



        /*
        $size = $_SESSION['invoices_size'];

        $response->getBody()->write("A: ".$this->use_var);
        $response->getBody()->write($size);
        if($size < 1) {
            $this->flash->addMessage('invoices', "No Invoices for the selected date range");
        } else {
            $this->flash->addMessage('invoices', "Invoices exist");
        }

        */

        //$response = $next($request, $response);

        //$response = $response->withStatus(400);

        return $response;

    }

}