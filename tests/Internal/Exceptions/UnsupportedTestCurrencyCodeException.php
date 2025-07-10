<?php

namespace App\Tests\Internal\Exceptions;

use Throwable;

class UnsupportedTestCurrencyCodeException extends \Exception
{
    public function __construct(string $currencyCode, int $code = 0, ?Throwable $previous = null)
    {
        $message = sprintf('Currency with code %s is not implemented yet', $currencyCode);
        parent::__construct($message, $code, $previous);
    }

}
