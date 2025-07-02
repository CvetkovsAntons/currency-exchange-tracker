<?php

namespace App\Exception\Currency;

use App\Exception\AbstractCustomException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CurrencyNotFoundException extends AbstractCustomException
{
    public function __construct(string $currencyCode, ?Throwable $previous = null)
    {
        $message = sprintf("Currency with code %s not found", $currencyCode);

        parent::__construct($message, Response::HTTP_NOT_FOUND, $previous);
    }

}
