<?php

namespace App\Tests\Service\Domain;

use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Exception\CurrencyPairException;
use App\Factory\CurrencyPairFactory;
use App\Repository\CurrencyPairRepository;
use App\Service\Domain\CurrencyPairService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CurrencyPairTest extends TestCase
{
    private CurrencyPairFactory&MockObject $factory;
    private CurrencyPairRepository&MockObject $repository;
    private CurrencyPairService $service;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(CurrencyPairFactory::class);
        $this->repository = $this->createMock(CurrencyPairRepository::class);

        $this->service = $this->getMockBuilder(CurrencyPairService::class)
            ->setConstructorArgs([$this->factory, $this->repository])
            ->onlyMethods(['exists'])
            ->getMock();
    }

    public function testCreateCurrencyPairSuccess(): void
    {
        [$from, $to] = $this->currencyMocks();
        $pair = $this->createMock(CurrencyPair::class);

        $this->currencyPairExistsMock($from, $to, false);

        $this->factory
            ->method('create')
            ->with($from, $to)
            ->willReturn($pair);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($pair);

        $result = $this->service->create($from, $to);

        $this->assertSame($pair, $result);
    }

    public function testCreateCurrencyPairAlreadyExists(): void
    {
        $this->expectException(CurrencyPairException::class);

        [$from, $to] = $this->currencyMocks();

        $this->currencyPairExistsMock($from, $to, true);

        $this->service->create($from, $to);
    }

    private function currencyMocks(): array
    {
        $from = $this->createMock(Currency::class);
        $to = $this->createMock(Currency::class);

        return [$from, $to];
    }

    private function currencyPairExistsMock(Currency $from, Currency $to, bool $return): void
    {
        $this->service
            ->method('exists')
            ->with($from, $to)
            ->willReturn($return);
    }

}
