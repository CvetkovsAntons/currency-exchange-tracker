<?php

namespace App\Tests\Provider\CurrencyApiProvider;

use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Tests\Abstract\Provider\AbstractCurrencyApiProviderTest;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CurrencyApiProviderGetLatestExchangeRateTest extends AbstractCurrencyApiProviderTest
{
    public function testReturnsRate(): void
    {
        $from = $this->createMock(Currency::class);
        $to = $this->createMock(Currency::class);
        $pair = $this->createMock(CurrencyPair::class);
        $rate = '1.23';

        $from
            ->method('getCode')
            ->willReturn('USD');

        $to
            ->method('getCode')
            ->willReturn('EUR');

        $pair
            ->method('getFromCurrency')
            ->willReturn($from);

        $pair
            ->method('getToCurrency')
            ->willReturn($to);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['data' => ['EUR' => $rate]]);

        $this->client
            ->method('latestExchangeRate')
            ->with('USD', 'EUR')
            ->willReturn($response);

        $result = $this->provider->getLatestExchangeRate($pair);

        $this->assertSame($rate, $result);
    }

    public function testReturnsNull(): void
    {
        $from = $this->createMock(Currency::class);
        $to = $this->createMock(Currency::class);
        $pair = $this->createMock(CurrencyPair::class);

        $from
            ->method('getCode')
            ->willReturn('USD');

        $to
            ->method('getCode')
            ->willReturn('EUR');

        $pair
            ->method('getFromCurrency')
            ->willReturn($from);

        $pair
            ->method('getToCurrency')
            ->willReturn($to);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['data' => []]);

        $this->client
            ->method('latestExchangeRate')
            ->with('USD', 'EUR')
            ->willReturn($response);

        $result = $this->provider->getLatestExchangeRate($pair);

        $this->assertNull($result);
    }

}
