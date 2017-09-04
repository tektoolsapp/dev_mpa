<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Carbon\Carbon;

class Schedule extends Model
{
    protected $table = 'schedule';
    protected $fillable = [
        'title',
        'schedule_description',
        'start_date',
        'start_time',
        //'duration',
        'hours',
        'mins',
        'resource'
    ];

}