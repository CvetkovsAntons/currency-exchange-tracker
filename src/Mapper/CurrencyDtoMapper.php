<?php

namespace App\Mapper;

use App\Dto\Currency as Dto;
use App\Entity\Currency as Entity;

class CurrencyDtoMapper
{
    public function mapFromEntity(Entity $currency): Dto
    {
        $dto = new Dto();

        $dto->code = $currency->getCode();
        $dto->name = $currency->getName();
        $dto->name_plural = $currency->getNamePlural();
        $dto->symbol = $currency->getSymbol();
        $dto->symbol_native = $currency->getSymbolNative();
        $dto->decimal_digits = $currency->getDecimalDigits();
        $dto->rounding = $currency->getRounding();
        $dto->type = $currency->getType()->value;

        return $dto;
    }

}
