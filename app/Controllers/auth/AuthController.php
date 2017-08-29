<?php

namespace App\Controllers\Auth;

use Slim\Router;
use Slim\Views\Twig;
use Slim\Flash\Messages as Flash;
use App\Auth\Auth;
//use App\Basket\Basket;
use App\Test\TestClass;
use App\Models\MpaUser;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{

    protected $router;
    protected $flash;
    protected $auth;

    public function __construct(Router $router, Flash $flash, Auth $auth, TestClass $test )
    {
        $this->router = $router;
        $this->flash = $flash;
        $this->auth = $auth;
        $this->test = $test;

        //$this->auth = $container->get('auth');

        //dump($test);

    }

    public function getSignIn(Request $request, Response $response, Twig $view)
    {

        return $view->render($response, 'auth/signin.twig');

    }

    public function getSignOut($request, $response)
    {

        $this->auth->logout();

        $this->flash->addMessage('info', 'You have been signed out!');

        return $response->withRedirect($this->router->pathFor('home'));

    }

    public function postSignIn(Request $request, Response $response)
    {

        $auth = $this->auth->attempt(

            $request->getParam('username'),
            $request->getParam('password')

        );

        //dump($auth);

        if(!$auth){

            $this->flash->addMessage('error', 'Could not sign you in with those details. Please try again.');

            return $response->withRedirect($this->router->pathFor('auth.signin'));

        }

        return $response->withRedirect($this->router->pathFor('home'));

    }

}