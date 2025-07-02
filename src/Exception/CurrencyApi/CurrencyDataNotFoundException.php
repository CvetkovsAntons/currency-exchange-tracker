<?php

namespace App\Exception\CurrencyApi;

use App\Exception\AbstractCustomException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CurrencyDataNotFoundException extends AbstractCustomException
{
    public function __construct(string $currencyCode, ?Throwable $previous = null)
    {
        $message = sprintf("Couldn't get data for %s currency from external currency API", $currencyCode);

        parent::__construct($message, Response::HTTP_NOT_FOUND, $previous);
    }

}
