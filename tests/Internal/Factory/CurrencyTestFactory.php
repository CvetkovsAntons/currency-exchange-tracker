<?php

namespace App\Tests\Internal\Factory;

use App\Builder\CurrencyBuilder;
use App\Dto\Currency as Dto;
use App\Entity\Currency as Entity;
use App\Mapper\CurrencyDtoMapper;
use App\Tests\Internal\Exceptions\UnsupportedTestCurrencyCodeException;

class CurrencyTestFactory
{
    /**
     * @throws UnsupportedTestCurrencyCodeException
     */
    public static function makeEntity(string $code): Entity
    {
        $builder = match ($code) {
            'USD' => new CurrencyBuilder()
                ->withCode($code)
                ->withName('US Dollar')
                ->withNamePlural('US dollars')
                ->withSymbol('$')
                ->withSymbolNative('$'),
            'EUR' => new CurrencyBuilder()
                ->withCode($code)
                ->withName('Euro')
                ->withNamePlural('Euros')
                ->withSymbol('€')
                ->withSymbolNative('€'),
            'GBP' => new CurrencyBuilder()
                ->withCode($code)
                ->withName('British Pound Sterling')
                ->withNamePlural('British pounds sterling')
                ->withSymbol('£')
                ->withSymbolNative('£'),
            'JPY' => new CurrencyBuilder()
                ->withCode($code)
                ->withName('Japanese Yen')
                ->withNamePlural('Japanese yen')
                ->withSymbol('¥')
                ->withSymbolNative('￥'),
            default => throw new UnsupportedTestCurrencyCodeException($code)
        };

        return $builder->build();
    }

    /**
     * @throws UnsupportedTestCurrencyCodeException
     */
    public static function makeDto(string $code = 'USD'): Dto
    {
        $currency = self::makeEntity($code);

        return new CurrencyDtoMapper()->mapFromEntity($currency);
    }

}
