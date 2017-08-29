<?php

namespace App\Members;

use App\Models\Member;

class Members
{

    public function attempt($email, $password)
    {

        /*
        $user = User::where('email', $email)->first();

        if (!$user) {

            return false;

        }

        if (password_verify($password, $user->password)) {

            $_SESSION['user'] = $user->id;

            return true;

        }

        return false;

        */

        return true;


    }


}