<?php

namespace App\Validation\Rules;

use App\Models\Member;
use Respect\Validation\Rules\AbstractRule;

class CompanyNumberSet extends AbstractRule
{
    protected $acn;
    protected $arbn;

    public function __construct($acn, $arbn)
    {
        $this->acn = $acn;
        $this->arbn = $arbn;
    }

    public function validate($input)
    {

        if(empty($input) && empty($this->acn) && empty($this->arbn)){
            return false;
        } else{
            return true;
        }

    }

}