<?php

namespace App\Exception\CurrencyApi;

use App\Exception\AbstractCustomException;
use Throwable;

class CurrencyApiRequestException extends AbstractCustomException
{
    public function __construct(Throwable $previous)
    {
        $message = sprintf("Currency API request failed: %s", $previous->getMessage());

        parent::__construct($message, $previous->getCode(), $previous);
    }

}
