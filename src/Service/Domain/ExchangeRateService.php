<?php

namespace App\Service\Domain;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Factory\ExchangeRateFactory;
use App\Factory\ExchangeRateHistoryFactory;
use App\Provider\CurrencyApiProvider;
use App\Repository\ExchangeRateHistoryRepository;
use App\Repository\ExchangeRateRepository;
use App\Service\Console\CommandInvokerService;
use DateTimeImmutable;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;

readonly class ExchangeRateService
{
    public function __construct(
        private ExchangeRateFactory           $factory,
        private ExchangeRateHistoryFactory    $historyFactory,
        private ExchangeRateRepository        $repository,
        private ExchangeRateHistoryRepository $historyRepository,
        private CurrencyApiProvider           $provider,
        private CurrencyPairService           $pairService,
        private CommandInvokerService         $commandInvokerService,
    ) {}

    public function create(CurrencyPair $currencyPair): ExchangeRate
    {
        $fromCurrency = $currencyPair->getFromCurrency();
        $toCurrency = $currencyPair->getToCurrency();

        if (!$this->pairService->exists($fromCurrency, $toCurrency)) {
            $this->commandInvokerService->runCreateCurrencyPair($fromCurrency->getCode(), $toCurrency->getCode());
            $currencyPair = $this->pairService->get($fromCurrency,  $toCurrency);
        }

        if ($this->exists($currencyPair)) {
            throw new DuplicateKeyException(sprintf(
                'Exchange rate for %s-%s already exists',
                $fromCurrency->getCode(),
                $toCurrency->getCode()
            ));
        }

        $now = new DateTimeImmutable();

        $exchangeRate = $this->provider->getLatestExchangeRate($currencyPair);

        $exchangeRate = $this->factory->create($currencyPair, $exchangeRate, $now);

        $this->saveExchangeRate($exchangeRate);

        return $exchangeRate;
    }

    public function exists(CurrencyPair $currencyPair): bool
    {
        return $this->repository->exists($currencyPair);
    }

    public function update(ExchangeRate $exchangeRate): ExchangeRate
    {
        $currencyPair = $exchangeRate->getCurrencyPair();

        if (!$this->exists($currencyPair)) {
            return $this->create($currencyPair);
        }

        $exchangeRate->setRate($this->provider->getLatestExchangeRate($currencyPair));

        $this->saveExchangeRate($exchangeRate);

        return $exchangeRate;
    }

    public function updateByPair(CurrencyPair $currencyPair): ExchangeRate
    {
        $exchangeRate = $this->get($currencyPair);

        if (is_null($exchangeRate)) {
            return $this->create($currencyPair);
        }

        return $this->update($exchangeRate);
    }

    private function saveExchangeRate(ExchangeRate $exchangeRate): void
    {
        $this->repository->save($exchangeRate);

        $history = $this->historyFactory->createFromRecord($exchangeRate);
        $this->historyRepository->save($history);
    }

    public function get(CurrencyPair $currencyPair): ?ExchangeRate
    {
        return $this->repository->findOneBy(['currencyPair' => $currencyPair]);
    }

}
