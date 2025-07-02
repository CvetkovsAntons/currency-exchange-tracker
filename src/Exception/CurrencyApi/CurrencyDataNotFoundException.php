<?php

namespace App\Exception\CurrencyApi;

use App\Exception\AbstractCustomException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CurrencyDataNotFoundException extends AbstractCustomException
{
    public function __construct(string $currencyCode, ?Throwable $previous = null)
    {
        $message = sprintf("Couldn't get data for data for %s currency from external API", $currencyCode);

        parent::__construct($message);
    }

}
