<?php

namespace App\Validation\Forms;

use Respect\Validation\Validator as v;

class GetInvoicesForm
{
    public static function rules()
    {
        return [
            'from_date' => v::notEmpty()->setName('From Date'),
            'to_date' => v::notEmpty()->setName('To Date'),
        ];
    }
}