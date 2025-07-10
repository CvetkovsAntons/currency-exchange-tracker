<?php

namespace App\Tests\Service\Domain\CurrencyService;

use App\Dto\Currency as CurrencyDto;
use App\Factory\CurrencyFactory;
use App\Provider\CurrencyApiProvider;
use App\Repository\CurrencyRepository;
use App\Service\Domain\CurrencyService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractCurrencyServiceTest extends TestCase
{
    protected CurrencyApiProvider&MockObject $provider;
    protected CurrencyFactory&MockObject $factory;
    protected CurrencyRepository&MockObject $repository;
    protected CurrencyService $service;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(CurrencyApiProvider::class);
        $this->factory = $this->createMock(CurrencyFactory::class);
        $this->repository = $this->createMock(CurrencyRepository::class);

        $this->service = $this->getMockBuilder(CurrencyService::class)
            ->setConstructorArgs([$this->provider, $this->factory, $this->repository])
            ->onlyMethods(['exists'])
            ->getMock();
    }

    protected function currencyExistsMock(string $currencyCode, bool $return): void
    {
        $this->service
            ->method('exists')
            ->with($currencyCode)
            ->willReturn($return);
    }

    protected function getCurrencyMock(string $currencyCode, ?CurrencyDto $return): void
    {
        $this->provider
            ->method('getCurrency')
            ->with($currencyCode)
            ->willReturn($return);
    }

}
