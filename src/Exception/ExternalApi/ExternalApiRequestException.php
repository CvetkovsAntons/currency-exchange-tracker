<?php

namespace App\Exception\ExternalApi;

use App\Exception\AbstractCustomException;
use Throwable;

class ExternalApiRequestException extends AbstractCustomException
{
    public function __construct(Throwable $previous)
    {
        $message = sprintf("External API request failed: %s", $previous->getMessage());

        parent::__construct($message, $previous->getCode(), $previous);
    }

}
