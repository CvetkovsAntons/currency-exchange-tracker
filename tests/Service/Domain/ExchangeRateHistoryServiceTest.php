<?php

namespace App\Tests\Service\Domain;

use App\Entity\ExchangeRate;
use App\Entity\ExchangeRateHistory;
use App\Factory\ExchangeRateHistoryFactory;
use App\Repository\ExchangeRateHistoryRepository;
use App\Service\Domain\ExchangeRateHistoryService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExchangeRateHistoryServiceTest extends TestCase
{
    private ExchangeRateHistoryFactory&MockObject $factory;
    private ExchangeRateHistoryRepository&MockObject $repository;
    private ExchangeRateHistoryService $service;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(ExchangeRateHistoryFactory::class);
        $this->repository = $this->createMock(ExchangeRateHistoryRepository::class);

        $this->service = new ExchangeRateHistoryService(
            $this->factory,
            $this->repository,
        );
    }

    public function testCreateExchangeRateHistorySuccess(): void
    {
        $exchangeRate = $this->createMock(ExchangeRate::class);
        $exchangeRateHistory = $this->createMock(ExchangeRateHistory::class);

        $this->factory
            ->method('createFromRecord')
            ->with($exchangeRate)
            ->willReturn($exchangeRateHistory);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($exchangeRateHistory);

        $result = $this->service
            ->create($exchangeRate);

        $this->assertSame($exchangeRateHistory, $result);
    }

}
