<?php

namespace App\Exception\Currency;

use App\Exception\AbstractCustomException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DuplicateCurrencyCodeException extends AbstractCustomException
{
    public function __construct(string $currencyCode, ?Throwable $previous = null)
    {
        $message = sprintf("Currency with code %s already exists", $currencyCode);

        parent::__construct($message, Response::HTTP_CONFLICT, $previous);
    }

}
