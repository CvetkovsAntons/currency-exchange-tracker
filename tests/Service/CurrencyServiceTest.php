<?php

namespace App\Tests\Service;

use App\Dto\Currency as CurrencyDto;
use App\Entity\Currency;
use App\Enum\CurrencyType;
use App\Exception\CurrencyApiException;
use App\Exception\CurrencyCodeException;
use App\Factory\CurrencyFactory;
use App\Provider\CurrencyApiProvider;
use App\Repository\CurrencyRepository;
use App\Service\Domain\CurrencyService;
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

        $this->service = new CurrencyService(
            $this->provider,
            $this->factory,
            $this->repository,
        );
    }

    private function prepareCurrencyDto(): CurrencyDto
    {
        $dto = new CurrencyDto();
        $dto->code = 'USD';
        $dto->name = 'US Dollar';
        $dto->name_plural = 'US Dollars';
        $dto->symbol = '$';
        $dto->symbol_native = '$';
        $dto->decimal_digits = 2;
        $dto->rounding = 0;
        $dto->type = CurrencyType::FIAT->value;

        return $dto;
    }

    public function testCreateCurrencySuccess(): void
    {
        $code = 'USD';
        $currencyData = $this->prepareCurrencyDto();
        $currency = $this->createMock(Currency::class);

        $this->repository
            ->method('exists')
            ->with($code)
            ->willReturn(false);

        $this->provider
            ->method('getCurrency')
            ->with($code)
            ->willReturn($currencyData);

        $this->factory
            ->method('create')
            ->with($currencyData)
            ->willReturn($currency);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($currency);

        $result = $this->service
            ->create($code);

        $this->assertSame($currency, $result);
    }

    public function testCreateCurrencyAlreadyExists(): void
    {
        $this->expectException(CurrencyCodeException::class);

        $code = 'EUR';

        $this->repository
            ->method('exists')
            ->with($code)
            ->willReturn(true);

        $this->service
            ->create($code);
    }

    public function testCreateCurrencyApiReturnsEmpty(): void
    {
        $this->expectException(CurrencyApiException::class);

        $code = 'ABC';

        $this->repository
            ->method('exists')
            ->with($code)
            ->willReturn(false);

        $this->provider
            ->method('getCurrency')
            ->with($code)
            ->willReturn(null);

        $this->service
            ->create($code);
    }

    public function testIsValidCodeReturnsTrueWhenCurrencyExists(): void
    {
        $dto = $this->prepareCurrencyDto();

        $this->provider
            ->method('getCurrency')
            ->with('USD')
            ->willReturn($dto);

        $result = $this->service
            ->isValidCode('USD');

        $this->assertTrue($result);
    }

    public function testIsValidCodeReturnsFalseWhenCurrencyIsNull(): void
    {
        $this->provider
            ->method('getCurrency')
            ->with('XYZ')
            ->willReturn(null);

        $result = $this->service
            ->isValidCode('XYZ');

        $this->assertFalse($result);
    }

    public function testIsValidCodeReturnsFalseOnException(): void
    {
        $this->provider
            ->method('getCurrency')
            ->with('FAIL')
            ->willThrowException(new RuntimeException('API error'));

        $result = $this->service
            ->isValidCode('FAIL');

        $this->assertFalse($result);
    }

}
