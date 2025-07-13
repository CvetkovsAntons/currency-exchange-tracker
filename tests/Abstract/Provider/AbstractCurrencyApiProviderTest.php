<?php

namespace App\Tests\Abstract\Provider;

use App\Client\CurrencyApiClient;
use App\Dto\Currency as CurrencyDto;
use App\Provider\CurrencyApiProvider;
use App\Tests\Internal\Factory\CurrencyTestFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractCurrencyApiProviderTest extends TestCase
{
    protected CurrencyApiClient&MockObject $client;
    protected DenormalizerInterface $denormalizer;
    protected CurrencyApiProvider $provider;

    protected function setUp(): void
    {
        $this->client = $this->createMock(CurrencyApiClient::class);
        $this->denormalizer = $this->getMockBuilder(DenormalizerInterface::class)
            ->onlyMethods(['denormalize', 'supportsDenormalization', 'getSupportedTypes'])
            ->getMock();

        $this->provider = new CurrencyApiProvider($this->client, $this->denormalizer);
    }

    protected function providerMock(): CurrencyApiProvider&MockObject
    {
        return $this->getMockBuilder(CurrencyApiProvider::class)
            ->setConstructorArgs([$this->client, $this->denormalizer])
            ->onlyMethods(['isAlive'])
            ->getMock();
    }

    protected function prepareGetCurrency(string $currencyCode): CurrencyDto
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

        $this->denormalizer
            ->method('denormalize')
            ->withAnyParameters()
            ->willReturn($dto);

        return $dto;
    }

}
