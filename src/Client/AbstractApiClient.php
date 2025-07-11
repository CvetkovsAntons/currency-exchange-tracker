<?php

namespace App\Client;

use App\Enum\CurrencyApiEndpoint;
use App\Enum\HttpMethod;
use App\Exception\ExternalApi\ExternalApiRequestException;
use App\Exception\ExternalApi\ExternalApiResponseException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class AbstractApiClient
{
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected LoggerInterface     $logger,
        private readonly string       $baseUrl,
        private readonly string       $apiKey,
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    protected function get(CurrencyApiEndpoint $endpoint, array $query = []): ResponseInterface
    {
        return $this->makeRequest(HttpMethod::GET, $endpoint, ['query' => $query]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function makeRequest(
        HttpMethod          $method,
        CurrencyApiEndpoint $endpoint,
        array               $options = []
    ): ResponseInterface
    {
        $url = $this->baseUrl . $endpoint->value;

        $options['query']['apikey'] = $this->apiKey;

        return $this->httpClient->request($method->value, $url, $options);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ExternalApiResponseException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function validateResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            return;
        }

        $message = $response->getContent(false);

        throw new ExternalApiResponseException($message, $statusCode);
    }

    /**
     * @throws ExternalApiRequestException
     */
    protected function processException(Throwable $throwable): void
    {
        $this->logger->error($throwable);

        throw new ExternalApiRequestException($throwable);
    }

}
