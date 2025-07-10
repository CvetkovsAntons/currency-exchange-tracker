<?php

namespace App\Factory;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use DateTimeImmutable;

class ExchangeRateFactory
{
    public function make(CurrencyPair $pair, string $rate, ?DateTimeImmutable $datetime = null): ExchangeRate
    {
        $datetime ??= new DateTimeImmutable();

        return new ExchangeRate()
            ->setCurrencyPair($pair)
            ->setRate($rate)
            ->setCreatedAt($datetime)
            ->setUpdatedAt($datetime);
    }

}
