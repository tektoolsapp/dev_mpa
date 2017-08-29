<?php

namespace App\Controllers\Settings;

use App\Models\MpaUser;
//use App\Models\MemberTypes;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;

class SettingsController extends Controller

{

    public function index($request, $response)

    {

        $this->logger->addInfo("Settings");

        $allusers = MpaUser::getUsers();

        //var_dump($allusers);

        //die();

        return $this->view->render($response, 'settings/settings.twig', [

            'js_script' => 'test',
            'users' => $allusers

        ]);

    }

    public function newUser($request, $response)
    {

        //var_dump($this->csrf->getTokenNameKey());

        //var_dump("OLD");

        return $this->view->render($response, 'settings/new.user.twig');

        //$allusers = MpaUser::getUsers();

        //var_dump($allusers);

        //die();

        /*
        return $this->view->render($response, 'settings/new.user.twig', [

            'users' => $allusers

        ]);
        */



    }

    public function postNewUser($request, $response)
    {

        //var_dump($request->getParams());

        //var_dump($request->getParsedBody());

        //die();

        $validation = $this->validator->validate($request, [
            'firstname' => v::notEmpty()->setName('Firstname'),
            'surname' => v::notEmpty()->setName('Surname'),
            'position' => v::notEmpty()->setName('Position'),
            'email' => v::notEmpty()->setName('Email'),
            'phone' => v::notEmpty()->setName('Phone'),
            'mobile' => v::notEmpty()->setName('Mobile'),
            'username' => v::noWhitespace()->notEmpty()->setName('Username'),
            'password' => v::noWhitespace()->notEmpty()->setName('Password'),
            'access' =>v::notEmpty()->NotSelected()->setName('Access Level'),
            'status' =>v::notEmpty()->NotSelected()->setName('User Status'),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('new.user'));
        }


        $user = MpaUser::create([
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

        //$this->auth->attempt($user->email, $request->getParam('password'));

        //var_dump($user);

        //die();

        //$this->flash->addMessage('info', 'You have been signed up!');

        //$this->auth->attempt($user->email, $request->getParam('password'));

        $this->flash->addMessage('info', 'New User has been setup!');

        return $response->withRedirect($this->router->pathFor('settings.main'));

    }

}