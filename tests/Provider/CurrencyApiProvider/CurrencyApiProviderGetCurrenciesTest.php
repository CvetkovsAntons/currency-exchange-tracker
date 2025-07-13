<?php

namespace App\Tests\Provider\CurrencyApiProvider;

use App\Dto\Currency as CurrencyDto;
use App\Exception\CurrencyApi\CurrencyApiUnavailableException;
use App\Tests\Abstract\Provider\AbstractCurrencyApiProviderTest;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CurrencyApiProviderGetCurrenciesTest extends AbstractCurrencyApiProviderTest
{
    public function testReturnsCurrencyArray(): void
    {
        $code = 'USD';
        $dto = $this->prepareGetCurrency($code);
        $result = $this->provider->getCurrencies($code);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(CurrencyDto::class, $result[$code]);
        $this->assertSame($dto, $result[$code]);
    }

    public function testReturnsEmptyArray(): void
    {
        $this->provider = $this->providerMock();

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn([]);

        $this->provider
            ->method('isAlive')
            ->willReturn(true);

        $this->client
            ->method('currencies')
            ->willReturn($response);

        $this->denormalizer
            ->expects($this->never())
            ->method('denormalize');

        $result = $this->provider->getCurrencies('USD');

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testThrowsException(): void
    {
        $this->provider = $this->providerMock();

        $this->provider
            ->method('isAlive')
            ->willReturn(false);

        $this->expectException(CurrencyApiUnavailableException::class);

        $this->provider->getCurrencies('USD');
    }

}
