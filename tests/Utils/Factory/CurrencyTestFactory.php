<?php

namespace App\Tests\Utils\Factory;

use App\Dto\Currency as CurrencyDto;
use App\Entity\Currency as CurrencyEntity;
use App\Enum\CurrencyType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class CurrencyTestFactory
{
    public static function create(
        string $code = 'USD',
        string $name = 'US Dollar',
        string $namePlural = 'US dollars',
        string $symbol = '$',
        string $symbolNative = '$',
        int $decimalDigits = 2,
        string $rounding = '0',
        CurrencyType $type = CurrencyType::FIAT,
        ?Collection $fromPairs = null,
        ?Collection $toPairs = null
    ): CurrencyEntity
    {
        return new CurrencyEntity()
            ->setCode($code)
            ->setName($name)
            ->setNamePlural($namePlural)
            ->setSymbol($symbol)
            ->setSymbolNative($symbolNative)
            ->setDecimalDigits($decimalDigits)
            ->setRounding($rounding)
            ->setType($type)
            ->setFromPairs($fromPairs ?? new ArrayCollection())
            ->setToPairs($toPairs ?? new ArrayCollection());
    }

    public static function createDto(
        string $code = 'USD',
        string $name = 'US Dollar',
        string $namePlural = 'US Dollars',
        string $symbol = '$',
        string $symbolNative = '$',
        int $decimalDigits = 2,
        string $rounding = '0',
        CurrencyType $type = CurrencyType::FIAT,
    ): CurrencyDto
    {
        $currency = new CurrencyDto();
        $currency->code = $code;
        $currency->name = $name;
        $currency->name_plural = $namePlural;
        $currency->symbol = $symbol;
        $currency->symbol_native = $symbolNative;
        $currency->decimal_digits = $decimalDigits;
        $currency->rounding = $rounding;
        $currency->type = $type->value;

        return $currency;
    }

}
