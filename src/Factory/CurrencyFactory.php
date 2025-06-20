<?php

namespace App\Factory;

use App\Dto\Currency as CurrencyDto;
use App\Entity\Currency as CurrencyEntity;
use App\Enum\CurrencyType;

class CurrencyFactory
{
    public function create(CurrencyDto $dto): CurrencyEntity
    {
        return new CurrencyEntity(
            code: $dto->code,
            name: $dto->name,
            namePlural: $dto->name_plural,
            symbol: $dto->symbol,
            symbolNative: $dto->symbol_native,
            decimalDigits: $dto->decimal_digits,
            rounding: $dto->rounding,
            type: CurrencyType::from($dto->type),
        );
    }

}
