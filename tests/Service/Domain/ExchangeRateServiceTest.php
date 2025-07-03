<?php

namespace App\Tests\Service\Domain;

use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Exception\CurrencyApi\ExchangeRateNotFoundException as CurrencyApiExchangeRateNotFoundException;
use App\Exception\CurrencyPair\CurrencyPairNotFoundException;
use App\Exception\ExchangeRate\DuplicateExchangeRateException;
use App\Exception\ExchangeRate\ExchangeRateNotFoundException;
use App\Factory\ExchangeRateFactory;
use App\Provider\CurrencyApiProvider;
use App\Repository\ExchangeRateRepository;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\ExchangeRateHistoryService;
use App\Service\Domain\ExchangeRateService;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

    public function testCreateExchangeRateSuccess(): void
    {
        [$pair, $from, $to] = $this->currencyMocks();
        $exchangeRate = $this->createMock(ExchangeRate::class);
        $rate = '1.23';

        $this->currencyPairExistsMock($from, $to, true);
        $this->exchangeRateExistsMock($pair, false);
        $this->latestExchangeRateMock($pair, $rate);

        $this->factory
            ->method('create')
            ->with($pair, $rate, $this->callback(fn ($v) => $v instanceof DateTimeImmutable))
            ->willReturn($exchangeRate);

        $result = $this->service->create($pair);

        $this->assertSame($exchangeRate, $result);
    }

    public function testCreateExchangeRatePairNotExists(): void
    {
        $this->expectException(CurrencyPairNotFoundException::class);

        [$pair, $from, $to] = $this->currencyMocks();

        $this->currencyPairExistsMock($from, $to, false);

        $this->service->create($pair);
    }

    public function testCreateExchangeRateAlreadyExists(): void
    {
        $this->expectException(DuplicateExchangeRateException::class);

        [$pair, $from, $to] = $this->currencyMocks();

        $this->currencyPairExistsMock($from, $to, true);
        $this->exchangeRateExistsMock($pair, true);

        $this->service->create($pair);
    }

    public function testCreateExchangeRateNotFound(): void
    {
        $this->expectException(CurrencyApiExchangeRateNotFoundException::class);

        [$pair, $from, $to] = $this->currencyMocks();

        $this->currencyPairExistsMock($from, $to, true);
        $this->exchangeRateExistsMock($pair, false);
        $this->latestExchangeRateMock($pair, null);

        $this->service->create($pair);
    }

    public function testSyncExchangeRateSuccess(): void
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

    public function testSyncExchangeRateNotExists(): void
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

    public function testSyncExchangeRateNotFound(): void
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

    private function currencyMocks(): array
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

    private function currencyPairExistsMock(Currency $from, Currency $to, bool $return): void
    {
        $this->pairService
            ->method('exists')
            ->with($from, $to)
            ->willReturn($return);
    }

    private function exchangeRateExistsMock(CurrencyPair $pair, bool $return): void
    {
        $this->service
            ->method('exists')
            ->with($pair)
            ->willReturn($return);
    }

    private function latestExchangeRateMock(CurrencyPair $pair, ?string $return): void
    {
        $this->provider
            ->method('getLatestExchangeRate')
            ->with($pair)
            ->willReturn($return);
    }

}
