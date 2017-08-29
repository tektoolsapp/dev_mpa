<?php

namespace App\Controllers;

use Slim\Router;
use Slim\Views\Twig;
use Slim\Flash\Messages as Flash;
use Slim\Csrf\Guard;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Interop\Container\ContainerInterface;
use App\Basket\Basket;

class HomeController
{
    public function index(Request $request, Response $response, Twig $view, Flash $flash, Basket $basket)
    {

        //unset($_SESSION);
        //dump($_SESSION);

        $api_version = getenv('API_VERSION');
        $api_key = getenv('API_KEY');
        $api_secret = getenv('API_SECRET');
        $redirect_url=getenv('REDIRECT_URL');
        $scope = getenv('SCOPE');
        $api_url = getenv('API_URL');
        $api_company_file = getenv('API_COMPANY_FILE');
        $api_coy_un = getenv('API_COY_UN');
        $api_coy_pw = getenv('API_COY_PW');

        $flash->getMessages();

        return $view->render($response, 'home.twig', [
            'integ_connect_script' => 'myob_connect',
        ]);
    }

}