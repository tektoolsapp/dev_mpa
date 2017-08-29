<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class MaxGreaterThanMin extends AbstractRule
{
    protected $min;

    public function __construct($min)
    {
        $this->min = $min;
    }

    public function validate($input)
    {

        //dump((int)$input, $this->min);

        if($this->min > 0 && (int)$input <= $this->min){
            return false;
        } else{
            return true;
        }
    }

}