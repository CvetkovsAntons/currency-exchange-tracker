<?php

namespace App\Tests\Utils\Factory;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRateHistory;
use DateTimeImmutable;
use DateTimeInterface;

class ExchangeRateHistoryTestFactory
{
    public static function create(
        CurrencyPair       $pair,
        string             $rate,
        ?DateTimeInterface $createdAt = null,
    ): ExchangeRateHistory
    {
        return new ExchangeRateHistory()
            ->setCurrencyPair($pair)
            ->setRate($rate)
            ->setCreatedAt($createdAt ?? new DateTimeImmutable());
    }

}
