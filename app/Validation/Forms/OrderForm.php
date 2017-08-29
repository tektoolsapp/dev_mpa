<?php

namespace App\Validation\Forms;

use Respect\Validation\Validator as v;

class OrderForm
{
    public static function rules()
    {
        return [
            'email' => v::email()->setName('email'),
            'name' => v::alpha(' ')->setName('Name'),
            'address1' => v::alnum(' - ')->setName('Address 1'),
            'address2' => v::optional(v::alnum(' -'))->setName('Address 2'),
            'suburb' => v::alnum(' ')->setName('Suburb'),
            'postcode' => v::notEmpty()->setName('Post Code')
        ];
    }
}