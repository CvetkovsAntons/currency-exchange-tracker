<?php

namespace App\Tests\Internal\Factory;

use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Factory\CurrencyPairFactory;

class CurrencyPairTestFactory
{
    public static function make(Currency $from, Currency $to): CurrencyPair
    {
        return new CurrencyPairFactory()->make($from, $to);
    }

}
