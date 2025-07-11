<?php

namespace App\Tests\Abstract\Service;

use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Factory\ExchangeRateFactory;
use App\Provider\CurrencyApiProvider;
use App\Repository\ExchangeRateRepository;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\ExchangeRateHistoryService;
use App\Service\Domain\ExchangeRateService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractExchangeRateServiceTest extends TestCase
{
    protected ExchangeRateFactory&MockObject $factory;
    protected ExchangeRateRepository&MockObject $repository;
    protected ExchangeRateHistoryService&MockObject $historyService;
    protected CurrencyApiProvider&MockObject $provider;
    protected CurrencyPairService&MockObject $pairService;
    protected ExchangeRateService $service;

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

    protected function currencyMocks(): array
    {
        $from = $this->createMock(Currency::class);
        $to = $this->createMock(Currency::class);
        $pair = $this->createMock(CurrencyPair::class);

        $from
            ->method('getCode')
            ->willReturn('USD');

        $to
            ->method('getCode')
            ->willReturn('EUR');

        $pair
            ->method('getFromCurrency')
            ->willReturn($from);

        $pair
            ->method('getToCurrency')
            ->willReturn($to);

        return [$pair, $from, $to];
    }

    protected function currencyPairExistsMock(Currency $from, Currency $to, bool $return): void
    {
        $this->pairService
            ->method('exists')
            ->with($from, $to)
            ->willReturn($return);
    }

    protected function exchangeRateExistsMock(CurrencyPair $pair, bool $return): void
    {
        $this->service
            ->method('exists')
            ->with($pair)
            ->willReturn($return);
    }

    protected function latestExchangeRateMock(CurrencyPair $pair, ?string $return): void
    {
        $this->provider
            ->method('getLatestExchangeRate')
            ->with($pair)
            ->willReturn($return);
    }

}
