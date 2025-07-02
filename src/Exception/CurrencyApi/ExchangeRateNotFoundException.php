<?php

namespace App\Exception\CurrencyApi;

use App\Exception\AbstractCustomException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ExchangeRateNotFoundException extends AbstractCustomException
{
    public function __construct(string $fromCurrencyCode, string $toCurrencyCode, ?Throwable $previous = null)
    {
        $message = sprintf(
            "Couldn't get exchange rate for %s-%s currency pair from external currency API",
            $fromCurrencyCode,
            $toCurrencyCode
        );

        parent::__construct($message, Response::HTTP_NOT_FOUND, $previous);
    }

}
