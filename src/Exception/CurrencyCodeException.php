<?php

namespace App\Exception;

use RuntimeException;
use Throwable;

class CurrencyCodeException extends RuntimeException
{
    private string $currencyCode {
        get {
            return $this->currencyCode;
        }
    }

    public function __construct(string $currencyCode, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->currencyCode = $currencyCode;

        if (empty($message)) {
            $message = sprintf('Currency code %s is invalid', $currencyCode);
        }

        parent::__construct($message, $code, $previous);
    }

}
