<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class CompanyNumberSetException extends ValidationException

{

    public static $defaultTemplates = [

        self::MODE_DEFAULT => [

            self::STANDARD => '',

        ]

    ];

}