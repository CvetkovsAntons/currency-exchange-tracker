<?php

namespace App\Service\Api;

use App\Enum\CurrencyApiEndpoint;
use App\Enum\HttpMethod;
use App\Exception\CurrencyApi\CurrencyApiRequestException;
use App\Exception\CurrencyApi\CurrencyApiResponseException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

readonly class CurrencyApiService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface     $logger,
        private string              $baseUrl,
        private string              $apiKey,
    ) {}

    /**
     * @return ResponseInterface
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
     * @param string ...$currencies
     * @return ResponseInterface
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
     * @param string $fromCurrency
     * @param string ...$toCurrencies
     * @return ResponseInterface
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

    /**
     * @param HttpMethod $method
     * @param CurrencyApiEndpoint $endpoint
     * @param array $options
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    private function makeRequest(HttpMethod $method, CurrencyApiEndpoint $endpoint, array $options = []): ResponseInterface
    {
        $url = $this->baseUrl . $endpoint->value;

        $options['query']['apikey'] = $this->apiKey;

        return $this->httpClient->request($method->value, $url, $options);
    }

    /**
     * @param ResponseInterface $response
     * @return void
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws CurrencyApiResponseException
     */
    private function validateResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            return;
        }

        $message = $response->getContent(false);

        throw new CurrencyApiResponseException($message, $statusCode);
    }

    /**
     * @param Throwable $throwable
     * @return void
     * @throws CurrencyApiRequestException
     */
    private function processException(Throwable $throwable): void
    {
        $this->logger->error($throwable);

        throw new CurrencyApiRequestException($throwable);
    }

}
