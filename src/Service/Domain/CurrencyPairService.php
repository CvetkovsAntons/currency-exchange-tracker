<?php

namespace App\Service\Domain;

use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Exception\CurrencyCodeException;
use App\Exception\CurrencyPairException;
use App\Factory\CurrencyPairFactory;
use App\Repository\CurrencyPairRepository;

readonly class CurrencyPairService
{
    public function __construct(
        private CurrencyPairFactory    $factory,
        private CurrencyPairRepository $repository,
        private CurrencyService        $currencyService,
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

    public function createFromCodes(string $fromCurrencyCode, string $toCurrencyCode): CurrencyPair
    {
        [$from, $to] = $this->prepareCurrencies($fromCurrencyCode, $toCurrencyCode);

        return $this->create($from, $to);
    }

    public function exists(Currency $from, Currency $to): bool
    {
        return $this->repository->exists($from, $to);
    }

    public function existsByCodes(string $fromCurrencyCode, string $toCurrencyCode): bool
    {
        [$from, $to] = $this->prepareCurrencies($fromCurrencyCode, $toCurrencyCode);

        return $this->repository->exists($from, $to);
    }

    public function get(Currency $from, Currency $to): ?CurrencyPair
    {
        return $this->repository->get($from, $to);
    }

    public function getByCodes(string $fromCurrencyCode, string $toCurrencyCode): ?CurrencyPair
    {
        [$from, $to] = $this->prepareCurrencies($fromCurrencyCode, $toCurrencyCode);

        return $this->get($from, $to);
    }

    /**
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     * @return Currency[]
     */
    private function prepareCurrencies(string $fromCurrencyCode, string $toCurrencyCode): array
    {
        $fromCurrency = $this->currencyService->getByCode($fromCurrencyCode);
        if (is_null($fromCurrency)) {
            throw new CurrencyCodeException($fromCurrencyCode);
        }

        $toCurrency = $this->currencyService->getByCode($toCurrencyCode);
        if (is_null($toCurrency)) {
            throw new CurrencyCodeException($toCurrencyCode);
        }

        return [$fromCurrency, $toCurrency];
    }

}
