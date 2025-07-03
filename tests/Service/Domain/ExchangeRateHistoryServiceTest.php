<?php

namespace App\Tests\Service\Domain;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Entity\ExchangeRateHistory;
use App\Factory\ExchangeRateHistoryFactory;
use App\Repository\ExchangeRateHistoryRepository;
use App\Service\Domain\ExchangeRateHistoryService;
use DateTimeImmutable;
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

    public function testGetLatestNoDateTime(): void
    {
        $pair = $this->createMock(CurrencyPair::class);
        $latest = $this->createMock(ExchangeRateHistory::class);

        $this->repository
            ->expects($this->once())
            ->method('getLatest')
            ->with($pair)
            ->willReturn($latest);

        $result = $this->service->getLatest($pair);

        $this->assertSame($latest, $result);
    }

    public function testGetLatestReturnsLatestAfter(): void
    {
        $pair = $this->createMock(CurrencyPair::class);
        $date = new DateTimeImmutable('2025-07-01 12:00:00');
        $after = $this->createMock(ExchangeRateHistory::class);

        $this->repository
            ->expects($this->once())
            ->method('getLatestAfter')
            ->with($pair, $date)
            ->willReturn($after);

        $this->repository
            ->expects($this->never())
            ->method('getLatestBefore');

        $result = $this->service->getLatest($pair, $date);

        $this->assertSame($after, $result);
    }

    public function testGetLatestReturnsLatestBefore(): void
    {
        $pair = $this->createMock(CurrencyPair::class);
        $date = new DateTimeImmutable('2025-07-01 12:00:00');
        $before = $this->createMock(ExchangeRateHistory::class);

        $this->repository
            ->expects($this->once())
            ->method('getLatestAfter')
            ->with($pair, $date)
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('getLatestBefore')
            ->with($pair, $date)
            ->willReturn($before);

        $result = $this->service->getLatest($pair, $date);

        $this->assertSame($before, $result);
    }

    public function testGetLatestReturnsNull(): void
    {
        $pair = $this->createMock(CurrencyPair::class);
        $date = new DateTimeImmutable('2025-07-01 12:00:00');

        $this->repository
            ->expects($this->once())
            ->method('getLatestAfter')
            ->with($pair, $date)
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('getLatestBefore')
            ->with($pair, $date)
            ->willReturn(null);

        $result = $this->service->getLatest($pair, $date);

        $this->assertNull($result);
    }

}
