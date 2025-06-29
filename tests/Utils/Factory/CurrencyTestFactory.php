<?php

namespace App\Tests\Utils\Factory;

use App\Dto\Currency;

class CurrencyTestFactory
{
    public static function makeDto(string $code = 'USD'): Currency
    {
        $currency = new Currency();
        $currency->code = $code;
        $currency->name = 'US Dollar';
        $currency->symbol = '$';
        $currency->name_plural = 'US Dollars';
        $currency->decimal_digits = 2;
        $currency->symbol_native = '$';
        $currency->rounding = 0;
        $currency->type = 'fiat';

        return $currency;
    }

}
