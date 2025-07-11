<?php

namespace App\Tests\Client\CurrencyApiClient;

use App\Enum\CurrencyApiEndpoint;
use App\Enum\HttpMethod;
use App\Tests\Abstract\Client\AbstractCurrencyApiClientTest;

class CurrencyApiClientStatusTest extends AbstractCurrencyApiClientTest
{

    public function testSuccess(): void
    {
        $response = $this->responseMock();

        $this->requestSuccessMock(
            return: $response,
            method: HttpMethod::GET,
            endpoint: CurrencyApiEndpoint::STATUS
        );

        $result = $this->client->status();

        $this->assertSame($response, $result);
    }

    public function testThrowsException(): void
    {
        $this->requestExceptionMock();

        $this->client->status();
    }

}
