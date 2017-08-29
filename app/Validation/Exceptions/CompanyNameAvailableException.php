<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class CompanyNameAvailableException extends ValidationException

{

    public static $defaultTemplates = [

        self::MODE_DEFAULT => [

            self::STANDARD => 'Company Name entered has already been used.',

        ]

    ];

}