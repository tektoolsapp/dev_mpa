<?php

/*
namespace App\Middleware;

class Middleware

{

    protected $container;

    public function __construct($container)
    {

        $this->container = $container;


    }

}
*/

namespace App\Middleware;

use Slim\Views\Twig as View;
use Slim\Csrf\Guard;

class Middleware
{
    protected $view;
    protected $csrf;

    public function __construct(View $view, Guard $csrf)
    {
        $this->view = $view;
        $this->csrf = $csrf;
    }
}