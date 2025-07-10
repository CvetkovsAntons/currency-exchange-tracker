<?php

namespace App\Tests\Service\Domain\CurrencyService;

use App\Entity\Currency;
use App\Exception\Currency\DuplicateCurrencyCodeException;
use App\Exception\CurrencyApi\CurrencyDataNotFoundException;
use App\Tests\Internal\Factory\CurrencyTestFactory;

class CurrencyServiceCreateTest extends AbstractCurrencyServiceTest
{
    public function testSuccess(): void
    {
        $code = 'USD';
        $currencyDto = CurrencyTestFactory::makeDto($code);
        $currency = $this->createMock(Currency::class);

        $this->currencyExistsMock($code, false);
        $this->getCurrencyMock($code, $currencyDto);

        $this->factory
            ->method('makeFromDto')
            ->with($currencyDto)
            ->willReturn($currency);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($currency);

        $result = $this->service->create($code);

        $this->assertSame($currency, $result);
    }

    public function testAlreadyExists(): void
    {
        $this->expectException(DuplicateCurrencyCodeException::class);

        $code = 'EUR';

        $this->currencyExistsMock($code, true);

        $this->service->create($code);
    }

    public function testApiReturnIsEmpty(): void
    {
        $this->expectException(CurrencyDataNotFoundException::class);

        $code = 'ABC';

        $this->currencyExistsMock($code, false);
        $this->getCurrencyMock($code, null);

        $this->service->create($code);
    }

}
