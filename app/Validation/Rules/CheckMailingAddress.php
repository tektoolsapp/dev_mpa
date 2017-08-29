<?php

namespace App\Validation\Rules;

use App\Models\Member;
use Respect\Validation\Rules\AbstractRule;

class CheckMailingAddress extends AbstractRule
{

    protected $checked;

    public function __construct($checked)
    {

        $this->checked = $checked;

    }

    public function validate($input)
    {

        if($this->checked == 'Y' && empty($input)){
            return false;
        } else{
            return true;
        }

        //dump('VALIDATE');

    }

}