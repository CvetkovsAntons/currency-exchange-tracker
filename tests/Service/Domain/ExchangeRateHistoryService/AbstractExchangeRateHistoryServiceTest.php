<?php

namespace App\Tests\Service\Domain\ExchangeRateHistoryService;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Entity\ExchangeRateHistory;
use App\Factory\ExchangeRateHistoryFactory;
use App\Repository\ExchangeRateHistoryRepository;
use App\Service\Domain\ExchangeRateHistoryService;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractExchangeRateHistoryServiceTest extends TestCase
{
    protected ExchangeRateHistoryFactory&MockObject $factory;
    protected ExchangeRateHistoryRepository&MockObject $repository;
    protected ExchangeRateHistoryService $service;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(ExchangeRateHistoryFactory::class);
        $this->repository = $this->createMock(ExchangeRateHistoryRepository::class);

        $this->service = new ExchangeRateHistoryService(
            $this->factory,
            $this->repository,
        );
    }

}
