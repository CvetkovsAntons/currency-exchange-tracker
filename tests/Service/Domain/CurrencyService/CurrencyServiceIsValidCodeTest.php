<?php

namespace App\Tests\Service\Domain\CurrencyService;

use App\Tests\Internal\Factory\CurrencyTestFactory;
use RuntimeException;

class CurrencyServiceIsValidCodeTest extends AbstractCurrencyServiceTest
{
    public function testReturnsTrueWhenCurrencyExists(): void
    {
        $code = 'USD';

        $currencyDto = CurrencyTestFactory::makeDto($code);

        $this->getCurrencyMock($code, $currencyDto);

        $result = $this->service->isValidCode($code);

        $this->assertTrue($result);
    }

    public function testReturnsFalseWhenCurrencyIsNull(): void
    {
        $code = 'XYZ';

        $this->getCurrencyMock($code, null);

        $result = $this->service->isValidCode($code);

        $this->assertFalse($result);
    }

    public function testReturnsFalseOnException(): void
    {
        $code = 'FAIL';

        $this->provider
            ->method('getCurrency')
            ->with($code)
            ->willThrowException(new RuntimeException('API error'));

        $result = $this->service->isValidCode($code);

        $this->assertFalse($result);
    }

}
