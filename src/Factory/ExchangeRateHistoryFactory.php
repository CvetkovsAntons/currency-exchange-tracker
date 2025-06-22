<?php

namespace App\Factory;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Entity\ExchangeRateHistory;
use DateTimeInterface;

class ExchangeRateHistoryFactory
{
    public function create(CurrencyPair $currencyPair, string $rate, DateTimeInterface $createdAt): ExchangeRateHistory
    {
        return new ExchangeRateHistory()
            ->setCurrencyPair($currencyPair)
            ->setRate($rate)
            ->setCreatedAt($createdAt);
    }

    public function createFromRecord(ExchangeRate $exchangeRate): ExchangeRateHistory
    {
        return $this->create(
            $exchangeRate->getCurrencyPair(),
            $exchangeRate->getRate(),
            $exchangeRate->getUpdatedAt()
        );
    }

}
