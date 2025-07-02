<?php

namespace App\Exception\CurrencyApi;

use App\Exception\AbstractCustomException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CurrencyApiUnavailableException extends AbstractCustomException
{
    public function __construct(
        string $message = 'Failed to connect to external currency API.',
        ?Throwable $previous = null
    )
    {
        parent::__construct($message, Response::HTTP_SERVICE_UNAVAILABLE, $previous);
    }

}
