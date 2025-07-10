<?php

namespace App\Factory;

use App\Builder\CurrencyBuilder;
use App\Dto\Currency as CurrencyDto;
use App\Entity\Currency as CurrencyEntity;
use App\Enum\CurrencyType;

class CurrencyFactory
{
    public function makeFromDto(CurrencyDto $dto): CurrencyEntity
    {
        return new CurrencyBuilder()
            ->withCode($dto->code)
            ->withName($dto->name)
            ->withNamePlural($dto->name_plural)
            ->withSymbol($dto->symbol)
            ->withSymbolNative($dto->symbol_native)
            ->withDecimalDigits($dto->decimal_digits)
            ->withRounding($dto->rounding)
            ->withType(CurrencyType::from($dto->type))
            ->build();
    }

}
