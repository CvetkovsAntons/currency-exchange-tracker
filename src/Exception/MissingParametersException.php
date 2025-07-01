<?php

namespace App\Exception;

use Throwable;

class MissingParametersException extends AbstractCustomException
{
    public function __construct(array $parameters, ?int $code = 400, ?Throwable $previous = null)
    {
        $parameters = implode(', ', array_filter($parameters));

        $message ??= 'Missing mandatory parameters: ' . $parameters;

        parent::__construct($message, $code, $previous);
    }

}
