<?php

namespace App\Exception\CurrencyApi;

use App\Exception\AbstractCustomException;
use Throwable;

class ExchangeRateNotFoundException extends AbstractCustomException
{
    public function __construct(string $fromCurrencyCode, string $toCurrencyCode, ?Throwable $previous = null)
    {
        $message = sprintf(
            "Couldn't get exchange rate for %s-%s currency pair from external API",
            $fromCurrencyCode,
            $toCurrencyCode
        );

        parent::__construct($message);
    }

}
