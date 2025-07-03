<?php

namespace App\Exception\CurrencyApi;

use App\Exception\AbstractCustomException;
use Throwable;

class CurrencyApiResponseException extends AbstractCustomException
{
    public function __construct(string $message, int $code, ?Throwable $previous = null)
    {
        $message = sprintf("Unexpected response from currency API: %s", $message);

        parent::__construct($message, $code, $previous);
    }

}
