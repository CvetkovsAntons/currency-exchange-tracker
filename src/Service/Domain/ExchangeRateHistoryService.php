<?php

namespace App\Service\Domain;

use App\Entity\ExchangeRate;
use App\Entity\ExchangeRateHistory;
use App\Factory\ExchangeRateHistoryFactory;
use App\Repository\ExchangeRateHistoryRepository;

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

}
