<?php

namespace App\Exception;

use Throwable;

class CurrencyCodeException extends AbstractCustomException
{
    public function __construct(string $currencyCode, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        if (empty($message)) {
            $message = sprintf('Currency code %s is invalid', $currencyCode);
        }
        parent::__construct($message, $code, $previous);
    }

}
