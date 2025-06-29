<?php

namespace App\Service\Domain;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Exception\CurrencyPairException;
use App\Exception\ExchangeRateException;
use App\Factory\ExchangeRateFactory;
use App\Provider\CurrencyApiProvider;
use App\Repository\ExchangeRateRepository;
use DateTimeImmutable;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class ExchangeRateService
{
    public function __construct(
        private ExchangeRateFactory        $factory,
        private ExchangeRateRepository     $repository,
        private ExchangeRateHistoryService $historyService,
        private CurrencyApiProvider        $provider,
        private CurrencyPairService        $pairService,
    ) {}

    private function saveExchangeRate(ExchangeRate $exchangeRate): void
    {
        $this->repository->save($exchangeRate);
        $this->historyService->create($exchangeRate);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ExchangeRateException
     */
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

        $rate = $this->provider->getLatestExchangeRate($currencyPair);
        if (is_null($rate)) {
            throw new ExchangeRateException(sprintf(
                'Exchange rate for %s-%s not found',
                $fromCurrency->getCode(),
                $toCurrency->getCode()
            ));
        }

        $exchangeRate = $this->factory->create(
            pair: $currencyPair,
            rate: $rate,
            datetime: new DateTimeImmutable()
        );

        $this->saveExchangeRate($exchangeRate);

        return $exchangeRate;
    }

    public function exists(CurrencyPair $currencyPair): bool
    {
        return $this->repository->exists($currencyPair);
    }

    /**
     * @param ExchangeRate $exchangeRate
     * @return ExchangeRate
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ExchangeRateException
     */
    public function sync(ExchangeRate $exchangeRate): ExchangeRate
    {
        $currencyPair = $exchangeRate->getCurrencyPair();
        $fromCurrency = $currencyPair->getFromCurrency();
        $toCurrency = $currencyPair->getToCurrency();

        if (!$this->exists($currencyPair)) {
            throw new ExchangeRateException(sprintf(
                "Exchange rate for currency pair %s-%s doesn't exist",
                $fromCurrency->getCode(),
                $toCurrency->getCode()
            ));
        }

        $rate = $this->provider->getLatestExchangeRate($currencyPair);
        if (is_null($rate)) {
            throw new ExchangeRateException(sprintf(
                'Exchange rate for %s-%s not found',
                $fromCurrency->getCode(),
                $toCurrency->getCode()
            ));
        }

        $exchangeRate->setRate($rate);

        $this->saveExchangeRate($exchangeRate);

        return $exchangeRate;
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ExchangeRateException
     */
    public function syncAll(): void
    {
        foreach ($this->getAll() as $exchangeRate) {
            $this->sync($exchangeRate);
        }
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
