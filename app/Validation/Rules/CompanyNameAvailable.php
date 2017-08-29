<?php

namespace App\Validation\Rules;

use App\Models\Member;

use Respect\Validation\Rules\AbstractRule;

class CompanyNameAvailable extends AbstractRule

{

    public function validate($input)
    {

        $stored_company_name = $_SESSION['stored_company_name'];
        $check_name = Member::where('company_name' , $input)->count();

        if($check_name == 0 || $input === $stored_company_name){

            return true;

        } else {

            return false;
        }

    }

}