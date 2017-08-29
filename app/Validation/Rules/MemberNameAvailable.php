<?php

namespace App\Validation\Rules;

use App\Models\Member;

use Respect\Validation\Rules\AbstractRule;

class MemberNameAvailable extends AbstractRule

{

    public function validate($input)
    {

        $stored_name = $_SESSION['stored_member_name'];
        $check_name = Member::where('name' , $input)->count();

        if($check_name == 0 || $input === $stored_name){

            return true;

        } else {

            return false;
        }

    }

}