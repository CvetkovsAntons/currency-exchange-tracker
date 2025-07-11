<?php

namespace App\Client;

use App\Enum\CurrencyApiEndpoint;
use App\Exception\ExternalApi\ExternalApiRequestException;
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
     * @throws ExternalApiRequestException
     */
    public function status(): ResponseInterface
    {
        try {
            $response = $this->get(CurrencyApiEndpoint::STATUS);

            $this->validateResponse($response);

            return $response;
        } catch (Throwable $e) {
            $this->processException($e);
        }
    }

    /**
     * @throws ExternalApiRequestException
     */
    public function currencies(string ...$currencies): ResponseInterface
    {
        try {
            $query = [];

            if (!empty($currencies)) {
                $query['currencies'] = implode(',', $currencies);
            }

            $response = $this->get(CurrencyApiEndpoint::CURRENCIES, $query);

            $this->validateResponse($response);

            return $response;
        } catch (Throwable $e) {
            $this->processException($e);
        }
    }

    /**
     * @throws ExternalApiRequestException
     */
    public function latestExchangeRate(string $fromCurrency, string ...$toCurrencies): ResponseInterface
    {
        try {
            $query = ['base_currency' => $fromCurrency];

            if (!empty($toCurrencies)) {
                $query['currencies'] = implode(',', $toCurrencies);
            }

            $response = $this->get(CurrencyApiEndpoint::LATEST_EXCHANGE_RATE, $query);

            $this->validateResponse($response);

            return $response;
        } catch (Throwable $e) {
            $this->processException($e);
        }
    }

}
