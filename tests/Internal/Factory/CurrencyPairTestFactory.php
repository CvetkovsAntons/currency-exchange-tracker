<?php

namespace App\Tests\Internal\Factory;

use App\Entity\Currency;
use App\Entity\CurrencyPair;

class CurrencyPairTestFactory
{
    public static function create(Currency $from, Currency $to): CurrencyPair
    {
        return new CurrencyPair()
            ->setFromCurrency($from)
            ->setToCurrency($to);
    }

}
