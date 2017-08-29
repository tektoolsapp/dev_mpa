<?php

namespace App\Controllers\Settings;

use Slim\Router;
use Slim\Views\Twig;
//use App\Models\Members;
//use App\Models\Contacts;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Flash\Messages as Flash;
use App\Validation\Contracts\ValidatorInterface;

class SettingsController
{

    protected $router;
    protected $validator;
    protected $flash;

    public function __construct(Router $router, ValidatorInterface $validator, Flash $flash)
    {
        $this->router = $router;
        $this->validator = $validator;
        $this->flash = $flash;
    }

    public function index(Request $request, Response $response, Twig $view)
    {
        return $view->render($response, 'settings/settings.twig', [

            'js_script' => 'test'

        ]);
    }

}