<?php

namespace App\Client;

use App\Enum\CurrencyApiEndpoint;
use App\Enum\HttpMethod;
use App\Exception\CurrencyApi\CurrencyApiRequestException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

/**
 * This service is used to communicate with external currency API
 */
class CurrencyApiClient extends AbstractApiClient
{
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected LoggerInterface     $logger,
        protected readonly string     $baseUrl,
        protected readonly string     $apiKey
    )
    {
        parent::__construct($httpClient, $logger, $baseUrl, $apiKey);
    }

    /**
     * @throws CurrencyApiRequestException
     */
    public function status(): ResponseInterface
    {
        try {
            $response = $this->makeRequest(
                method: HttpMethod::GET,
                endpoint: CurrencyApiEndpoint::STATUS,
            );

            $this->validateResponse($response);

            return $response;
        } catch (Throwable $e) {
            $this->processException($e);
        }
    }

    /**
     * @throws CurrencyApiRequestException
     */
    public function currencies(string ...$currencies): ResponseInterface
    {
        try {
            $query = [];

            if (!empty($currencies)) {
                $query['currencies'] = implode(',', $currencies);
            }

            $response = $this->makeRequest(
                method: HttpMethod::GET,
                endpoint: CurrencyApiEndpoint::CURRENCIES,
                options: ['query' => $query],
            );

            $this->validateResponse($response);

            return $response;
        } catch (Throwable $e) {
            $this->processException($e);
        }
    }

    /**
     * @throws CurrencyApiRequestException
     */
    public function latestExchangeRate(string $fromCurrency, string ...$toCurrencies): ResponseInterface
    {
        try {
            $query = ['base_currency' => $fromCurrency];

            if (!empty($toCurrencies)) {
                $query['currencies'] = implode(',', $toCurrencies);
            }

            $response = $this->makeRequest(
                method: HttpMethod::GET,
                endpoint: CurrencyApiEndpoint::LATEST_EXCHANGE_RATE,
                options: ['query' => $query],
            );

            $this->validateResponse($response);

            return $response;
        } catch (Throwable $e) {
            $this->processException($e);
        }
    }

}
