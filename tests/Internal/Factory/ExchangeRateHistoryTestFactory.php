<?php

namespace App\Tests\Internal\Factory;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRateHistory;
use App\Factory\ExchangeRateHistoryFactory;
use DateTimeImmutable;
use DateTimeInterface;

class ExchangeRateHistoryTestFactory
{
    public static function make(
        CurrencyPair       $pair,
        string             $rate,
        ?DateTimeInterface $createdAt = null,
    ): ExchangeRateHistory
    {
        return new ExchangeRateHistoryFactory()->make($pair, $rate, $createdAt);
    }

}
