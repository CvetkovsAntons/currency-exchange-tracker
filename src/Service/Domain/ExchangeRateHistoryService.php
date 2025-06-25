<?php

namespace App\Service\Domain;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Entity\ExchangeRateHistory;
use App\Exception\CurrencyPairException;
use App\Factory\ExchangeRateHistoryFactory;
use App\Repository\ExchangeRateHistoryRepository;
use DateTimeInterface;

readonly class ExchangeRateHistoryService
{
    public function __construct(
        private ExchangeRateHistoryFactory    $factory,
        private ExchangeRateHistoryRepository $repository,
        private CurrencyPairService           $pairService,
    ) {}

    /**
     * @return ExchangeRateHistory[]
     */
    public function getAll(): array
    {
        return $this->repository->getAll();
    }

    public function get(CurrencyPair $pair, ?DateTimeInterface $createdAt = null): ?ExchangeRateHistory
    {
        return $this->repository->findClosestBefore($pair, $createdAt);
    }

    public function create(ExchangeRate $exchangeRate): ExchangeRateHistory
    {
        $history = $this->factory->createFromRecord($exchangeRate);
        $this->repository->save($history);

        return $history;
    }

}
