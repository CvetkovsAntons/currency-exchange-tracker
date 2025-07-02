<?php

namespace App\Exception\ExchangeRate;

use App\Exception\AbstractCustomException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ExchangeRateNotFoundException extends AbstractCustomException
{
    public function __construct(string $fromCurrencyCode, string $toCurrencyCode, ?Throwable $previous = null)
    {
        $message = sprintf("Exchange rate for currency pair %s-%s not found", $fromCurrencyCode, $toCurrencyCode);

        parent::__construct($message, Response::HTTP_NOT_FOUND, $previous);
    }

}
