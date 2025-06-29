<?php

namespace App\Service\Api;

use App\Enum\CurrencyApiEndpoint;
use App\Enum\HttpMethod;
use App\Exception\CurrencyApiException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
     * @throws CurrencyApiException
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
     * @throws CurrencyApiException
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
     * @throws CurrencyApiException
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
     */
    private function validateResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            return;
        }

        $message = $response->getContent(false);

        throw new HttpException(
            statusCode: $statusCode,
            message: "Unexpected HTTP status code: $statusCode. Message: $message",
            code: $statusCode
        );
    }

    /**
     * @param Throwable $throwable
     * @return void
     * @throws CurrencyApiException
     */
    private function processException(Throwable $throwable): void
    {
        $this->logger->error($throwable);

        throw new CurrencyApiException(
            message: "Currency API request failed: {$throwable->getMessage()}",
            code: $throwable->getCode(),
            previous: $throwable
        );
    }

}
