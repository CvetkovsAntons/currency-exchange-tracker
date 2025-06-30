<?php

namespace App\Tests\Internal\Factory;

use App\Dto\ExchangeRateRequest;

class RequestTestFactory
{
    public static function valid(
        string $from = 'USD',
        string $to = 'EUR',
        ?string $datetime = '2024-01-01T00:00:00Z'
    ): ExchangeRateRequest
    {
        $request = new ExchangeRateRequest();
        $request->from = $from;
        $request->to = $to;
        $request->datetime = $datetime;

        return $request;
    }

    public static function invalidDatetime(): ExchangeRateRequest
    {
        $request = new ExchangeRateRequest();
        $request->from = 'USD';
        $request->to = 'EUR';
        $request->datetime = 'not-a-date';

        return $request;
    }

}
