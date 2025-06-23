<?php

namespace App\Service\Domain;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Exception\CurrencyPairException;
use App\Factory\ExchangeRateFactory;
use App\Provider\CurrencyApiProvider;
use App\Repository\ExchangeRateRepository;
use DateTimeImmutable;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;

readonly class ExchangeRateService
{
    public function __construct(
        private ExchangeRateFactory        $factory,
        private ExchangeRateRepository     $repository,
        private ExchangeRateHistoryService $historyService,
        private CurrencyApiProvider        $provider,
        private CurrencyPairService        $pairService,
    ) {}

    public function create(CurrencyPair $currencyPair): ExchangeRate
    {
        $fromCurrency = $currencyPair->getFromCurrency();
        $toCurrency = $currencyPair->getToCurrency();

        if (!$this->pairService->exists($fromCurrency, $toCurrency)) {
            throw new CurrencyPairException(sprintf(
                "Currency pair %s-%s doesn't exist",
                $fromCurrency->getCode(),
                $toCurrency->getCode()
            ));
        }

        if ($this->exists($currencyPair)) {
            throw new DuplicateKeyException(sprintf(
                'Exchange rate for %s-%s already exists',
                $fromCurrency->getCode(),
                $toCurrency->getCode()
            ));
        }

        $exchangeRate = $this->factory->create(
            pair: $currencyPair,
            rate: $this->provider->getLatestExchangeRate($currencyPair),
            datetime: new DateTimeImmutable()
        );

        $this->saveExchangeRate($exchangeRate);

        return $exchangeRate;
    }

    public function exists(CurrencyPair $currencyPair): bool
    {
        return $this->repository->exists($currencyPair);
    }

    public function sync(ExchangeRate $exchangeRate): ExchangeRate
    {
        $currencyPair = $exchangeRate->getCurrencyPair();

        if (!$this->exists($currencyPair)) {
            $fromCurrency = $currencyPair->getFromCurrency();
            $toCurrency = $currencyPair->getToCurrency();

            throw new CurrencyPairException(sprintf(
                "Exchange rate for currency pair %s-%s doesn't exist",
                $fromCurrency->getCode(),
                $toCurrency->getCode()
            ));
        }

        $exchangeRate->setRate($this->provider->getLatestExchangeRate($currencyPair));

        $this->saveExchangeRate($exchangeRate);

        return $exchangeRate;
    }

    public function syncAll(): void
    {
        foreach ($this->getAll() as $exchangeRate) {
            $this->sync($exchangeRate);
        }
    }

    private function saveExchangeRate(ExchangeRate $exchangeRate): void
    {
        $this->repository->save($exchangeRate);
        $this->historyService->create($exchangeRate);
    }

    public function get(CurrencyPair $currencyPair): ?ExchangeRate
    {
        return $this->repository->findOneBy(['currencyPair' => $currencyPair]);
    }

    /**
     * @return ExchangeRate[]
     */
    public function getAll(): array
    {
        return $this->repository->findAll();
    }

}
