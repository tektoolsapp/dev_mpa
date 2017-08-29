<?php

namespace App\Validation\Forms;

use Respect\Validation\Validator as v;

class UserForm
{
    public static function rules()
    {
        return [
            'firstname' => v::notEmpty()->setName('Firstname'),
            'surname' => v::notEmpty()->setName('Surname'),
            'position' => v::notEmpty()->setName('Position'),
            'email' => v::notEmpty()->setName('Email'),
            'phone' => v::notEmpty()->setName('Phone'),
            'mobile' => v::notEmpty()->setName('Mobile'),
            'username' => v::noWhitespace()->notEmpty()->setName('Username'),
            'password' => v::noWhitespace()->notEmpty()->setName('Password')
            //'access' =>v::notEmpty()->NotSelected()->setName('Access Level'),
            //'status' =>v::notEmpty()->NotSelected()->setName('User Status'),
        ];
    }
}