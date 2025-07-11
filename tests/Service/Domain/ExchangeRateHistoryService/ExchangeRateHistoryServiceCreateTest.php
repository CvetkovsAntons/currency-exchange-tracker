<?php

namespace App\Tests\Service\Domain\ExchangeRateHistoryService;

use App\Entity\ExchangeRate;
use App\Entity\ExchangeRateHistory;
use App\Tests\Abstract\Service\AbstractExchangeRateHistoryServiceTest;

class ExchangeRateHistoryServiceCreateTest extends AbstractExchangeRateHistoryServiceTest
{
    public function testCreateExchangeRateHistorySuccess(): void
    {
        $exchangeRate = $this->createMock(ExchangeRate::class);
        $exchangeRateHistory = $this->createMock(ExchangeRateHistory::class);

        $this->factory
            ->method('makeFromExchangeRate')
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
