<?php

namespace App\Tests\Service\Domain\ExchangeRateHistoryService;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRateHistory;
use DateTimeImmutable;

class ExchangeRateHistoryServiceGetLatestTest extends AbstractExchangeRateHistoryServiceTest
{
    public function testNoDateTime(): void
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

    public function testReturnsLatestAfter(): void
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

    public function testReturnsLatestBefore(): void
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

    public function testReturnsNull(): void
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
