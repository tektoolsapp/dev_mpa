<?php

namespace App\Validation\Forms;

use Respect\Validation\Validator as v;

class EventForm
{
    public static function rules()
    {
        return [
            'event_name' => v::notEmpty()->setName('Event Name'),
            'event_description' => v::notEmpty()->setName('Event Description'),
            'event_type' => v::notEmpty()->NotSelected()->setName('Event Type'),
            'event_date' => v::notEmpty()->setName('Event Date'),
            'event_start' => v::notEmpty()->setName('Event Start'),
            'min_participants' => v::optional(v::alnum(' -')->length(0, 5))->setName('Min. Participants'),
            'max_participants' => v::optional(v::alnum(' -')->length(0, 5)->MaxGreaterThanMin($_SESSION['min_participants']))->setName('Max. Participants')
        ];
    }
}