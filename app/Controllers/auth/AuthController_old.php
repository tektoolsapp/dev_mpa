<?php


namespace App\Controllers\Auth;

//use App\Models\User;
use App\Models\MpaUser;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;

//use Slim\Views\Twig as View;

class AuthController extends Controller

{

    public function getSignOut($request, $response)
    {

        $this->auth->logout();

        $this->flash->addMessage('info', 'You have been signed out!');

        return $response->withRedirect($this->router->pathFor('home'));

    }

    public function getSignIn($request, $response)
    {

        return $this->view->render($response, 'auth/signin.twig');

    }

    public function postSignIn($request, $response)
    {

        //var_dump($request->getParams());

        //die();

        $auth = $this->auth->attempt(

            $request->getParam('username'),
            $request->getParam('password')

        );

        if(!$auth){

            $this->flash->addMessage('error', 'Could not sign you in with those details. Please try again.');

            return $response->withRedirect($this->router->pathFor('auth.signin'));

        }

        return $response->withRedirect($this->router->pathFor('home'));

    }

    /*
    public function getSignUp($request, $response)
    {

        //var_dump($this->csrf->getTokenNameKey());

        var_dump("OLD");

        return $this->view->render($response, 'auth/signup.twig');

    }
    */

    /*
    public function postSignUp($request, $response)
    {

        //var_dump($request->getParams());

        //var_dump($request->getParsedBody());

        //die();

        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email()->emailAvailable(),
            'name' => v::notEmpty(),
            'password' => v::noWhitespace()->notEmpty()
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.signup'));
        }

        $user = User::create([
            'email' => $request->getParam('email'),
            'name' => $request->getParam('name'),
            'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT)
        ]);

        $this->auth->attempt($user->email, $request->getParam('password'));

        //var_dump($user);

        //die();

        //$this->flash->addMessage('info', 'You have been signed up!');

        //$this->auth->attempt($user->email, $request->getParam('password'));

        $this->flash->addMessage('info', 'You have been signed up!');

        return $response->withRedirect($this->router->pathFor('home'));

    }
    */

}