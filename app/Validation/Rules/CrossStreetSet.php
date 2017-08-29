<?php

namespace App\Validation\Rules;

use App\Models\Member;
use Respect\Validation\Rules\AbstractRule;

class CrossStreetSet extends AbstractRule
{
    protected $sewer_junction;
    protected $water_main;

    public function __construct($sewer_junction, $water_main)
    {
        $this->sewer_junction = $sewer_junction;
        $this->water_main = $water_main;
    }

    public function validate($input)
    {
        if(empty($input) && (!empty($this->sewer_junction) || !empty($this->water_main))){
            return false;
        } else{
            return true;
        }

    }

}