<?php

namespace App\Tests\Abstract\Client;

use App\Client\CurrencyApiClient;
use App\Enum\CurrencyApiEndpoint;
use App\Enum\HttpMethod;
use App\Exception\AbstractCustomException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractCurrencyApiClientTest extends TestCase
{
    private const string BASE_URL = 'https://api.example.com/';
    private const string API_KEY = 'test-api-key';

    protected HttpClientInterface&MockObject $httpClient;
    protected LoggerInterface&MockObject $logger;
    protected CurrencyApiClient $client;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->client = new CurrencyApiClient(
            $this->httpClient,
            $this->logger,
            self::BASE_URL,
            self::API_KEY,
        );
    }

    protected function responseMock(): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        return $response;
    }

    protected function requestSuccessMock(
        ResponseInterface   $return,
        HttpMethod          $method,
        CurrencyApiEndpoint $endpoint,
        array               $options = []
    ): void
    {
        $url = self::BASE_URL . $endpoint->value;

        $options['query']['apikey'] = self::API_KEY;

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with($method->value, $url, $options)
            ->willReturn($return);
    }

    protected function requestExceptionMock(): void
    {
        $this->httpClient
            ->method('request')
            ->willThrowException(new AbstractCustomException('Network error'));

        $this->logger
            ->expects($this->once())
            ->method('error');

        $this->expectException(AbstractCustomException::class);
    }

}
