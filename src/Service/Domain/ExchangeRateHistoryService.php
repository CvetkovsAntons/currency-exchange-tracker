<?php

namespace App\Service\Domain;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Entity\ExchangeRateHistory;
use App\Factory\ExchangeRateHistoryFactory;
use App\Repository\ExchangeRateHistoryRepository;
use DateTimeInterface;

readonly class ExchangeRateHistoryService
{
    public function __construct(
        private ExchangeRateHistoryFactory    $factory,
        private ExchangeRateHistoryRepository $repository,
    ) {}

    /**
     * @return ExchangeRateHistory[]
     */
    public function getAll(): array
    {
        return $this->repository->getAll();
    }

    public function create(ExchangeRate $exchangeRate): ExchangeRateHistory
    {
        $history = $this->factory->createFromRecord($exchangeRate);
        $this->repository->save($history);

        return $history;
    }

    public function getLatest(CurrencyPair $pair, ?DateTimeInterface $createdAt = null): ?ExchangeRateHistory
    {
        if (is_null($createdAt)) {
            return $this->repository->getLatest($pair);
        }

        $after = $this->repository->getLatestAfter($pair, $createdAt);

        if (!is_null($after)) {
            return $after;
        }

        return $this->repository->getLatestBefore($pair, $createdAt);
    }

}
