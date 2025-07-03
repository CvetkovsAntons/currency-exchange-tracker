<?php

namespace App\Exception\Currency;

use App\Exception\AbstractCustomException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InvalidCurrencyCodeException extends AbstractCustomException
{
    public function __construct(string $currencyCode, ?Throwable $previous = null)
    {
        $message = sprintf("Currency code %s isn't valid", $currencyCode);

        parent::__construct($message, Response::HTTP_BAD_REQUEST, $previous);
    }

}
