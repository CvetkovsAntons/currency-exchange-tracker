<?php

namespace App\Factory;

use App\Entity\Currency;
use App\Entity\CurrencyPair;

class CurrencyPairFactory
{
    public static function create(Currency $from, Currency $to): CurrencyPair
    {
        return new CurrencyPair()
            ->setFromCurrency($from)
            ->setToCurrency($to);
    }

}
