<?php

namespace App\Service;

use App\Enum\CurrencyApiEndpoint;
use App\Enum\HttpMethod;
use Psr\Log\LoggerInterface;
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

    public function isAlive(): bool
    {
        try {
            $response = $this->makeRequest(
                method: HttpMethod::GET,
                endpoint: CurrencyApiEndpoint::STATUS,
            );

            if ($response->getStatusCode() !== 200) {
                $response->getContent();
                return false;
            }

            return true;
        } catch (Throwable $e) {
            $this->logger->error($e);
            return false;
        }
    }

    public function getCurrencyData(string $currencyCode): ?array
    {
        try {
            $response = $this->makeRequest(
                method: HttpMethod::GET,
                endpoint: CurrencyApiEndpoint::CURRENCIES,
                options: ['query' => ['currencies' => $currencyCode]],
            );

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            return $response->toArray();
        } catch (Throwable $e) {
            $this->logger->error($e);
            return null;
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

}
