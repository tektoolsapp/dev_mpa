<?php

namespace App\Validation\Rules;

use App\Models\Member;
use Respect\Validation\Rules\AbstractRule;

class FlimsyTypeSet extends AbstractRule
{
    protected $flimsy;
    protected $sewer_junction;
    protected $water_main;

    public function __construct($flimsy, $sewer_junction, $water_main)
    {
        $this->flimsy = $flimsy;
        $this->sewer_junction = $sewer_junction;
        $this->water_main = $water_main;
    }

    public function validate($input)
    {
        if(empty($this->flimsy) && empty($this->sewer_junction) && empty($this->water_main)){
            return false;
        } else{
            return true;
        }

    }

}