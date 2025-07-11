<?php

namespace App\Tests\Service\Domain\ExchangeRateService;

use App\Entity\ExchangeRate;
use App\Exception\CurrencyApi\ExchangeRateNotFoundException as CurrencyApiExchangeRateNotFoundException;
use App\Exception\ExchangeRate\ExchangeRateNotFoundException;
use App\Tests\Abstract\Service\AbstractExchangeRateServiceTest;

class ExchangeRateServiceSyncTest extends AbstractExchangeRateServiceTest
{
    public function testSuccess(): void
    {
        [$pair] = $this->currencyMocks();
        $exchangeRate = $this->createMock(ExchangeRate::class);
        $rate = '1.23';

        $exchangeRate
            ->method('getCurrencyPair')
            ->willReturn($pair);

        $this->exchangeRateExistsMock($pair, true);
        $this->latestExchangeRateMock($pair, $rate);

        $exchangeRate
            ->expects($this->once())
            ->method('setRate')
            ->with($rate);

        $result = $this->service->sync($exchangeRate);

        $this->assertSame($exchangeRate, $result);
    }

    public function testNotExists(): void
    {
        $this->expectException(ExchangeRateNotFoundException::class);

        [$pair] = $this->currencyMocks();
        $exchangeRate = $this->createMock(ExchangeRate::class);

        $exchangeRate
            ->method('getCurrencyPair')
            ->willReturn($pair);

        $this->exchangeRateExistsMock($pair, false);

        $this->service->sync($exchangeRate);
    }

    public function testNotFound(): void
    {
        $this->expectException(CurrencyApiExchangeRateNotFoundException::class);

        [$pair] = $this->currencyMocks();
        $exchangeRate = $this->createMock(ExchangeRate::class);

        $exchangeRate
            ->method('getCurrencyPair')
            ->willReturn($pair);

        $this->exchangeRateExistsMock($pair, true);
        $this->latestExchangeRateMock($pair, null);

        $this->service->sync($exchangeRate);
    }

}
