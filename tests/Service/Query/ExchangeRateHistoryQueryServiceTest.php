<?php

namespace App\Tests\Service\Query;

use App\Dto\ExchangeRateRequest;
use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Entity\ExchangeRateHistory;
use App\Exception\CurrencyCodeException;
use App\Exception\DateTimeInvalidException;
use App\Exception\MissingParametersException;
use App\Repository\ExchangeRateHistoryRepository;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\CurrencyService;
use App\Service\Query\ExchangeRateHistoryQueryService;
use App\Tests\Internal\Factory\RequestTestFactory;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExchangeRateHistoryQueryServiceTest extends TestCase
{
    private ExchangeRateHistoryRepository&MockObject $repository;
    private CurrencyService&MockObject $currencyService;
    private CurrencyPairService&MockObject $pairService;
    private ExchangeRateHistoryQueryService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ExchangeRateHistoryRepository::class);
        $this->currencyService = $this->createMock(CurrencyService::class);
        $this->pairService = $this->createMock(CurrencyPairService::class);

        $this->service = new ExchangeRateHistoryQueryService(
            $this->repository,
            $this->currencyService,
            $this->pairService
        );
    }

    public function testFetchSuccess(): void
    {
        $request = RequestTestFactory::validExchangeRate();

        [$from, $to] = $this->currencyMocks();
        $pair = $this->createMock(CurrencyPair::class);
        $exchangeRate = $this->createMock(ExchangeRateHistory::class);

        $this->getCurrencyMock($from);
        $this->getCurrencyMock($to);
        $this->getCurrencyPairMock($from, $to, $pair);

        $this->repository
            ->method('findClosest')
            ->with($pair, new DateTimeImmutable($request->datetime))
            ->willReturn($exchangeRate);

        $result = $this->service->getClosestExchangeRate($request);

        $this->assertSame($exchangeRate, $result);
    }

    public function testFetchThrowsOnMissingParameters(): void
    {
        $this->expectException(MissingParametersException::class);

        $request = new ExchangeRateRequest();

        $this->service->getClosestExchangeRate($request);
    }

    public function testFetchThrowsWhenFromCurrencyNotFound(): void
    {
        $this->expectException(CurrencyCodeException::class);

        $request = RequestTestFactory::validExchangeRate('AAA');

        $this->getCurrencyMock(null);

        $this->service->getClosestExchangeRate($request);
    }

    public function testFetchThrowsOnInvalidDatetime(): void
    {
        $this->expectException(DateTimeInvalidException::class);

        $request = RequestTestFactory::invalidExchangeRate();

        [$from, $to] = $this->currencyMocks();
        $pair = $this->createMock(CurrencyPair::class);

        $this->getCurrencyMock($from);
        $this->getCurrencyMock($to);
        $this->getCurrencyPairMock($from, $to, $pair);

        $this->service->getClosestExchangeRate($request);
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
