<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityTypes extends Model

{

    protected $table = 'activity_types';

    public function getActivityTypes()
    {

        $activity_types = ActivityTypes::where('activity_type_status', '<>', 'X')->orderBy('display_order')->get();

        return $activity_types;

    }

}