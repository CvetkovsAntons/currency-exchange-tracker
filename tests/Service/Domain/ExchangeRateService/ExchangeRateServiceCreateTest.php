<?php

namespace App\Tests\Service\Domain\ExchangeRateService;

use App\Entity\ExchangeRate;
use App\Exception\CurrencyApi\ExchangeRateNotFoundException as CurrencyApiExchangeRateNotFoundException;
use App\Exception\CurrencyPair\CurrencyPairNotFoundException;
use App\Exception\ExchangeRate\DuplicateExchangeRateException;
use App\Tests\Abstract\Service\AbstractExchangeRateServiceTest;
use DateTimeImmutable;

class ExchangeRateServiceCreateTest extends AbstractExchangeRateServiceTest
{
    public function testSuccess(): void
    {
        [$pair, $from, $to] = $this->currencyMocks();
        $exchangeRate = $this->createMock(ExchangeRate::class);
        $rate = '1.23';

        $this->currencyPairExistsMock($from, $to, true);
        $this->exchangeRateExistsMock($pair, false);
        $this->latestExchangeRateMock($pair, $rate);

        $this->factory
            ->method('make')
            ->with($pair, $rate, $this->callback(fn ($v) => $v instanceof DateTimeImmutable))
            ->willReturn($exchangeRate);

        $result = $this->service->create($pair);

        $this->assertSame($exchangeRate, $result);
    }

    public function testPairNotExists(): void
    {
        $this->expectException(CurrencyPairNotFoundException::class);

        [$pair, $from, $to] = $this->currencyMocks();

        $this->currencyPairExistsMock($from, $to, false);

        $this->service->create($pair);
    }

    public function testAlreadyExists(): void
    {
        $this->expectException(DuplicateExchangeRateException::class);

        [$pair, $from, $to] = $this->currencyMocks();

        $this->currencyPairExistsMock($from, $to, true);
        $this->exchangeRateExistsMock($pair, true);

        $this->service->create($pair);
    }

    public function testNotFound(): void
    {
        $this->expectException(CurrencyApiExchangeRateNotFoundException::class);

        [$pair, $from, $to] = $this->currencyMocks();

        $this->currencyPairExistsMock($from, $to, true);
        $this->exchangeRateExistsMock($pair, false);
        $this->latestExchangeRateMock($pair, null);

        $this->service->create($pair);
    }

}
