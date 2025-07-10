<?php

namespace App\Factory;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Entity\ExchangeRateHistory;
use DateTimeImmutable;
use DateTimeInterface;

class ExchangeRateHistoryFactory
{
    public function make(
        CurrencyPair $currencyPair,
        string $rate,
        ?DateTimeInterface $createdAt = null
    ): ExchangeRateHistory
    {
        return new ExchangeRateHistory()
            ->setCurrencyPair($currencyPair)
            ->setRate($rate)
            ->setCreatedAt($createdAt ?? new DateTimeImmutable());
    }

    public function makeFromExchangeRate(ExchangeRate $exchangeRate): ExchangeRateHistory
    {
        return $this->make(
            $exchangeRate->getCurrencyPair(),
            $exchangeRate->getRate(),
            $exchangeRate->getUpdatedAt()
        );
    }

}
