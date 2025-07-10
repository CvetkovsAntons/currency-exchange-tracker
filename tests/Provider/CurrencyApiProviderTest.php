<?php

namespace App\Tests\Provider;

use App\Client\CurrencyApiClient;
use App\Dto\Currency as CurrencyDto;
use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Exception\CurrencyApi\CurrencyApiUnavailableException;
use App\Provider\CurrencyApiProvider;
use App\Tests\Internal\Factory\CurrencyTestFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CurrencyApiProviderTest extends TestCase
{
    private CurrencyApiClient&MockObject $client;
    private DenormalizerInterface $serializer;
    private CurrencyApiProvider $provider;

    protected function setUp(): void
    {
        $this->client = $this->createMock(CurrencyApiClient::class);
        $this->serializer = $this->getMockBuilder(DenormalizerInterface::class)
            ->onlyMethods(['denormalize', 'supportsDenormalization', 'getSupportedTypes'])
            ->getMock();

        $this->provider = new CurrencyApiProvider($this->client, $this->serializer);
    }

    public function testIsAliveReturnsTrue(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client
            ->method('status')
            ->willReturn($response);

        $result = $this->provider->isAlive();

        $this->assertTrue($result);
    }

    public function testIsAliveReturnsFalse(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(404);

        $this->client
            ->method('status')
            ->willReturn($response);

        $result = $this->provider->isAlive();

        $this->assertFalse($result);
    }

    public function testIsAliveThrowException(): void
    {
        $this->client
            ->method('status')
            ->willThrowException(new CurrencyApiUnavailableException());

        $this->expectException(CurrencyApiUnavailableException::class);

        $this->provider->isAlive();
    }

    public function testGetCurrenciesReturnsCurrencyArray(): void
    {
        $code = 'USD';
        $dto = $this->prepareGetCurrency($code);
        $result = $this->provider->getCurrencies($code);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(CurrencyDto::class, $result[$code]);
        $this->assertSame($dto, $result[$code]);
    }

    public function testGetCurrenciesReturnsEmptyArray(): void
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

        $result = $this->provider->getCurrencies('USD');

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testGetCurrenciesThrowsException(): void
    {
        $this->provider = $this->providerMock();

        $this->provider
            ->method('isAlive')
            ->willReturn(false);

        $this->expectException(CurrencyApiUnavailableException::class);

        $this->provider->getCurrencies('USD');
    }

    public function testGetCurrencyReturnsDto(): void
    {
        $code = 'USD';
        $dto = $this->prepareGetCurrency($code);
        $result = $this->provider->getCurrency($code);

        $this->assertInstanceOf(CurrencyDto::class, $result);
        $this->assertSame($dto, $result);
    }

    public function testGetCurrencyReturnsNull(): void
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

    public function testGetCurrencyThrowsException(): void
    {
        $this->provider = $this->providerMock();

        $this->provider
            ->method('isAlive')
            ->willReturn(false);

        $this->expectException(CurrencyApiUnavailableException::class);

        $this->provider->getCurrency('USD');
    }

    public function testGetLatestExchangeRateReturnsRate(): void
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

    public function testGetLatestExchangeRateReturnsNull(): void
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

    private function providerMock(): CurrencyApiProvider&MockObject
    {
        return $this->getMockBuilder(CurrencyApiProvider::class)
            ->setConstructorArgs([$this->client, $this->serializer])
            ->onlyMethods(['isAlive'])
            ->getMock();
    }

    private function prepareGetCurrency(string $currencyCode): CurrencyDto
    {
        $this->provider = $this->providerMock();
        $dto = CurrencyTestFactory::makeDto($currencyCode);
        $responseData = ['data' => [$currencyCode => $dto->toArray()]];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($responseData);

        $this->provider
            ->method('isAlive')
            ->willReturn(true);

        $this->client
            ->method('currencies')
            ->with($currencyCode)
            ->willReturn($response);

        $this->serializer
            ->method('denormalize')
            ->withAnyParameters()
            ->willReturn($dto);

        return $dto;
    }

}
