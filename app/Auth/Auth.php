<?php

namespace App\Auth;

//use App\Models\User;
use App\Models\MpaUser;

class Auth
{

    public function user()
    {

        return mpauser::find($_SESSION['user']);

    }

    public function hello()
    {

        return "Hello";

    }

    public function check()
    {

        return isset($_SESSION['user']);

    }

    public function attempt($username, $password)
    {

        //$username = 'charliebrown';

        $user = mpauser::where('username', $username)->first();


        //var_dump($password, $user->password);

        //die();


        //$hash = substr( $user->password, 0, 60 );


        if (!$user) {

            return false;

        }

        if (password_verify($password, $user->password)) {

            $_SESSION['user'] = $user->id;

            return true;

        }

        return false;

    }

    public function logout()
    {

        unset($_SESSION['user']);

    }

}