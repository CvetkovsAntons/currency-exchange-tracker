<?php

namespace App\Exception\CurrencyPair;

use App\Exception\AbstractCustomException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CurrencyPairNotFoundException extends AbstractCustomException
{
    public function __construct(string $fromCurrencyCode, string $toCurrencyCode, ?Throwable $previous = null)
    {
        $message = sprintf("Currency pair %s-%s not found", $fromCurrencyCode, $toCurrencyCode);

        parent::__construct($message, Response::HTTP_NOT_FOUND, $previous);
    }

}
