<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class CheckMailingAddressException extends ValidationException

{

    public static $defaultTemplates = [

        self::MODE_DEFAULT => [

            self::STANDARD => '{{name}} must be entered'

        ]

    ];

}