<?php

namespace App\Controllers\Settings;

use Slim\Router;
use Slim\Views\Twig;
use App\Models\MpaUser;
use App\Models\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Flash\Messages as Flash;
use App\Validation\Contracts\ValidatorInterface;
use App\Validation\Forms\UserForm;

//use Slim\Csrf\Guard as Guard;

class ManageUsersController
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
        //$this->guard = $guard;

        //dump($guard);

        //die();
    }

    public function index(Request $request, Response $response, Twig $view, MpaUser $mpauser, UserStatus $user_status)
    {
        $this->flash->getMessages();

        $users = $mpauser->getUsers();

        return $view->render($response, 'settings/manage.users.main.twig', [

            'js_script' => 'manageusers',
            'users' => $users,

        ]);
    }

    public function newUser(Request $request, Response $response, Twig $view, MpaUser $mpauser)
    {
        return $view->render($response, 'settings/new.user.twig', [

            'mode' => 'add',
            'js_script' => 'manageusers'

        ]);
    }

    public function createUser(Request $request, Response $response, Twig $view, MpaUser $mpauser)
    {
        $validation = $this->validator->validate($request, UserForm::rules());

        if ($validation->fails()) {
            return $response->withRedirect($this->router->pathFor('manageusers.new'));
        }

        $user = $mpauser->firstorcreate([

            'firstname' => $request->getParam('firstname'),
            'surname' => $request->getParam('surname'),
            'position' => $request->getParam('position'),
            'email' => $request->getParam('email'),
            'phone' => $request->getParam('phone'),
            'mobile' => $request->getParam('mobile'),
            'username' => $request->getParam('username'),
            'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT),
            'access' => $request->getParam('access'),
            'status' => $request->getParam('status')

        ]);

        $this->flash->addMessage('info', 'New User has been setup!');

        return $response->withRedirect($this->router->pathFor('manageusers.index'));

    }

    public function editUser($id, Request $request, Response $response, Twig $view, MpaUser $mpauser)
    {
        $user = $mpauser->where('id', $id)->get()->first();

        return $view->render($response, 'settings/new.user.twig', [

            'mode' => 'edit',
            'js_script' => 'manageusers',
            'user' => $user

        ]);
    }

    public function updateUser($id, Request $request, Response $response, Twig $view, MpaUser $mpauser)
    {
        $validation = $this->validator->validate($request, UserForm::rules());

        if ($validation->fails()) {
            return $response->withRedirect($this->router->pathFor('manageusers.new'));
        }

        $mpauser->where('id', $id)->update([

            'firstname' => $request->getParam('firstname'),
            'surname' => $request->getParam('surname'),
            'position' => $request->getParam('position'),
            'email' => $request->getParam('email'),
            'phone' => $request->getParam('phone'),
            'mobile' => $request->getParam('mobile'),
            'username' => $request->getParam('username'),
            'password' => $request->getParam('password'),
            'access' => $request->getParam('access'),
            'status' => $request->getParam('status')

        ]);

        $this->flash->addMessage('info', 'User Details have been updated!');

        return $response->withRedirect($this->router->pathFor('manageusers.index'));

    }

}