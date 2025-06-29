<?php

namespace App\Tests\Service\Domain;

use App\Dto\Currency as CurrencyDto;
use App\Entity\Currency;
use App\Exception\CurrencyApiException;
use App\Exception\CurrencyCodeException;
use App\Factory\CurrencyFactory;
use App\Provider\CurrencyApiProvider;
use App\Repository\CurrencyRepository;
use App\Service\Domain\CurrencyService;
use App\Tests\Utils\Factory\CurrencyTestFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CurrencyServiceTest extends TestCase
{
    private CurrencyApiProvider&MockObject $provider;
    private CurrencyFactory&MockObject $factory;
    private CurrencyRepository&MockObject $repository;
    private CurrencyService $service;

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

    public function testCreateCurrencySuccess(): void
    {
        $code = 'USD';
        $currencyDto = CurrencyTestFactory::makeDto($code);
        $currency = $this->createMock(Currency::class);

        $this->currencyExistsMock($code, false);
        $this->getCurrencyMock($code, $currencyDto);

        $this->factory
            ->method('create')
            ->with($currencyDto)
            ->willReturn($currency);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($currency);

        $result = $this->service->create($code);

        $this->assertSame($currency, $result);
    }

    public function testCreateCurrencyAlreadyExists(): void
    {
        $this->expectException(CurrencyCodeException::class);

        $code = 'EUR';

        $this->currencyExistsMock($code, true);

        $this->service->create($code);
    }

    public function testCreateCurrencyApiReturnsEmpty(): void
    {
        $this->expectException(CurrencyApiException::class);

        $code = 'ABC';

        $this->currencyExistsMock($code, false);
        $this->getCurrencyMock($code, null);

        $this->service->create($code);
    }

    public function testIsValidCodeReturnsTrueWhenCurrencyExists(): void
    {
        $code = 'USD';

        $currencyDto = CurrencyTestFactory::makeDto($code);

        $this->getCurrencyMock($code, $currencyDto);

        $result = $this->service->isValidCode($code);

        $this->assertTrue($result);
    }

    public function testIsValidCodeReturnsFalseWhenCurrencyIsNull(): void
    {
        $code = 'XYZ';

        $this->getCurrencyMock($code, null);

        $result = $this->service->isValidCode($code);

        $this->assertFalse($result);
    }

    public function testIsValidCodeReturnsFalseOnException(): void
    {
        $code = 'FAIL';

        $this->provider
            ->method('getCurrency')
            ->with($code)
            ->willThrowException(new RuntimeException('API error'));

        $result = $this->service->isValidCode($code);

        $this->assertFalse($result);
    }

    private function currencyExistsMock(string $currencyCode, bool $return): void
    {
        $this->service
            ->method('exists')
            ->with($currencyCode)
            ->willReturn($return);
    }

    private function getCurrencyMock(string $currencyCode, ?CurrencyDto $return): void
    {
        $this->provider
            ->method('getCurrency')
            ->with($currencyCode)
            ->willReturn($return);
    }

}
