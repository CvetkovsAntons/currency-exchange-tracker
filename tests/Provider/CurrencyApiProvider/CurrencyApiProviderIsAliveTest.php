<?php

namespace App\Tests\Provider\CurrencyApiProvider;

use App\Exception\CurrencyApi\CurrencyApiUnavailableException;
use App\Tests\Abstract\Provider\AbstractCurrencyApiProviderTest;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CurrencyApiProviderIsAliveTest extends AbstractCurrencyApiProviderTest
{
    public function testReturnsTrue(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client
            ->method('status')
            ->willReturn($response);

        $result = $this->provider->isAlive();

        $this->assertTrue($result);
    }

    public function testReturnsFalse(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(404);

        $this->client
            ->method('status')
            ->willReturn($response);

        $result = $this->provider->isAlive();

        $this->assertFalse($result);
    }

    public function testThrowException(): void
    {
        $this->client
            ->method('status')
            ->willThrowException(new CurrencyApiUnavailableException());

        $this->expectException(CurrencyApiUnavailableException::class);

        $this->provider->isAlive();
    }

}
