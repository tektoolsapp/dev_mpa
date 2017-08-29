<?php

namespace App\Middleware;

use Carbon\Carbon;
use Slim\Views\Twig;
use Slim\Router;
use Slim\Flash\Messages as Flash;

class InvoiceDateRangeMiddleware extends Middleware
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

        if($request->getParam('update_dates') == 'Y'){

            $_SESSION['update_dates'] = 'Y';

            //CHECK FOR A FROM & TO DATE
            $set_from_date = $request->getParam('from_date');
            $set_to_date = $request->getParam('to_date');

            $error_count = 0;

            if(!empty($set_from_date)) {
                //$start_date = Carbon::createFromFormat('d-m-Y', $set_from_date);
                $start = $set_from_date;
                $_SESSION['from_date'] = $start;
            } elseif(empty($set_from_date)) {
                $_SESSION['from_date'] = '';
                $error_count++;
                $this->flash->addMessage('error', "No From Date selected");
            }

            if(!empty($set_to_date)) {
                //$end_date = Carbon::createFromFormat('d-m-Y', $set_to_date);
                $end = $set_to_date;
                $_SESSION['to_date'] = $end;
            } elseif(empty($set_to_date)) {
                $_SESSION['to_date'] = '';
                $error_count++;
                $this->flash->addMessage('error', "No To Date selected");
            }

            if($error_count > 0) {
                return $response->withRedirect($this->router->pathFor('invoices.index'));
            }

        }

        $response = $next($request, $response);

        return $response;

    }
}