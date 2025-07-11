<?php

namespace App\Exception\ExternalApi;

use App\Exception\AbstractCustomException;
use Throwable;

class ExternalApiResponseException extends AbstractCustomException
{
    public function __construct(string $message, int $code, ?Throwable $previous = null)
    {
        $message = sprintf("Unexpected response from external API: %s", $message);

        parent::__construct($message, $code, $previous);
    }

}
