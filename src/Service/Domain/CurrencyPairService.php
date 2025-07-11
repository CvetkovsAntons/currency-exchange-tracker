<?php

namespace App\Service\Domain;

use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Exception\CurrencyPair\DuplicateCurrencyPairException;
use App\Factory\CurrencyPairFactory;
use App\Repository\CurrencyPairRepository;

readonly class CurrencyPairService
{
    public function __construct(
        private CurrencyPairFactory    $factory,
        private CurrencyPairRepository $repository,
    ) {}

    /**
     * @throws DuplicateCurrencyPairException
     */
    public function create(Currency $from, Currency $to): CurrencyPair
    {
        if ($this->exists($from, $to)) {
            throw new DuplicateCurrencyPairException($from->getCode(), $to->getCode());
        }

        $currencyPair = $this->factory->make($from, $to);

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

    public function track(CurrencyPair $pair): void
    {
        if ($pair->getIsTracked()) {
            return;
        }

        $pair->setIsTracked(true);
        $this->repository->save($pair);
    }

    public function untrack(CurrencyPair $pair): void
    {
        if (!$pair->getIsTracked()) {
            return;
        }

        $pair->setIsTracked(false);
        $this->repository->save($pair);
    }

}
