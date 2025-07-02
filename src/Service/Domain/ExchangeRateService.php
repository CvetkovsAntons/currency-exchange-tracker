<?php

namespace App\Service\Domain;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Exception\CurrencyPair\CurrencyPairNotFoundException;
use App\Exception\ExchangeRate\DuplicateExchangeRateException;
use App\Exception\ExchangeRate\ExchangeRateNotFoundException;
use App\Factory\ExchangeRateFactory;
use App\Provider\CurrencyApiProvider;
use App\Repository\ExchangeRateRepository;
use DateTimeImmutable;
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
     * @throws CurrencyPairNotFoundException
     * @throws ExchangeRateNotFoundException
     * @throws DuplicateExchangeRateException
     */
    public function create(CurrencyPair $pair): ExchangeRate
    {
        $from = $pair->getFromCurrency();
        $to = $pair->getToCurrency();

        if (!$this->pairService->exists($from, $to)) {
            throw new CurrencyPairNotFoundException($from->getCode(), $to->getCode());
        }

        if ($this->exists($pair)) {
            throw new DuplicateExchangeRateException($from->getCode(), $to->getCode());
        }

        $rate = $this->provider->getLatestExchangeRate($pair);
        if (is_null($rate)) {
            throw new ExchangeRateNotFoundException($from->getCode(), $to->getCode());
        }

        $exchangeRate = $this->factory->create($pair, $rate, new DateTimeImmutable());

        $this->saveExchangeRate($exchangeRate);

        return $exchangeRate;
    }

    public function exists(CurrencyPair $currencyPair): bool
    {
        return !is_null($this->repository->findOneBy(['currencyPair' => $currencyPair]));
    }

    /**
     * @param ExchangeRate $exchangeRate
     * @return ExchangeRate
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ExchangeRateNotFoundException
     */
    public function sync(ExchangeRate $exchangeRate): ExchangeRate
    {
        $pair = $exchangeRate->getCurrencyPair();
        $from = $pair->getFromCurrency();
        $to = $pair->getToCurrency();

        if (!$this->exists($pair)) {
            throw new ExchangeRateNotFoundException($from->getCode(), $to->getCode());
        }

        $rate = $this->provider->getLatestExchangeRate($pair);
        if (is_null($rate)) {
            throw new ExchangeRateNotFoundException($from->getCode(), $to->getCode());
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
     * @throws ExchangeRateNotFoundException
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

    public function delete(ExchangeRate $exchangeRate): void
    {
        $this->repository->delete($exchangeRate);
    }

}
