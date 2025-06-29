<?php

namespace App\Tests\Utils\Factory;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRateHistory;
use DateTimeInterface;

class ExchangeRateHistoryTestFactory
{
    public static function create(
        CurrencyPair      $currencyPair,
        string            $rate,
        DateTimeInterface $createdAt
    ): ExchangeRateHistory
    {
        return new ExchangeRateHistory()
            ->setCurrencyPair($currencyPair)
            ->setRate($rate)
            ->setCreatedAt($createdAt);
    }

}
