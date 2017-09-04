<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class TimeNotZero extends AbstractRule
{
    protected $time;

    public function __construct($time)
    {
        $this->time = $time;
    }

    public function validate($input)
    {
        //return false;

        if(empty($input) && (int)$this->time < 1){
            return false;
        } else{
            return true;
        }

    }

}