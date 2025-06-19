<?php

namespace App\Factory;

use App\Entity\Currency;
use App\Entity\CurrencyPair;

class CurrencyFactory
{
    public static function create(Currency $from, Currency $to): CurrencyPair
    {
        $pair = new CurrencyPair();
        $pair->setFromCurrency($from);
        $pair->setToCurrency($to);
        return $pair;
    }

}
