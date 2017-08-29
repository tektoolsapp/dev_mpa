<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class CheckLicenceTypeException extends ValidationException

{

    public static $defaultTemplates = [

        self::MODE_DEFAULT => [

            self::STANDARD => '{{name}} Number must be entered'

        ]

    ];

}