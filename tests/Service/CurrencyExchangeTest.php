<?php

namespace App\Tests\Service;

use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Exception\CurrencyPairException;
use App\Factory\CurrencyPairFactory;
use App\Repository\CurrencyPairRepository;
use App\Service\Domain\CurrencyPairService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CurrencyExchangeTest extends TestCase
{
    private CurrencyPairFactory&MockObject $factory;
    private CurrencyPairRepository&MockObject $repository;
    private CurrencyPairService $service;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(CurrencyPairFactory::class);
        $this->repository = $this->createMock(CurrencyPairRepository::class);

        $this->service = new CurrencyPairService(
            $this->factory,
            $this->repository,
        );
    }

    public function testCreateCurrencyPairSuccess(): void
    {
        $fromCurrency = $this->createMock(Currency::class);
        $toCurrency = $this->createMock(Currency::class);
        $currencyPair = $this->createMock(CurrencyPair::class);

        $this->repository
            ->method('exists')
            ->with($fromCurrency, $toCurrency)
            ->willReturn(false);

        $this->factory
            ->method('create')
            ->with($fromCurrency, $toCurrency)
            ->willReturn($currencyPair);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($currencyPair);

        $result = $this->service->create($fromCurrency, $toCurrency);

        $this->assertSame($currencyPair, $result);
    }

    public function testCreateCurrencyPairAlreadyExists(): void
    {
        $this->expectException(CurrencyPairException::class);

        $fromCurrency = $this->createMock(Currency::class);
        $toCurrency = $this->createMock(Currency::class);

        $this->repository
            ->method('exists')
            ->with($fromCurrency, $toCurrency)
            ->willReturn(true);

        $this->service->create($fromCurrency, $toCurrency);
    }

}
