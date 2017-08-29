<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberTypes extends Model

{

    protected $table = 'member_types';

    public function getMemberTypes()
    {

        //dump($this);

        //die();

        $member_types = MemberTypes::where('member_type_status', '<>', 'X')->orderBy('display_order')->get();

        //return $this::all()->where('member_type_status', '<>', 'X')->orderBy('display_order')->get();

        return $member_types;

    }

}