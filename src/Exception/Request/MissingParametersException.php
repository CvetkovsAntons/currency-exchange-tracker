<?php

namespace App\Exception\Request;

use App\Exception\AbstractCustomException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class MissingParametersException extends AbstractCustomException
{
    public function __construct(array $parameters, ?Throwable $previous = null)
    {
        $parameters = implode(', ', array_filter($parameters));

        $message = sprintf('Missing mandatory parameter(s): %s.', $parameters);

        parent::__construct($message, Response::HTTP_BAD_REQUEST, $previous);
    }

}
