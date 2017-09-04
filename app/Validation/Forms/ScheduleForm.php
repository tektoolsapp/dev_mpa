<?php

namespace App\Validation\Forms;

use Respect\Validation\Validator as v;

class ScheduleForm
{
    public static function rules()
    {
        return [
            'event_title' => v::notEmpty()->setName('Title'),
            'event_description' => v::notEmpty()->setName('Description'),
            'start_date' => v::notEmpty()->setName('Start Date'),
            'start_time' => v::notEmpty()->setName('Start Time'),
            'hours' => v::optional(v::intVal()->max(12)->length(0, 2))->TimeNotZero($_SESSION['duration'])->setName('Hours'),
            'mins' => v::optional(v::intVal()->max(59)->length(0, 2))->TimeNotZero($_SESSION['duration'])->setName('Minutes'),
            'resource' => v::notEmpty()->NotSelected()->setName('Employee')
        ];
    }
}