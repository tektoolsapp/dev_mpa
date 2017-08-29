<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class NotSelected extends AbstractRule
{
    public function validate($input)
    {
        if($input == 'N'){
            return false;
        } else {
            return true;
        }
    }

}