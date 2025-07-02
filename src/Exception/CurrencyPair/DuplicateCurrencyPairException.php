<?php

namespace App\Exception\CurrencyPair;

use App\Exception\AbstractCustomException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DuplicateCurrencyPairException extends AbstractCustomException
{
    public function __construct(?string $fromCurrencyCode = null, ?string $toCurrencyCode = null, ?Throwable $previous = null)
    {
        $message = sprintf("Currency code %s-%s already exists", $fromCurrencyCode, $toCurrencyCode);

        parent::__construct($message, Response::HTTP_CONFLICT, $previous);
    }

}
