<?php

namespace App\Factory;

use App\Entity\Currency;
use App\Entity\CurrencyPair;

class CurrencyPairFactory
{
    public function make(Currency $from, Currency $to, bool $isTracked = true): CurrencyPair
    {
        return new CurrencyPair()
            ->setFromCurrency($from)
            ->setToCurrency($to)
            ->setIsTracked($isTracked);
    }

}
