<?php

namespace App\Exception\DateTime;

use App\Exception\AbstractCustomException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DateTimeInvalidFormatException extends AbstractCustomException
{
    public function __construct(string $format = 'Y-m-d H:i:s', ?Throwable $previous = null)
    {
        $format = trim($format);

        if (empty($format)) {
            $format = 'Y-m-d H:i:s';
        }

        $message = sprintf("Datetime format is invalid. Valid format: %s", $format);

        parent::__construct($message, Response::HTTP_BAD_REQUEST, $previous);
    }

}
