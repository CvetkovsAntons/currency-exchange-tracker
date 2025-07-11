<?php

namespace App\Tests\Provider\CurrencyApiProvider;

use App\Dto\Currency as CurrencyDto;
use App\Exception\CurrencyApi\CurrencyApiUnavailableException;
use App\Tests\Abstract\Provider\AbstractCurrencyApiProviderTest;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CurrencyApiProviderGetCurrencyTest extends AbstractCurrencyApiProviderTest
{
    public function testReturnsDto(): void
    {
        $code = 'USD';
        $dto = $this->prepareGetCurrency($code);
        $result = $this->provider->getCurrency($code);

        $this->assertInstanceOf(CurrencyDto::class, $result);
        $this->assertSame($dto, $result);
    }

    public function testReturnsNull(): void
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

        $this->serializer
            ->expects($this->never())
            ->method('denormalize');

        $result = $this->provider->getCurrency('USD');

        $this->assertNull($result);
    }

    public function testThrowsException(): void
    {
        $this->provider = $this->providerMock();

        $this->provider
            ->method('isAlive')
            ->willReturn(false);

        $this->expectException(CurrencyApiUnavailableException::class);

        $this->provider->getCurrency('USD');
    }

}
