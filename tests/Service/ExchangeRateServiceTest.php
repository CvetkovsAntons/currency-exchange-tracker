<?php

namespace App\Tests\Service;

use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Exception\CurrencyPairException;
use App\Exception\ExchangeRateException;
use App\Factory\ExchangeRateFactory;
use App\Provider\CurrencyApiProvider;
use App\Repository\ExchangeRateRepository;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\ExchangeRateHistoryService;
use App\Service\Domain\ExchangeRateService;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;

class ExchangeRateServiceTest extends TestCase
{
    private ExchangeRateFactory&MockObject $factory;
    private ExchangeRateRepository&MockObject $repository;
    private ExchangeRateHistoryService&MockObject $historyService;
    private CurrencyApiProvider&MockObject $provider;
    private CurrencyPairService&MockObject $pairService;
    private ExchangeRateService $service;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(ExchangeRateFactory::class);
        $this->repository = $this->createMock(ExchangeRateRepository::class);
        $this->historyService = $this->createMock(ExchangeRateHistoryService::class);
        $this->provider = $this->createMock(CurrencyApiProvider::class);
        $this->pairService = $this->createMock(CurrencyPairService::class);

        $this->service = $this->getMockBuilder(ExchangeRateService::class)
            ->setConstructorArgs([
                $this->factory,
                $this->repository,
                $this->historyService,
                $this->provider,
                $this->pairService
            ])
            ->onlyMethods(['exists'])
            ->getMock();
    }

    private function createMockCurrencyPair(): CurrencyPair
    {
        $currencyPair = $this->createMock(CurrencyPair::class);
        $fromCurrency = $this->createMock(Currency::class);
        $toCurrency = $this->createMock(Currency::class);

        $fromCurrency->method('getCode')
            ->willReturn('USD');

        $toCurrency->method('getCode')
            ->willReturn('EUR');

        $currencyPair->method('getFromCurrency')
            ->willReturn($fromCurrency);

        $currencyPair->method('getToCurrency')
            ->willReturn($toCurrency);

        return $currencyPair;
    }

    public function testCreateExchangeRateSuccess(): void
    {
        $currencyPair = $this->createMock(CurrencyPair::class);
        $fromCurrency = $this->createMock(Currency::class);
        $toCurrency = $this->createMock(Currency::class);
        $exchangeRate = $this->createMock(ExchangeRate::class);
        $rate = '1.23';

        $currencyPair->method('getFromCurrency')
            ->willReturn($fromCurrency);

        $currencyPair->method('getToCurrency')
            ->willReturn($toCurrency);

        $fromCurrency->method('getCode')
            ->willReturn('USD');

        $toCurrency->method('getCode')
            ->willReturn('EUR');

        $this->pairService
            ->method('exists')
            ->with($fromCurrency, $toCurrency)
            ->willReturn(true);

        $this->service
            ->method('exists')
            ->with($currencyPair)
            ->willReturn(false);

        $this->provider
            ->method('getLatestExchangeRate')
            ->with($currencyPair)
            ->willReturn($rate);

        $this->factory
            ->method('create')
            ->with(
                $currencyPair,
                $rate,
                $this->callback(fn($dt) => $dt instanceof DateTimeImmutable),
            )
            ->willReturn($exchangeRate);

        $result = $this->service->create($currencyPair);

        $this->assertSame($exchangeRate, $result);
    }

    public function testCreateExchangeRatePairNotExists(): void
    {
        $this->expectException(CurrencyPairException::class);

        $currencyPair = $this->createMock(CurrencyPair::class);
        $fromCurrency = $this->createMock(Currency::class);
        $toCurrency = $this->createMock(Currency::class);

        $currencyPair->method('getFromCurrency')
            ->willReturn($fromCurrency);

        $currencyPair->method('getToCurrency')
            ->willReturn($toCurrency);

        $fromCurrency->method('getCode')
            ->willReturn('USD');

        $toCurrency->method('getCode')
            ->willReturn('EUR');

        $this->pairService
            ->method('exists')
            ->with($fromCurrency, $toCurrency)
            ->willReturn(false);

        $this->service->create($currencyPair);
    }

    public function testCreateExchangeRateAlreadyExists(): void
    {
        $this->expectException(DuplicateKeyException::class);

        $currencyPair = $this->createMock(CurrencyPair::class);
        $fromCurrency = $this->createMock(Currency::class);
        $toCurrency = $this->createMock(Currency::class);

        $currencyPair->method('getFromCurrency')
            ->willReturn($fromCurrency);

        $currencyPair->method('getToCurrency')
            ->willReturn($toCurrency);

        $fromCurrency->method('getCode')
            ->willReturn('USD');

        $toCurrency->method('getCode')
            ->willReturn('EUR');

        $this->pairService
            ->method('exists')
            ->with($fromCurrency, $toCurrency)
            ->willReturn(true);

        $this->service
            ->method('exists')
            ->with($currencyPair)
            ->willReturn(true);

        $this->service->create($currencyPair);
    }

    public function testCreateExchangeRateNotFound(): void
    {
        $this->expectException(ExchangeRateException::class);

        $currencyPair = $this->createMock(CurrencyPair::class);
        $fromCurrency = $this->createMock(Currency::class);
        $toCurrency = $this->createMock(Currency::class);

        $currencyPair->method('getFromCurrency')
            ->willReturn($fromCurrency);

        $currencyPair->method('getToCurrency')
            ->willReturn($toCurrency);

        $fromCurrency->method('getCode')
            ->willReturn('USD');

        $toCurrency->method('getCode')
            ->willReturn('EUR');

        $this->pairService
            ->method('exists')
            ->with($fromCurrency, $toCurrency)
            ->willReturn(true);

        $this->service
            ->method('exists')
            ->with($currencyPair)
            ->willReturn(false);

        $this->provider
            ->method('getLatestExchangeRate')
            ->with($currencyPair)
            ->willReturn(null);

        $this->service->create($currencyPair);
    }

}
