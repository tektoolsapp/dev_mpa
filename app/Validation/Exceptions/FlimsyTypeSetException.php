<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class FlimsyTypeSetException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'A Order Type must be Selected'
        ]
    ];

}