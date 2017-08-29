<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class MemberNameAvailableException extends ValidationException

{

    public static $defaultTemplates = [

        self::MODE_DEFAULT => [

            self::STANDARD => 'Member Name entered has already been used.',

        ]

    ];

}