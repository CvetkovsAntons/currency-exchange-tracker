<?php

namespace App\Tests\Service\Query;

use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Entity\ExchangeRateHistory;
use App\Exception\DateTime\DateTimeInvalidFormatException;
use App\Exception\ExchangeRate\ExchangeRateNotFoundException;
use App\Exception\Request\MissingParametersException;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\CurrencyService;
use App\Service\Domain\ExchangeRateHistoryService;
use App\Service\Query\ExchangeRateHistoryQueryService;
use App\Tests\Internal\Factory\RequestTestFactory;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExchangeRateHistoryQueryServiceTest extends TestCase
{
    private ExchangeRateHistoryService&MockObject $historyService;
    private CurrencyService&MockObject $currencyService;
    private CurrencyPairService&MockObject $pairService;
    private ExchangeRateHistoryQueryService $service;

    protected function setUp(): void
    {
        $this->historyService = $this->createMock(ExchangeRateHistoryService::class);
        $this->currencyService = $this->createMock(CurrencyService::class);
        $this->pairService = $this->createMock(CurrencyPairService::class);

        $this->service = new ExchangeRateHistoryQueryService(
            $this->historyService,
            $this->currencyService,
            $this->pairService
        );
    }

    public function testGetLatestExchangeRateSuccess(): void
    {
        $request = RequestTestFactory::exchangeRate();

        [$from, $to] = $this->currencyMocks();
        $pair = $this->createMock(CurrencyPair::class);
        $rate = $this->createMock(ExchangeRateHistory::class);

        $this->getCurrencyMock($from);
        $this->getCurrencyMock($to);
        $this->getCurrencyPairMock($from, $to, $pair);

        $this->historyService
            ->method('getLatest')
            ->with($pair, new DateTimeImmutable($request->datetime))
            ->willReturn($rate);

        $result = $this->service->getLatestExchangeRate($request);

        $this->assertSame($rate, $result);
    }

    public function testGetLatestExchangeRateCurrencyNotFound(): void
    {
        $request = RequestTestFactory::exchangeRate();

        $this->currencyService
            ->method('get')
            ->willReturn(null);

        $result = $this->service->getLatestExchangeRate($request);

        $this->assertNull($result);
    }

    public function testGetLatestExchangeRateCurrencyPairNotFound(): void
    {
        $request = RequestTestFactory::exchangeRate();

        [$from, $to] = $this->currencyMocks();

        $this->getCurrencyMock($from);
        $this->getCurrencyMock($to);

        $this->pairService
            ->method('get')
            ->willReturn(null);

        $result = $this->service->getLatestExchangeRate($request);

        $this->assertNull($result);
    }

    public function testGetLatestExchangeRateMissingParameters(): void
    {
        $this->expectException(MissingParametersException::class);

        $request = RequestTestFactory::exchangeRate('', '');

        $this->service->getLatestExchangeRate($request);
    }

    public function testGetLatestExchangeRateInvalidDatetime(): void
    {
        $this->expectException(DateTimeInvalidFormatException::class);

        $request = RequestTestFactory::exchangeRate(datetime: 'not-a-date');

        [$from, $to] = $this->currencyMocks();
        $pair = $this->createMock(CurrencyPair::class);

        $this->getCurrencyMock($from);
        $this->getCurrencyMock($to);
        $this->getCurrencyPairMock($from, $to, $pair);

        $this->service->getLatestExchangeRate($request);
    }

    public function testGetLatestExchangeRateNotFound(): void
    {
        $this->expectException(ExchangeRateNotFoundException::class);

        $request = RequestTestFactory::exchangeRate();

        [$from, $to] = $this->currencyMocks();
        $pair = $this->createMock(CurrencyPair::class);

        $this->getCurrencyMock($from);
        $this->getCurrencyMock($to);
        $this->getCurrencyPairMock($from, $to, $pair);

        $this->historyService
            ->method('getLatest')
            ->with($pair, new DateTimeImmutable($request->datetime))
            ->willReturn(null);

        $this->service->getLatestExchangeRate($request);
    }

    private function getCurrencyMock(?Currency $return): void
    {
        $this->currencyService
            ->method('get')
            ->willReturn($return);
    }

    private function getCurrencyPairMock(Currency $from, Currency $to, ?CurrencyPair $return): void
    {
        $this->pairService
            ->method('get')
            ->with($from, $to)
            ->willReturn($return);
    }

    private function currencyMocks(): array
    {
        $from = $this->createMock(Currency::class);
        $to = $this->createMock(Currency::class);

        $from
            ->method('getCode')
            ->willReturn('USD');

        $to
            ->method('getCode')
            ->willReturn('EUR');

        return [$from, $to];
    }

}
