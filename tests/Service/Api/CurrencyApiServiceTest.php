<?php

namespace App\Tests\Service\Api;

use App\Enum\CurrencyApiEndpoint;
use App\Enum\HttpMethod;
use App\Exception\AbstractCustomException;
use App\Service\Api\CurrencyApiService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CurrencyApiServiceTest extends TestCase
{
    private const string BASE_URL = 'https://api.example.com/';
    private const string API_KEY = 'test-api-key';

    private HttpClientInterface&MockObject $httpClient;
    private LoggerInterface&MockObject $logger;
    private CurrencyApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new CurrencyApiService(
            $this->httpClient,
            $this->logger,
            self::BASE_URL,
            self::API_KEY,
        );
    }

    public function testStatusReturnsResponse(): void
    {
        $response = $this->responseMock();

        $this->requestSuccessMock(
            return: $response,
            method: HttpMethod::GET,
            endpoint: CurrencyApiEndpoint::STATUS
        );

        $result = $this->service->status();

        $this->assertSame($response, $result);
    }

    public function testStatusThrowsException(): void
    {
        $this->requestExceptionMock();

        $this->service->status();
    }

    public function testCurrenciesWithoutArgs(): void
    {
        $response = $this->responseMock();

        $this->requestSuccessMock(
            return: $response,
            method: HttpMethod::GET,
            endpoint: CurrencyApiEndpoint::CURRENCIES
        );

        $result = $this->service->currencies();

        $this->assertSame($response, $result);
    }

    public function testCurrenciesWithArgs(): void
    {
        $response = $this->responseMock();

        $this->requestSuccessMock(
            return: $response,
            method: HttpMethod::GET,
            endpoint: CurrencyApiEndpoint::CURRENCIES,
            options: ['query' => ['currencies' => 'PHP,EUR']]
        );

        $result = $this->service->currencies('PHP', 'EUR');

        $this->assertSame($response, $result);
    }

    public function testCurrenciesThrowsException(): void
    {
        $this->requestExceptionMock();

        $this->service->currencies('PHP');
    }

    public function testLatestExchangeRateWithoutArgs(): void
    {
        $response = $this->responseMock();

        $baseCurrency = 'PHP';

        $this->requestSuccessMock(
            return: $response,
            method: HttpMethod::GET,
            endpoint: CurrencyApiEndpoint::LATEST_EXCHANGE_RATE,
            options: ['query' => ['base_currency' => $baseCurrency]]
        );

        $result = $this->service->latestExchangeRate($baseCurrency);

        $this->assertSame($response, $result);
    }

    public function testLatestExchangeRateWithArgs(): void
    {
        $response = $this->responseMock();

        $baseCurrency = 'PHP';
        $options = ['query' => ['base_currency' => $baseCurrency, 'currencies' => 'PHP,EUR,USD']];

        $this->requestSuccessMock(
            return: $response,
            method: HttpMethod::GET,
            endpoint: CurrencyApiEndpoint::LATEST_EXCHANGE_RATE,
            options: $options
        );

        $result = $this->service->latestExchangeRate($baseCurrency, 'PHP', 'EUR', 'USD');

        $this->assertSame($response, $result);
    }

    public function testLatestExchangeRateThrowsException(): void
    {
        $this->requestExceptionMock();

        $this->service->latestExchangeRate('PHP');
    }

    private function responseMock(): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        return $response;
    }

    private function requestSuccessMock(
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

    private function requestExceptionMock(): void
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
