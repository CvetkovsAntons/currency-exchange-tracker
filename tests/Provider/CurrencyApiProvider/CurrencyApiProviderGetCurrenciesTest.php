<?php

namespace App\Tests\Provider\CurrencyApiProvider;

use App\Dto\Currency as CurrencyDto;
use App\Exception\CurrencyApi\CurrencyApiUnavailableException;
use App\Tests\Abstract\Provider\AbstractCurrencyApiProviderTest;

class CurrencyApiProviderGetCurrenciesTest extends AbstractCurrencyApiProviderTest
{
    public function testReturnsArray(): void
    {
        $code = 'USD';
        $currency = $this->prepareGetCurrency($code);

        $this->cache
            ->method('getCurrencies')
            ->willReturn([$code => $currency]);

        $this->client
            ->expects($this->never())
            ->method('currencies');

        $result = $this->provider->getCurrencies($code);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(CurrencyDto::class, $result[$code]);
        $this->assertSame($currency, $result[$code]);
    }

    public function testThrowsException(): void
    {
        $this->cache
            ->method('getCurrencies')
            ->willReturn(['USD' => null]);

        $this->provider = $this->providerMock();

        $this->provider
            ->method('isAlive')
            ->willReturn(false);

        $this->expectException(CurrencyApiUnavailableException::class);

        $this->provider->getCurrencies('USD');
    }

}
