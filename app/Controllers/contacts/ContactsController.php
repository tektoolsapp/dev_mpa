<?php

namespace App\Controllers\Contacts;

use Slim\Router;
use Slim\Views\Twig;
use App\Models\Contacts;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Flash\Messages as Flash;
use App\Validation\Contracts\ValidatorInterface;
//use App\Validation\Forms\UserForm;


class ContactsController
{

    protected $router;
    protected $validator;
    protected $flash;
    protected $guard;

    public function __construct(Router $router, ValidatorInterface $validator, Flash $flash )
    {
        $this->router = $router;
        $this->validator = $validator;
        $this->flash = $flash;

    }

    public function index(Request $request, Response $response, Twig $view, Contacts $contacts)
    {
        $this->flash->getMessages();

        $contacts = $contacts->get();

        return $view->render($response, 'contacts/all.contacts.twig', [

            'contacts' => $contacts,
            'js_script' => 'contacts',
            //'contacts_display_status' => $args['status'],
            //'contacts_display_name' => $display_name

        ]);
    }

}