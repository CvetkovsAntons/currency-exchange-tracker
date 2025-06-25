<?php

namespace App\Exception;

use Exception;
use Throwable;

class DateTimeInvalidException extends Exception
{
    public function __construct(string $message = "", int $code = 400, ?Throwable $previous = null)
    {
        if (!$message) {
            $message = 'Invalid datetime format';
        }
        parent::__construct($message, $code, $previous);
    }

}
