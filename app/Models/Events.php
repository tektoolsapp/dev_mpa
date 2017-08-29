<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Carbon\Carbon;

class Events extends Model
{
    protected $table = 'events';
    protected $fillable = [
        'event_name',
        'event_description',
        'event_type',
        'event_date',
        'event_start',
        'min_participants',
        'max_participants',
    ];

}