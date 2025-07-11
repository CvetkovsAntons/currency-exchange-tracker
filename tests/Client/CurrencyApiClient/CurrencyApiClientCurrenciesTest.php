<?php

namespace App\Tests\Client\CurrencyApiClient;

use App\Enum\CurrencyApiEndpoint;
use App\Enum\HttpMethod;
use App\Tests\Abstract\Client\AbstractCurrencyApiClientTest;

class CurrencyApiClientCurrenciesTest extends AbstractCurrencyApiClientTest
{

    public function testSuccessWithoutArgs(): void
    {
        $response = $this->responseMock();

        $this->requestSuccessMock(
            return: $response,
            method: HttpMethod::GET,
            endpoint: CurrencyApiEndpoint::CURRENCIES
        );

        $result = $this->client->currencies();

        $this->assertSame($response, $result);
    }

    public function testSuccessWithArgs(): void
    {
        $response = $this->responseMock();

        $this->requestSuccessMock(
            return: $response,
            method: HttpMethod::GET,
            endpoint: CurrencyApiEndpoint::CURRENCIES,
            options: ['query' => ['currencies' => 'PHP,EUR']]
        );

        $result = $this->client->currencies('PHP', 'EUR');

        $this->assertSame($response, $result);
    }

    public function testThrowsException(): void
    {
        $this->requestExceptionMock();

        $this->client->currencies('PHP');
    }

}
