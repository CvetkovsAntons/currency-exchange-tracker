<?php

namespace App\Service\Domain;

use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Exception\CurrencyPairException;
use App\Factory\CurrencyPairFactory;
use App\Repository\CurrencyPairRepository;

readonly class CurrencyPairService
{
    public function __construct(
        private CurrencyPairFactory    $factory,
        private CurrencyPairRepository $repository,
    ) {}

    public function create(Currency $from, Currency $to): CurrencyPair
    {
        if ($this->exists($from, $to)) {
            throw new CurrencyPairException(sprintf(
                'Currency pair %s->%s already exists',
                $from->getCode(),
                $to->getCode()
            ));
        }

        $currencyPair = $this->factory->create($from, $to);

        $this->repository->save($currencyPair);

        return $currencyPair;
    }

    public function exists(Currency $from, Currency $to): bool
    {
        return $this->repository->exists($from, $to);
    }

    public function get(Currency $from, Currency $to): ?CurrencyPair
    {
        return $this->repository->findOneBy(['fromCurrency' => $from, 'toCurrency' => $to]);
    }

}
