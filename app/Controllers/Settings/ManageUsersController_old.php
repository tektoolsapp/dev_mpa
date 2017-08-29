<?php

namespace App\Controllers\Settings;

use App\Models\MpaUser;
use App\Controllers\Controller;
use Respect\Validation\Rules\Length;
use Respect\Validation\Validator as v;
//use Illuminate\Pagination\LengthAwarePaginator;
//use Illuminate\Pagination\Paginator;

class ManageUsersController extends Controller

{

    public function index($request, $response)

    {

        $this->logger->addInfo("ManageUsers");

        $allusers = MpaUser::getUsers()->appends($request->getParams());

        for ($i = 0; $i <= sizeof($allusers); $i++) {

            if($allusers[$i]['status'] == 'A'){
                $allusers[$i]['status'] = 'Active';
            } else {
                $allusers[$i]['status'] = 'In-Active';
            }

        }

        return $this->view->render($response, 'settings/manage.users.main.twig', [

            'js_script' => 'manageusers',
            'users' => $allusers,
        ]);

    }

    public function newUser($request, $response)
    {

        return $this->view->render($response, 'settings/new.user.twig', [

            'mode' => 'add',
            'js_script' => 'manageusers'

        ]);

    }

    public function displayUser($request, $response, $args)
    {

        $id = $args['id'];

        $user = MpaUser::where('id', $id)->get()->first();

        return $this->view->render($response, 'settings/new.user.twig', [

            'mode' => 'edit',
            'js_script' => 'manageusers',
            'user' => $user

        ]);

    }

    public function editUser($request, $response, $args)
    {

        //var_dump($request->getParam('id'));

        //die();

        $id = $args['id'];

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
            return $response->withRedirect($this->router->pathFor('edit.user'));
        }

        MpaUser::where('id', $request->getParam('id'))
            ->update([

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

        $this->flash->addMessage('info', 'User details were successfully updated!');

        return $response->withRedirect($this->router->pathFor('manage.users.main'));

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

        $this->flash->addMessage('info', 'New User has been setup!');

        return $response->withRedirect($this->router->pathFor('manage.users.main'));

    }

}