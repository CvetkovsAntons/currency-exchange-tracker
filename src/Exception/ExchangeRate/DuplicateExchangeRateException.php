<?php

namespace App\Exception\ExchangeRate;

use App\Exception\AbstractCustomException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DuplicateExchangeRateException extends AbstractCustomException
{
    public function __construct(?string $fromCurrencyCode = null, ?string $toCurrencyCode = null, ?Throwable $previous = null)
    {
        $message = sprintf(
            "Exchange rate for currency pair %s-%s already exists",
            $fromCurrencyCode,
            $toCurrencyCode
        );

        parent::__construct($message, Response::HTTP_CONFLICT, $previous);
    }

}
