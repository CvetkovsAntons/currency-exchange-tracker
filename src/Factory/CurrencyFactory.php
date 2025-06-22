<?php

namespace App\Factory;

use App\Dto\Currency as CurrencyDto;
use App\Entity\Currency as CurrencyEntity;
use App\Enum\CurrencyType;
use Doctrine\Common\Collections\ArrayCollection;

class CurrencyFactory
{
    public function create(CurrencyDto $dto): CurrencyEntity
    {
        $collection = new ArrayCollection();

        return new CurrencyEntity()
            ->setCode($dto->code)
            ->setName($dto->name)
            ->setNamePlural($dto->name_plural)
            ->setSymbol($dto->symbol)
            ->setSymbolNative($dto->symbol_native)
            ->setDecimalDigits($dto->decimal_digits)
            ->setRounding($dto->rounding)
            ->setType(CurrencyType::from($dto->type))
            ->setFromPairs($collection)
            ->setToPairs($collection);
    }

}
