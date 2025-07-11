<?php

namespace App\Tests\Abstract\Service;

use App\Factory\ExchangeRateHistoryFactory;
use App\Repository\ExchangeRateHistoryRepository;
use App\Service\Domain\ExchangeRateHistoryService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractExchangeRateHistoryServiceTest extends TestCase
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
