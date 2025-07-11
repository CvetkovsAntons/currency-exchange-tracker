<?php

namespace App\Tests\Client\CurrencyApiClient;

use App\Enum\CurrencyApiEndpoint;
use App\Enum\HttpMethod;
use App\Tests\Abstract\Client\AbstractCurrencyApiClientTest;

class CurrencyApiClientLatestExchangeRateTest extends AbstractCurrencyApiClientTest
{
    public function testSuccessWithoutArgs(): void
    {
        $response = $this->responseMock();

        $baseCurrency = 'PHP';

        $this->requestSuccessMock(
            return: $response,
            method: HttpMethod::GET,
            endpoint: CurrencyApiEndpoint::LATEST_EXCHANGE_RATE,
            options: ['query' => ['base_currency' => $baseCurrency]]
        );

        $result = $this->client->latestExchangeRate($baseCurrency);

        $this->assertSame($response, $result);
    }

    public function testSuccessWithArgs(): void
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

        $result = $this->client->latestExchangeRate($baseCurrency, 'PHP', 'EUR', 'USD');

        $this->assertSame($response, $result);
    }

    public function testThrowsException(): void
    {
        $this->requestExceptionMock();

        $this->client->latestExchangeRate('PHP');
    }

}
